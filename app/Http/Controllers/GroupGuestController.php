<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Person;
use App\Services\ImageService;
use Illuminate\Http\Request;

class GroupGuestController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function store(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'photo' => 'nullable|image|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $this->imageService->storeProfilePhoto(
                $request->file('photo'),
                'people'
            );
        }

        $validated['church_id'] = $this->getCurrentChurch()->id;
        $validated['membership_status'] = 'guest';

        $person = Person::create($validated);

        $group->members()->attach($person->id, ['role' => Group::ROLE_GUEST]);

        return $this->successResponse($request, __('messages.guest_added'), 'groups.show', ['group' => $group->id]);
    }

    public function update(Request $request, Group $group, Person $guest)
    {
        $this->authorize('update', $group);
        abort_unless($group->guests()->where('people.id', $guest->id)->exists(), 404);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'photo' => 'nullable|image|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('photo')) {
            $this->imageService->delete($guest->photo);
            $validated['photo'] = $this->imageService->storeProfilePhoto(
                $request->file('photo'),
                'people'
            );
        }

        $guest->update($validated);

        return $this->successResponse($request, __('messages.guest_updated'), 'groups.show', ['group' => $group->id]);
    }

    public function destroy(Request $request, Group $group, Person $guest)
    {
        $this->authorize('update', $group);
        abort_unless($group->guests()->where('people.id', $guest->id)->exists(), 404);

        // Remove from group (detach pivot)
        $group->members()->detach($guest->id);

        return $this->successResponse($request, __('messages.guest_deleted'), 'groups.show', ['group' => $group->id]);
    }
}
