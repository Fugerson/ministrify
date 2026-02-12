<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Display all announcements
     */
    public function index()
    {
        if (!auth()->user()->canView('announcements')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        $user = auth()->user();
        $church = $this->getCurrentChurch();

        $announcements = Announcement::forChurch($church->id)
            ->published()
            ->with('author')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = Announcement::unreadCount($church->id, $user->id);

        return view('announcements.index', compact('announcements', 'unreadCount'));
    }

    /**
     * Show single announcement
     */
    public function show(Announcement $announcement)
    {
        $this->authorizeChurch($announcement);

        // Mark as read
        $announcement->markAsReadBy(auth()->user());

        return view('announcements.show', compact('announcement'));
    }

    /**
     * Create form
     */
    public function create()
    {
        if (!auth()->user()->canCreate('announcements')) {
            abort(403);
        }

        return view('announcements.create');
    }

    /**
     * Store new announcement
     */
    public function store(Request $request)
    {
        if (!auth()->user()->canCreate('announcements')) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'is_pinned' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $user = auth()->user();
        $church = $this->getCurrentChurch();

        $announcement = Announcement::create([
            'church_id' => $church->id,
            'author_id' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_pinned' => $request->boolean('is_pinned'),
            'is_published' => true,
            'published_at' => now(),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()->route('announcements.index')
            ->with('success', 'Оголошення опубліковано');
    }

    /**
     * Edit form
     */
    public function edit(Announcement $announcement)
    {
        $this->authorizeChurch($announcement);
        $this->authorizeAuthorOrPermission($announcement, 'edit');

        return view('announcements.edit', compact('announcement'));
    }

    /**
     * Update announcement
     */
    public function update(Request $request, Announcement $announcement)
    {
        $this->authorizeChurch($announcement);
        $this->authorizeAuthorOrPermission($announcement, 'edit');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'is_pinned' => 'boolean',
            'expires_at' => 'nullable|date',
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_pinned' => $request->boolean('is_pinned'),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()->route('announcements.index')
            ->with('success', 'Оголошення оновлено');
    }

    /**
     * Delete announcement
     */
    public function destroy(Announcement $announcement)
    {
        $this->authorizeChurch($announcement);
        $this->authorizeAuthorOrPermission($announcement, 'delete');

        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Оголошення видалено');
    }

    /**
     * Toggle pin status
     */
    /**
     * Author can edit/delete own announcements, otherwise require permission
     */
    protected function authorizeAuthorOrPermission(Announcement $announcement, string $action): void
    {
        $user = auth()->user();

        // Author can always manage their own
        if ($announcement->author_id === $user->id) {
            return;
        }

        // Otherwise require the specific permission
        $hasPermission = match ($action) {
            'edit' => $user->canEdit('announcements'),
            'delete' => $user->canDelete('announcements'),
            default => false,
        };

        if (!$hasPermission) {
            abort(403, 'Тільки автор або користувач з відповідними правами може керувати цим оголошенням.');
        }
    }

    public function togglePin(Announcement $announcement)
    {
        $this->authorizeChurch($announcement);
        $this->authorizeAuthorOrPermission($announcement, 'edit');

        $announcement->update(['is_pinned' => !$announcement->is_pinned]);

        return back()->with('success', $announcement->is_pinned ? 'Оголошення закріплено' : 'Оголошення відкріплено');
    }
}
