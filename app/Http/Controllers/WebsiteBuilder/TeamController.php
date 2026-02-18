<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RequiresChurch;
use App\Models\StaffMember;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    use RequiresChurch;

    public function index()
    {
        $church = $this->getChurchOrFail();
        $staffMembers = $church->staffMembers()->orderBy('sort_order')->get();
        $roleCategories = StaffMember::CATEGORIES;

        return view('website-builder.team.index', compact('church', 'staffMembers', 'roleCategories'));
    }

    public function create()
    {
        $church = $this->getChurchOrFail();
        $roleCategories = StaffMember::CATEGORIES;

        return view('website-builder.team.create', compact('church', 'roleCategories'));
    }

    public function store(Request $request)
    {
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'role_category' => 'nullable|string|in:' . implode(',', array_keys(StaffMember::CATEGORIES)),
            'bio' => 'nullable|string|max:2000',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'photo' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:2048',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'is_public' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $stored = ImageService::storeWithHeicConversion($request->file('photo'), "churches/{$church->id}/team");
            $validated['photo'] = $stored['path'];
        }

        $validated['church_id'] = $church->id;
        $validated['sort_order'] = $church->staffMembers()->max('sort_order') + 1;

        StaffMember::create($validated);

        return redirect()->route('website-builder.team.index')->with('success', 'Члена команди додано');
    }

    public function edit(StaffMember $staffMember)
    {
        $this->authorize('view', $staffMember);
        $church = $this->getChurchOrFail();
        $roleCategories = StaffMember::CATEGORIES;

        return view('website-builder.team.edit', compact('church', 'staffMember', 'roleCategories'));
    }

    public function update(Request $request, StaffMember $staffMember)
    {
        $this->authorize('update', $staffMember);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'role_category' => 'nullable|string|in:' . implode(',', array_keys(StaffMember::CATEGORIES)),
            'bio' => 'nullable|string|max:2000',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'photo' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:2048',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'is_public' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($staffMember->photo) {
                Storage::disk('public')->delete($staffMember->photo);
            }
            $stored = ImageService::storeWithHeicConversion($request->file('photo'), "churches/{$staffMember->church_id}/team");
            $validated['photo'] = $stored['path'];
        }

        $staffMember->update($validated);

        return redirect()->route('website-builder.team.index')->with('success', 'Дані оновлено');
    }

    public function destroy(StaffMember $staffMember)
    {
        $this->authorize('delete', $staffMember);

        if ($staffMember->photo) {
            Storage::disk('public')->delete($staffMember->photo);
        }

        $staffMember->delete();

        return redirect()->route('website-builder.team.index')->with('success', 'Члена команди видалено');
    }

    public function reorder(Request $request)
    {
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:staff_members,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            StaffMember::where('id', $id)
                ->where('church_id', $church->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
