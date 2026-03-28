<?php

namespace App\Livewire;

use App\Events\ChurchDataUpdated;
use App\Models\Attendance;
use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AttendanceCheckin extends Component
{
    public Attendance $attendance;

    public array $presentIds = [];

    public string $search = '';

    public int $totalCount = 0;

    public int $guestsCount = 0;

    public string $notes = '';

    public function mount(Attendance $attendance): void
    {
        $this->attendance = $attendance->load('records');
        $this->presentIds = $attendance->records->where('present', true)->pluck('person_id')->toArray();
        $this->totalCount = $attendance->total_count ?? count($this->presentIds);
        $this->guestsCount = $attendance->guests_count ?? 0;
        $this->notes = $attendance->notes ?? '';
    }

    public function togglePerson(int $personId): void
    {
        if (in_array($personId, $this->presentIds)) {
            $this->presentIds = array_values(array_diff($this->presentIds, [$personId]));
            $this->attendance->markAbsent(Person::find($personId));
        } else {
            $this->presentIds[] = $personId;
            $this->attendance->markPresent(Person::find($personId));
        }

        $this->attendance->recalculateCounts();
        $this->totalCount = $this->attendance->fresh()->total_count;
    }

    public function selectAll(): void
    {
        $churchId = $this->attendance->church_id;
        $allIds = Person::where('church_id', $churchId)
            ->notGuest()
            ->pluck('id')
            ->toArray();

        $this->presentIds = $allIds;
        $this->saveAll();
    }

    public function clearAll(): void
    {
        $this->presentIds = [];
        $this->saveAll();
    }

    public function updateGuestsCount(): void
    {
        $this->attendance->update(['guests_count' => max(0, $this->guestsCount)]);
        $this->attendance->recalculateCounts();
        $this->totalCount = $this->attendance->fresh()->total_count;
    }

    public function updateNotes(): void
    {
        $this->attendance->update(['notes' => $this->notes]);
    }

    public function render(): View
    {
        $churchId = $this->attendance->church_id;

        $people = Person::where('church_id', $churchId)
            ->notGuest()
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $presentCount = count($this->presentIds);

        return view('livewire.attendance-checkin', compact('people', 'presentCount'));
    }

    protected function saveAll(): void
    {
        DB::transaction(function () {
            $this->attendance->records()->delete();

            $records = collect($this->presentIds)->map(fn ($id) => [
                'attendance_id' => $this->attendance->id,
                'person_id' => $id,
                'present' => true,
                'checked_in_at' => now()->format('H:i'),
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            if ($records) {
                DB::table('attendance_records')->insert($records);
            }

            $this->attendance->recalculateCounts();
            $this->totalCount = $this->attendance->fresh()->total_count;
        });

        ChurchDataUpdated::dispatch($this->attendance->church_id);
    }
}
