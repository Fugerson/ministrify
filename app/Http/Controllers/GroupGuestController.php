<?php

namespace App\Http\Controllers;

use App\Events\ChurchDataUpdated;
use App\Models\Group;
use App\Models\GroupGuest;
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
        $validated['group_id'] = $group->id;

        GroupGuest::create($validated);

        broadcast(new ChurchDataUpdated($this->getCurrentChurch()->id, 'groups', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.guest_added'), 'groups.show', ['group' => $group->id]);
    }

    public function update(Request $request, Group $group, GroupGuest $guest)
    {
        $this->authorize('update', $group);
        abort_unless($guest->group_id === $group->id, 404);

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

        broadcast(new ChurchDataUpdated($this->getCurrentChurch()->id, 'groups', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.guest_updated'), 'groups.show', ['group' => $group->id]);
    }

    public function destroy(Request $request, Group $group, GroupGuest $guest)
    {
        $this->authorize('update', $group);
        abort_unless($guest->group_id === $group->id, 404);

        $guest->delete();

        broadcast(new ChurchDataUpdated($this->getCurrentChurch()->id, 'groups', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.guest_deleted'), 'groups.show', ['group' => $group->id]);
    }
}
