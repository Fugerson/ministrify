<?php

namespace App\Http\Controllers;

use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        // Initialize onboarding if not started or state is corrupt
        if (!is_array($user->onboarding_state)) {
            $user->startOnboarding();
            $user->refresh();
        }

        $currentStep = $user->getCurrentOnboardingStep();
        $steps = User::ONBOARDING_STEPS;
        $stepsState = $user->onboarding_state['steps'] ?? [];
        $progress = $user->getOnboardingProgress();
        $church = $this->getCurrentChurch();

        return view('onboarding.wizard', compact(
            'user',
            'currentStep',
            'steps',
            'stepsState',
            'progress',
            'church'
        ));
    }

    public function step(string $step)
    {
        if (!array_key_exists($step, User::ONBOARDING_STEPS)) {
            abort(404);
        }

        $user = auth()->user();
        $church = $this->getCurrentChurch();

        // Gather step-specific data
        $data = match ($step) {
            'church_profile' => [
                'church' => $church,
            ],
            'first_ministry' => [
                'ministries' => $church->ministries()->get(),
            ],
            'add_people' => [
                'peopleCount' => $church->people()->count(),
            ],
            'set_roles' => [
                'users' => $church->users()->get(),
            ],
            default => [],
        };

        return view("onboarding.steps.{$step}", array_merge($data, [
            'step' => $step,
            'stepConfig' => User::ONBOARDING_STEPS[$step],
            'user' => $user,
        ]));
    }

    public function saveStep(Request $request, string $step)
    {
        if (!array_key_exists($step, User::ONBOARDING_STEPS)) {
            abort(404);
        }

        $user = auth()->user();
        $church = $this->getCurrentChurch();

        try {
            // Handle step-specific logic
            $savedData = match ($step) {
                'welcome' => $this->handleWelcome($request),
                'church_profile' => $this->handleChurchProfile($request, $church),
                'first_ministry' => $this->handleFirstMinistry($request, $church),
                'add_people' => $this->handleAddPeople($request, $church),
                'set_roles' => $this->handleSetRoles($request, $church),
                'feature_tour' => $this->handleFeatureTour($request),
                default => [],
            };
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Помилка валідації',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        $user->completeOnboardingStep($step, $savedData);

        // Check if this was the last step
        if ($step === 'feature_tour') {
            $user->finishOnboarding();
            return response()->json([
                'success' => true,
                'completed' => true,
                'redirect' => route('dashboard'),
            ]);
        }

        // Get next step
        $steps = array_keys(User::ONBOARDING_STEPS);
        $currentIndex = array_search($step, $steps);
        $nextStep = $steps[$currentIndex + 1] ?? null;

        return response()->json([
            'success' => true,
            'nextStep' => $nextStep,
            'progress' => $user->getOnboardingProgress(),
        ]);
    }

    public function skip(Request $request, string $step)
    {
        if (!array_key_exists($step, User::ONBOARDING_STEPS)) {
            abort(404);
        }

        $stepConfig = User::ONBOARDING_STEPS[$step];
        if ($stepConfig['required']) {
            return response()->json([
                'success' => false,
                'message' => 'Цей крок обов\'язковий.',
            ], 400);
        }

        $user = auth()->user();
        $user->skipOnboardingStep($step);

        $steps = array_keys(User::ONBOARDING_STEPS);
        $currentIndex = array_search($step, $steps);
        $nextStep = $steps[$currentIndex + 1] ?? null;

        return response()->json([
            'success' => true,
            'nextStep' => $nextStep,
            'progress' => $user->getOnboardingProgress(),
        ]);
    }

    public function complete()
    {
        $user = auth()->user();
        $user->finishOnboarding();

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);
    }

    public function restart()
    {
        $user = auth()->user();
        $user->restartOnboarding();

        return response()->json([
            'success' => true,
            'redirect' => route('onboarding.show'),
        ]);
    }

    public function dismissHint(Request $request)
    {
        $request->validate(['hint' => 'required|string']);

        $user = auth()->user();
        $user->dismissOnboardingHint($request->hint);

        return response()->json(['success' => true]);
    }

    // Step handlers

    private function handleWelcome(Request $request): array
    {
        return ['viewed' => true];
    }

    private function handleChurchProfile(Request $request, $church): array
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'public_email' => 'nullable|email|max:255',
            'public_phone' => 'nullable|string|max:50',
            'logo' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:2048',
        ]);

        // Use existing name if not provided
        if (empty($validated['name'])) {
            $validated['name'] = $church->name;
        }

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($church->logo) {
                Storage::disk('public')->delete($church->logo);
            }
            $stored = ImageService::storeWithHeicConversion($request->file('logo'), 'logos');
            $validated['logo'] = $stored['path'];
        }

        // Only update fields that were actually submitted
        $updateData = array_filter($validated, fn($value) => $value !== null && $value !== '');
        if (!empty($updateData)) {
            $church->update($updateData);
        }

        return ['updated_fields' => array_keys($updateData)];
    }

    private function handleFirstMinistry(Request $request, $church): array
    {
        // Allow completing without data (optional step)
        $ministries = $request->input('ministries', []);

        if (empty($ministries)) {
            return ['skipped' => true];
        }

        $validated = $request->validate([
            'ministries' => 'required|array|min:1',
            'ministries.*' => 'required|string|max:255',
        ]);

        $createdIds = [];
        foreach ($validated['ministries'] as $name) {
            $name = trim($name);
            if (empty($name)) continue;

            // Check if ministry with this name already exists
            $exists = $church->ministries()->where('name', $name)->exists();
            if ($exists) continue;

            $ministry = $church->ministries()->create([
                'name' => $name,
                'slug' => Str::slug($name) . '-' . Str::random(4),
            ]);
            $createdIds[] = $ministry->id;
        }

        return ['ministry_ids' => $createdIds, 'created_count' => count($createdIds)];
    }

    private function handleAddPeople(Request $request, $church): array
    {
        $mode = $request->input('mode', 'manual');
        $added = [];

        if ($mode === 'manual') {
            // Check if people array is provided and has data
            $people = $request->input('people', []);
            $hasValidPeople = collect($people)->filter(fn($p) => !empty($p['first_name']))->isNotEmpty();

            if (!$hasValidPeople) {
                // Allow completing without data (optional step)
                return ['mode' => $mode, 'added_count' => 0, 'skipped' => true];
            }

            $validated = $request->validate([
                'people' => 'required|array|min:1',
                'people.*.first_name' => 'required|string|max:255',
                'people.*.last_name' => 'nullable|string|max:255',
                'people.*.email' => 'nullable|email|max:255',
                'people.*.phone' => 'nullable|string|max:50',
            ]);

            foreach ($validated['people'] as $personData) {
                if (empty($personData['first_name'])) continue;

                $person = $church->people()->create([
                    'first_name' => $personData['first_name'],
                    'last_name' => $personData['last_name'] ?? null,
                    'email' => $personData['email'] ?? null,
                    'phone' => $personData['phone'] ?? null,
                ]);
                $added[] = $person->id;
            }
        } elseif ($mode === 'csv') {
            // Check if CSV file is provided
            if (!$request->hasFile('csv_file')) {
                return ['mode' => $mode, 'added_count' => 0, 'skipped' => true];
            }

            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            ]);

            $file = $request->file('csv_file');
            $handle = fopen($file->getPathname(), 'r');
            $header = fgetcsv($handle);

            // Map columns
            $columnMap = [
                'first_name' => $this->findColumn($header, ['first_name', 'first name', 'firstname', "ім'я", 'імя']),
                'last_name' => $this->findColumn($header, ['last_name', 'last name', 'lastname', 'прізвище']),
                'email' => $this->findColumn($header, ['email', 'e-mail', 'пошта', 'електронна пошта']),
                'phone' => $this->findColumn($header, ['phone', 'telephone', 'телефон', 'мобільний']),
            ];

            while (($row = fgetcsv($handle)) !== false) {
                $personData = [];
                foreach ($columnMap as $field => $index) {
                    if ($index !== null && isset($row[$index])) {
                        $personData[$field] = trim($row[$index]);
                    }
                }

                if (!empty($personData['first_name'])) {
                    $person = $church->people()->create($personData);
                    $added[] = $person->id;
                }
            }

            fclose($handle);
        }

        return ['mode' => $mode, 'added_count' => count($added), 'people_ids' => $added];
    }

    private function findColumn(array $header, array $possibleNames): ?int
    {
        $header = array_map('strtolower', $header);
        foreach ($possibleNames as $name) {
            $index = array_search(strtolower($name), $header);
            if ($index !== false) {
                return $index;
            }
        }
        return null;
    }

    private function handleSetRoles(Request $request, $church): array
    {
        // Check if users array is provided and has data
        $users = $request->input('users', []);
        $hasValidUsers = collect($users)->filter(fn($u) => !empty($u['email']) && !empty($u['name']))->isNotEmpty();

        if (!$hasValidUsers) {
            // Allow completing without data (optional step)
            return ['created_users' => [], 'skipped' => true];
        }

        $validated = $request->validate([
            'users' => 'required|array|min:1',
            'users.*.email' => 'required|email|max:255',
            'users.*.name' => 'required|string|max:255',
            'users.*.role' => 'required|in:admin,leader,volunteer',
        ]);

        $created = [];

        foreach ($validated['users'] as $userData) {
            // Check if user already exists
            $existingUser = User::where('email', $userData['email'])->first();
            if ($existingUser) {
                continue;
            }

            // Find matching ChurchRole for the legacy role name
            // Map legacy 'admin' role to 'administrator' slug
            $roleSlug = $userData['role'] === 'admin' ? 'administrator' : $userData['role'];
            $churchRole = \App\Models\ChurchRole::where('church_id', $church->id)
                ->where('slug', $roleSlug)
                ->first();

            $user = $church->users()->create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make(Str::random(16)),
                'role' => $userData['role'],
                'church_role_id' => $churchRole?->id,
                'onboarding_completed' => true, // Skip onboarding for invited users
            ]);

            // Create or merge Person record by email
            $nameParts = explode(' ', $userData['name'], 2);
            $person = \App\Models\Person::where('email', $userData['email'])
                ->where('church_id', $church->id)
                ->first();

            if (!$person) {
                $person = \App\Models\Person::create([
                    'church_id' => $church->id,
                    'user_id' => $user->id,
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $userData['email'],
                    'membership_status' => 'member',
                ]);
            } else {
                // Merge: update existing person with user link and latest data
                $person->update([
                    'user_id' => $user->id,
                    'membership_status' => 'member',
                ]);
            }

            // Create pivot record
            \Illuminate\Support\Facades\DB::table('church_user')->updateOrInsert(
                ['user_id' => $user->id, 'church_id' => $church->id],
                [
                    'church_role_id' => $churchRole?->id,
                    'person_id' => $person->id,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $created[] = $user->id;
        }

        return ['created_users' => $created];
    }

    private function handleFeatureTour(Request $request): array
    {
        return ['tour_completed' => true];
    }
}
