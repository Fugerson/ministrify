<?php

namespace App\Services;

use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonMergeService
{
    /**
     * Find potential duplicate people within a church.
     *
     * @return Collection<int, array{personA: Person, personB: Person, reasons: string[]}>
     */
    public function findDuplicates(int $churchId): Collection
    {
        $people = Person::where('church_id', $churchId)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $pairs = collect();
        $seen = [];

        // Strategy 1: Normalized phone match
        $phoneGroups = [];
        foreach ($people as $person) {
            $normalized = Person::normalizePhone($person->phone);
            if ($normalized) {
                $phoneGroups[$normalized][] = $person;
            }
        }
        foreach ($phoneGroups as $group) {
            if (count($group) < 2) {
                continue;
            }
            for ($i = 0; $i < count($group); $i++) {
                for ($j = $i + 1; $j < count($group); $j++) {
                    $key = min($group[$i]->id, $group[$j]->id).'-'.max($group[$i]->id, $group[$j]->id);
                    if (! isset($seen[$key])) {
                        $seen[$key] = ['personA' => $group[$i], 'personB' => $group[$j], 'reasons' => []];
                    }
                    $seen[$key]['reasons'][] = 'phone';
                }
            }
        }

        // Strategy 2: Email match (case-insensitive)
        $emailGroups = [];
        foreach ($people as $person) {
            if ($person->email) {
                $emailGroups[mb_strtolower($person->email)][] = $person;
            }
        }
        foreach ($emailGroups as $group) {
            if (count($group) < 2) {
                continue;
            }
            for ($i = 0; $i < count($group); $i++) {
                for ($j = $i + 1; $j < count($group); $j++) {
                    $key = min($group[$i]->id, $group[$j]->id).'-'.max($group[$i]->id, $group[$j]->id);
                    if (! isset($seen[$key])) {
                        $seen[$key] = ['personA' => $group[$i], 'personB' => $group[$j], 'reasons' => []];
                    }
                    $seen[$key]['reasons'][] = 'email';
                }
            }
        }

        // Strategy 3: Full name match (case-insensitive, trimmed)
        $nameGroups = [];
        foreach ($people as $person) {
            $nameKey = mb_strtolower(trim($person->first_name).' '.trim($person->last_name));
            if ($nameKey && $nameKey !== ' ') {
                $nameGroups[$nameKey][] = $person;
            }
        }
        foreach ($nameGroups as $group) {
            if (count($group) < 2) {
                continue;
            }
            for ($i = 0; $i < count($group); $i++) {
                for ($j = $i + 1; $j < count($group); $j++) {
                    $key = min($group[$i]->id, $group[$j]->id).'-'.max($group[$i]->id, $group[$j]->id);
                    if (! isset($seen[$key])) {
                        $seen[$key] = ['personA' => $group[$i], 'personB' => $group[$j], 'reasons' => []];
                    }
                    $seen[$key]['reasons'][] = 'name';
                }
            }
        }

        // Strategy 4: Similar email (same prefix before @ or Levenshtein ≤ 2)
        $emailPeople = $people->filter(fn ($p) => ! empty($p->email));
        $emailArray = $emailPeople->values()->all();
        for ($i = 0; $i < count($emailArray); $i++) {
            for ($j = $i + 1; $j < count($emailArray); $j++) {
                $emailA = mb_strtolower($emailArray[$i]->email);
                $emailB = mb_strtolower($emailArray[$j]->email);

                if ($emailA === $emailB) {
                    continue; // Already caught by Strategy 2
                }

                $prefixA = strstr($emailA, '@', true);
                $prefixB = strstr($emailB, '@', true);

                $isSimilar = ($prefixA && $prefixB && $prefixA === $prefixB)
                    || levenshtein($emailA, $emailB) <= 2;

                if ($isSimilar) {
                    $key = min($emailArray[$i]->id, $emailArray[$j]->id).'-'.max($emailArray[$i]->id, $emailArray[$j]->id);
                    if (! isset($seen[$key])) {
                        $seen[$key] = ['personA' => $emailArray[$i], 'personB' => $emailArray[$j], 'reasons' => []];
                    }
                    $seen[$key]['reasons'][] = 'similar_email';
                }
            }
        }

        // Deduplicate reasons
        foreach ($seen as &$pair) {
            $pair['reasons'] = array_unique($pair['reasons']);
        }

        return collect(array_values($seen));
    }

    /**
     * Merge secondary person into primary person.
     * Transfers all data and relationships, then soft-deletes secondary.
     */
    public function merge(Person $primary, Person $secondary): void
    {
        DB::transaction(function () use ($primary, $secondary) {
            // 1. Transfer scalar fields (fill NULLs on primary from secondary)
            $scalarFields = [
                'phone', 'email', 'iban', 'gender', 'marital_status',
                'telegram_username', 'telegram_chat_id', 'photo',
                'address', 'birth_date', 'anniversary', 'first_visit_date',
                'joined_date', 'baptism_date', 'church_role', 'church_role_id',
                'membership_status', 'notes', 'is_shepherd',
            ];

            $updates = [];
            foreach ($scalarFields as $field) {
                if (empty($primary->{$field}) && ! empty($secondary->{$field})) {
                    $updates[$field] = $secondary->{$field};
                }
            }

            // For date fields, keep the earliest (most meaningful) value
            $dateFields = ['first_visit_date', 'joined_date', 'baptism_date'];
            foreach ($dateFields as $dateField) {
                if (! empty($primary->{$dateField}) && ! empty($secondary->{$dateField})) {
                    $primaryDate = Carbon::parse($primary->{$dateField});
                    $secondaryDate = Carbon::parse($secondary->{$dateField});
                    if ($secondaryDate->lt($primaryDate)) {
                        $updates[$dateField] = $secondary->{$dateField};
                    }
                }
            }

            if (! empty($updates)) {
                $primary->update($updates);
            }

            // 2. Transfer user account link
            if (! $primary->user_id && $secondary->user_id) {
                // Unlink secondary first
                $userId = $secondary->user_id;
                $secondary->update(['user_id' => null]);

                // Link to primary
                $primary->update(['user_id' => $userId]);

                // Update church_user pivot
                DB::table('church_user')
                    ->where('person_id', $secondary->id)
                    ->update(['person_id' => $primary->id, 'updated_at' => now()]);
            }

            // 3. Transfer BelongsToMany pivots (without duplicates)

            // ministry_person
            $existingMinistries = DB::table('ministry_person')
                ->where('person_id', $primary->id)
                ->pluck('ministry_id')
                ->toArray();

            DB::table('ministry_person')
                ->where('person_id', $secondary->id)
                ->whereNotIn('ministry_id', $existingMinistries)
                ->update(['person_id' => $primary->id]);

            DB::table('ministry_person')
                ->where('person_id', $secondary->id)
                ->delete();

            // group_person
            $existingGroups = DB::table('group_person')
                ->where('person_id', $primary->id)
                ->pluck('group_id')
                ->toArray();

            DB::table('group_person')
                ->where('person_id', $secondary->id)
                ->whereNotIn('group_id', $existingGroups)
                ->update(['person_id' => $primary->id]);

            DB::table('group_person')
                ->where('person_id', $secondary->id)
                ->delete();

            // person_tag
            $existingTags = DB::table('person_tag')
                ->where('person_id', $primary->id)
                ->pluck('tag_id')
                ->toArray();

            DB::table('person_tag')
                ->where('person_id', $secondary->id)
                ->whereNotIn('tag_id', $existingTags)
                ->update(['person_id' => $primary->id]);

            DB::table('person_tag')
                ->where('person_id', $secondary->id)
                ->delete();

            // 4. Transfer HasMany relationships (simple person_id update)
            // Remove duplicate attendance records (same attendance_id already exists for primary)
            if (\Schema::hasTable('attendance_records')) {
                $existingAttendanceIds = DB::table('attendance_records')
                    ->where('person_id', $primary->id)
                    ->pluck('attendance_id');
                DB::table('attendance_records')
                    ->where('person_id', $secondary->id)
                    ->whereIn('attendance_id', $existingAttendanceIds)
                    ->delete();
            }

            $hasManyTables = [
                'assignments',
                'event_responsibilities',
                'person_worship_skills',
                'event_worship_team',
                'event_ministry_team',
                'unavailable_dates',
                'attendance_records',
                'transactions',
                'person_communications',
                'blockout_dates',
                'telegram_messages',
                'family_relationships',
                'prayer_requests',
                'online_donations',
                'scheduling_preferences',
                'meeting_attendees',
            ];

            foreach ($hasManyTables as $table) {
                if (\Schema::hasTable($table)) {
                    DB::table($table)
                        ->where('person_id', $secondary->id)
                        ->update(['person_id' => $primary->id]);
                }
            }

            // family_relationships: also update related_person_id
            if (\Schema::hasTable('family_relationships')) {
                DB::table('family_relationships')
                    ->where('related_person_id', $secondary->id)
                    ->update(['related_person_id' => $primary->id]);

                // Clean up self-referential relationships (if merged persons were related to each other)
                DB::table('family_relationships')
                    ->where('person_id', $primary->id)
                    ->where('related_person_id', $primary->id)
                    ->delete();

                // Clean up exact duplicate relationships created by merge
                $familyDuplicates = DB::table('family_relationships')
                    ->where(function ($q) use ($primary) {
                        $q->where('person_id', $primary->id)
                            ->orWhere('related_person_id', $primary->id);
                    })
                    ->select('person_id', 'related_person_id', 'relationship_type', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as cnt'))
                    ->groupBy('person_id', 'related_person_id', 'relationship_type')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();

                foreach ($familyDuplicates as $dup) {
                    DB::table('family_relationships')
                        ->where('person_id', $dup->person_id)
                        ->where('related_person_id', $dup->related_person_id)
                        ->where('relationship_type', $dup->relationship_type)
                        ->where('id', '!=', $dup->keep_id)
                        ->delete();
                }

                // Clean up semantic duplicates (same relationship stored in opposite directions)
                // e.g., A→C "parent" and C→A "child" are the same relationship
                $this->cleanSemanticFamilyDuplicates($primary->id);
            }

            // kanban cards
            if (\Schema::hasTable('kanban_cards') && \Schema::hasColumn('kanban_cards', 'person_id')) {
                DB::table('kanban_cards')
                    ->where('person_id', $secondary->id)
                    ->update(['person_id' => $primary->id]);
            }

            // staff_members
            if (\Schema::hasTable('staff_members') && \Schema::hasColumn('staff_members', 'person_id')) {
                DB::table('staff_members')
                    ->where('person_id', $secondary->id)
                    ->update(['person_id' => $primary->id]);
            }

            // testimonials
            if (\Schema::hasTable('testimonials') && \Schema::hasColumn('testimonials', 'person_id')) {
                DB::table('testimonials')
                    ->where('person_id', $secondary->id)
                    ->update(['person_id' => $primary->id]);
            }

            // monobank_transactions
            if (\Schema::hasTable('monobank_transactions') && \Schema::hasColumn('monobank_transactions', 'person_id')) {
                DB::table('monobank_transactions')
                    ->where('person_id', $secondary->id)
                    ->update(['person_id' => $primary->id]);
            }

            // privatbank_transactions
            if (\Schema::hasTable('privatbank_transactions') && \Schema::hasColumn('privatbank_transactions', 'person_id')) {
                DB::table('privatbank_transactions')
                    ->where('person_id', $secondary->id)
                    ->update(['person_id' => $primary->id]);
            }

            // 5. Transfer shepherd relationships
            // Handle case where primary's shepherd was the secondary person
            if ($primary->shepherd_id === $secondary->id) {
                $fallbackShepherd = ($secondary->shepherd_id && $secondary->shepherd_id !== $primary->id)
                    ? $secondary->shepherd_id
                    : null;
                $primary->update(['shepherd_id' => $fallbackShepherd]);
            }

            // People who had secondary as shepherd → now primary (exclude primary to prevent self-shepherding)
            Person::where('shepherd_id', $secondary->id)
                ->where('id', '!=', $primary->id)
                ->update(['shepherd_id' => $primary->id]);

            // If secondary had a shepherd but primary doesn't
            if (! $primary->shepherd_id && $secondary->shepherd_id && $secondary->shepherd_id !== $primary->id) {
                $primary->update(['shepherd_id' => $secondary->shepherd_id]);
            }

            // 6. Transfer leadership roles
            // Groups where secondary is leader
            DB::table('groups')
                ->where('leader_id', $secondary->id)
                ->update(['leader_id' => $primary->id]);

            // Ensure new leader is also a member in each affected group
            $affectedGroupIds = \App\Models\Group::where('leader_id', $primary->id)->pluck('id');
            foreach ($affectedGroupIds as $groupId) {
                DB::table('group_person')->updateOrInsert(
                    ['group_id' => $groupId, 'person_id' => $primary->id],
                    ['role' => 'leader', 'joined_at' => now()]
                );
            }

            // Ministries where secondary is leader
            DB::table('ministries')
                ->where('leader_id', $secondary->id)
                ->update(['leader_id' => $primary->id]);

            // Ensure new leader is also a member in each affected ministry
            $affectedMinistryIds = \App\Models\Ministry::where('leader_id', $primary->id)->pluck('id');
            foreach ($affectedMinistryIds as $ministryId) {
                DB::table('ministry_person')->updateOrInsert(
                    ['ministry_id' => $ministryId, 'person_id' => $primary->id],
                    ['role' => 'leader']
                );
            }

            // 7. Soft delete secondary
            $secondary->delete();

            Log::info('People merged', [
                'primary_id' => $primary->id,
                'secondary_id' => $secondary->id,
                'primary_name' => $primary->full_name,
                'secondary_name' => $secondary->full_name,
            ]);
        });
    }

    /**
     * Merge two people with explicit field selection (git-merge style).
     *
     * @param  Person  $personA  The base/primary person (receives merged data)
     * @param  Person  $personB  The secondary person (will be soft-deleted)
     * @param  array  $fieldSelections  ['phone' => 'A', 'email' => 'B', ...] — which person's value to keep per field
     */
    public function mergeWithFieldSelection(Person $personA, Person $personB, array $fieldSelections): void
    {
        DB::transaction(function () use ($personA, $personB, $fieldSelections) {
            // Map JS field names to model field names
            if (isset($fieldSelections['photo_url'])) {
                $fieldSelections['photo'] = $fieldSelections['photo_url'];
                unset($fieldSelections['photo_url']);
            }

            // 1. Apply field selections
            $scalarFields = [
                'phone', 'email', 'iban', 'gender', 'marital_status',
                'telegram_username', 'telegram_chat_id', 'photo',
                'address', 'birth_date', 'anniversary', 'first_visit_date',
                'joined_date', 'baptism_date', 'church_role', 'church_role_id',
                'membership_status', 'notes', 'is_shepherd',
            ];

            $updates = [];
            foreach ($scalarFields as $field) {
                if (isset($fieldSelections[$field])) {
                    if ($fieldSelections[$field] === 'B') {
                        // Take value from personB
                        $updates[$field] = $personB->{$field};
                    }
                    // 'A' means keep personA's value — no action needed
                } else {
                    // No explicit selection — fallback: fill NULLs from secondary
                    if (empty($personA->{$field}) && ! empty($personB->{$field})) {
                        $updates[$field] = $personB->{$field};
                    }
                }
            }

            if (! empty($updates)) {
                $personA->update($updates);
            }

            // 2. Transfer user account link
            if (! $personA->user_id && $personB->user_id) {
                $userId = $personB->user_id;
                $personB->update(['user_id' => null]);
                $personA->update(['user_id' => $userId]);

                DB::table('church_user')
                    ->where('person_id', $personB->id)
                    ->update(['person_id' => $personA->id, 'updated_at' => now()]);
            }

            // 3. Transfer BelongsToMany pivots (union — always combine)

            // ministry_person
            $existingMinistries = DB::table('ministry_person')
                ->where('person_id', $personA->id)
                ->pluck('ministry_id')
                ->toArray();

            DB::table('ministry_person')
                ->where('person_id', $personB->id)
                ->whereNotIn('ministry_id', $existingMinistries)
                ->update(['person_id' => $personA->id]);

            DB::table('ministry_person')
                ->where('person_id', $personB->id)
                ->delete();

            // group_person
            $existingGroups = DB::table('group_person')
                ->where('person_id', $personA->id)
                ->pluck('group_id')
                ->toArray();

            DB::table('group_person')
                ->where('person_id', $personB->id)
                ->whereNotIn('group_id', $existingGroups)
                ->update(['person_id' => $personA->id]);

            DB::table('group_person')
                ->where('person_id', $personB->id)
                ->delete();

            // person_tag
            $existingTags = DB::table('person_tag')
                ->where('person_id', $personA->id)
                ->pluck('tag_id')
                ->toArray();

            DB::table('person_tag')
                ->where('person_id', $personB->id)
                ->whereNotIn('tag_id', $existingTags)
                ->update(['person_id' => $personA->id]);

            DB::table('person_tag')
                ->where('person_id', $personB->id)
                ->delete();

            // 4. Transfer HasMany relationships

            // Remove duplicate attendance records (same attendance_id already exists for primary)
            if (\Schema::hasTable('attendance_records')) {
                $existingAttendanceIds = DB::table('attendance_records')
                    ->where('person_id', $personA->id)
                    ->pluck('attendance_id');
                DB::table('attendance_records')
                    ->where('person_id', $personB->id)
                    ->whereIn('attendance_id', $existingAttendanceIds)
                    ->delete();
            }

            $hasManyTables = [
                'assignments',
                'event_responsibilities',
                'person_worship_skills',
                'event_worship_team',
                'event_ministry_team',
                'unavailable_dates',
                'attendance_records',
                'transactions',
                'person_communications',
                'blockout_dates',
                'telegram_messages',
                'family_relationships',
                'prayer_requests',
                'online_donations',
                'scheduling_preferences',
                'meeting_attendees',
            ];

            foreach ($hasManyTables as $table) {
                if (\Schema::hasTable($table)) {
                    DB::table($table)
                        ->where('person_id', $personB->id)
                        ->update(['person_id' => $personA->id]);
                }
            }

            if (\Schema::hasTable('family_relationships')) {
                DB::table('family_relationships')
                    ->where('related_person_id', $personB->id)
                    ->update(['related_person_id' => $personA->id]);

                // Clean up self-referential relationships (if merged persons were related to each other)
                DB::table('family_relationships')
                    ->where('person_id', $personA->id)
                    ->where('related_person_id', $personA->id)
                    ->delete();

                // Clean up exact duplicate relationships created by merge
                $familyDuplicates = DB::table('family_relationships')
                    ->where(function ($q) use ($personA) {
                        $q->where('person_id', $personA->id)
                            ->orWhere('related_person_id', $personA->id);
                    })
                    ->select('person_id', 'related_person_id', 'relationship_type', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as cnt'))
                    ->groupBy('person_id', 'related_person_id', 'relationship_type')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();

                foreach ($familyDuplicates as $dup) {
                    DB::table('family_relationships')
                        ->where('person_id', $dup->person_id)
                        ->where('related_person_id', $dup->related_person_id)
                        ->where('relationship_type', $dup->relationship_type)
                        ->where('id', '!=', $dup->keep_id)
                        ->delete();
                }

                // Clean up semantic duplicates (same relationship stored in opposite directions)
                // e.g., A→C "parent" and C→A "child" are the same relationship
                $this->cleanSemanticFamilyDuplicates($personA->id);
            }

            if (\Schema::hasTable('kanban_cards') && \Schema::hasColumn('kanban_cards', 'person_id')) {
                DB::table('kanban_cards')
                    ->where('person_id', $personB->id)
                    ->update(['person_id' => $personA->id]);
            }

            if (\Schema::hasTable('staff_members') && \Schema::hasColumn('staff_members', 'person_id')) {
                DB::table('staff_members')
                    ->where('person_id', $personB->id)
                    ->update(['person_id' => $personA->id]);
            }

            if (\Schema::hasTable('testimonials') && \Schema::hasColumn('testimonials', 'person_id')) {
                DB::table('testimonials')
                    ->where('person_id', $personB->id)
                    ->update(['person_id' => $personA->id]);
            }

            if (\Schema::hasTable('monobank_transactions') && \Schema::hasColumn('monobank_transactions', 'person_id')) {
                DB::table('monobank_transactions')
                    ->where('person_id', $personB->id)
                    ->update(['person_id' => $personA->id]);
            }

            if (\Schema::hasTable('privatbank_transactions') && \Schema::hasColumn('privatbank_transactions', 'person_id')) {
                DB::table('privatbank_transactions')
                    ->where('person_id', $personB->id)
                    ->update(['person_id' => $personA->id]);
            }

            // 5. Transfer shepherd relationships
            // Handle case where primary's shepherd was the secondary person
            if ($personA->shepherd_id === $personB->id) {
                $fallbackShepherd = ($personB->shepherd_id && $personB->shepherd_id !== $personA->id)
                    ? $personB->shepherd_id
                    : null;
                $personA->update(['shepherd_id' => $fallbackShepherd]);
            }

            // People who had secondary as shepherd → now primary (exclude primary to prevent self-shepherding)
            Person::where('shepherd_id', $personB->id)
                ->where('id', '!=', $personA->id)
                ->update(['shepherd_id' => $personA->id]);

            // If secondary had a shepherd but primary doesn't
            if (! $personA->shepherd_id && $personB->shepherd_id && $personB->shepherd_id !== $personA->id) {
                $personA->update(['shepherd_id' => $personB->shepherd_id]);
            }

            // 6. Transfer leadership roles
            DB::table('groups')
                ->where('leader_id', $personB->id)
                ->update(['leader_id' => $personA->id]);

            // Ensure new leader is also a member in each affected group
            $affectedGroupIds = \App\Models\Group::where('leader_id', $personA->id)->pluck('id');
            foreach ($affectedGroupIds as $groupId) {
                DB::table('group_person')->updateOrInsert(
                    ['group_id' => $groupId, 'person_id' => $personA->id],
                    ['role' => 'leader', 'joined_at' => now()]
                );
            }

            DB::table('ministries')
                ->where('leader_id', $personB->id)
                ->update(['leader_id' => $personA->id]);

            // Ensure new leader is also a member in each affected ministry
            $affectedMinistryIds = \App\Models\Ministry::where('leader_id', $personA->id)->pluck('id');
            foreach ($affectedMinistryIds as $ministryId) {
                DB::table('ministry_person')->updateOrInsert(
                    ['ministry_id' => $ministryId, 'person_id' => $personA->id],
                    ['role' => 'leader']
                );
            }

            // 7. Soft delete secondary
            $personB->delete();

            Log::info('People merged with field selection', [
                'primary_id' => $personA->id,
                'secondary_id' => $personB->id,
                'primary_name' => $personA->full_name,
                'secondary_name' => $personB->full_name,
                'field_selections' => $fieldSelections,
            ]);
        });
    }

    /**
     * Remove semantic duplicate family relationships for a person.
     *
     * After merge, the same relationship can exist in opposite directions:
     * e.g., A→C "parent" and C→A "child" mean the same thing.
     * This method detects and removes such duplicates, keeping the row with the lower ID.
     */
    private function cleanSemanticFamilyDuplicates(int $personId): void
    {
        $relationships = DB::table('family_relationships')
            ->where(function ($q) use ($personId) {
                $q->where('person_id', $personId)
                    ->orWhere('related_person_id', $personId);
            })
            ->orderBy('id')
            ->get();

        $seen = [];
        $toDelete = [];

        foreach ($relationships as $rel) {
            // Normalize to a canonical form: (smaller_person_id, larger_person_id, normalized_type)
            // For asymmetric types (parent/child), normalize so the type always describes
            // the relationship from the person with the smaller ID to the larger ID.
            $personA = min((int) $rel->person_id, (int) $rel->related_person_id);
            $personB = max((int) $rel->person_id, (int) $rel->related_person_id);

            if ((int) $rel->person_id === $personA) {
                // Direction is already A→B, keep type as-is
                $normalizedType = $rel->relationship_type;
            } else {
                // Direction is B→A, invert the type to get A→B perspective
                $normalizedType = \App\Models\FamilyRelationship::getInverseType($rel->relationship_type);
            }

            $canonicalKey = "{$personA}-{$personB}-{$normalizedType}";

            if (isset($seen[$canonicalKey])) {
                // This is a semantic duplicate — mark for deletion (keep the earlier one)
                $toDelete[] = $rel->id;
            } else {
                $seen[$canonicalKey] = $rel->id;
            }
        }

        if (! empty($toDelete)) {
            DB::table('family_relationships')
                ->whereIn('id', $toDelete)
                ->delete();
        }
    }
}
