<?php

namespace App\Http\Controllers;

use App\Models\Ministry;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class ResourceController extends Controller
{
    /**
     * Display resources (root or folder contents)
     * Only shows church-wide resources (ministry_id IS NULL)
     */
    public function index(Request $request, ?Resource $folder = null)
    {
        if (!auth()->user()->canView('resources')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        $churchId = $this->getCurrentChurch()->id;

        // Validate folder belongs to church and is a general resource (not ministry-specific)
        if ($folder && ($folder->church_id !== $churchId || $folder->ministry_id !== null)) {
            abort(404);
        }

        $resources = Resource::where('church_id', $churchId)
            ->whereNull('ministry_id')
            ->where('parent_id', $folder?->id)
            ->with('creator')
            ->orderByRaw("type = 'folder' DESC")
            ->orderBy('name')
            ->get();

        $breadcrumbs = $folder ? $folder->getBreadcrumbs() : [];

        // Calculate storage usage
        $storageUsed = Resource::getChurchUsage($churchId);
        $storageLimit = Resource::MAX_CHURCH_STORAGE;
        $storagePercent = $storageLimit > 0 ? round(($storageUsed / $storageLimit) * 100, 1) : 0;

        return view('resources.index', compact(
            'resources',
            'folder',
            'breadcrumbs',
            'storageUsed',
            'storageLimit',
            'storagePercent'
        ));
    }

    /**
     * Create a new folder
     */
    public function createFolder(Request $request)
    {
        if (!auth()->user()->canCreate('resources')) {
            abort(403);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:resources,id',
            'icon' => 'nullable|string|max:10',
        ]);

        $churchId = $this->getCurrentChurch()->id;

        // Validate parent belongs to church
        if ($validated['parent_id']) {
            $parent = Resource::where('id', $validated['parent_id'])
                ->where('church_id', $churchId)
                ->where('type', 'folder')
                ->firstOrFail();
        }

        Resource::create([
            'church_id' => $churchId,
            'parent_id' => $validated['parent_id'] ?? null,
            'created_by' => auth()->id(),
            'name' => $validated['name'],
            'type' => 'folder',
            'icon' => $validated['icon'] ?? null,
        ]);

        return back()->with('success', 'Папку створено');
    }

    /**
     * Upload a file
     */
    public function upload(Request $request)
    {
        if (!auth()->user()->canCreate('resources')) {
            abort(403);
        }
        $churchId = $this->getCurrentChurch()->id;

        // Convert empty string to null for parent_id
        if ($request->parent_id === '') {
            $request->merge(['parent_id' => null]);
        }

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . (Resource::MAX_FILE_SIZE / 1024), // KB
            ],
            'parent_id' => 'nullable|exists:resources,id',
        ]);

        $file = $request->file('file');

        // Check mime type
        if (!in_array($file->getMimeType(), Resource::ALLOWED_MIMES)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Цей тип файлу не підтримується'], 422);
            }
            return back()->with('error', 'Цей тип файлу не підтримується');
        }

        // Check church storage limit
        if (!Resource::canUpload($churchId, $file->getSize())) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Перевищено ліміт сховища. Видаліть непотрібні файли.'], 422);
            }
            return back()->with('error', 'Перевищено ліміт сховища. Видаліть непотрібні файли.');
        }

        // Validate parent belongs to church
        if ($request->parent_id) {
            Resource::where('id', $request->parent_id)
                ->where('church_id', $churchId)
                ->where('type', 'folder')
                ->firstOrFail();
        }

        // Store file
        $path = $file->store("resources/{$churchId}", 'public');

        Resource::create([
            'church_id' => $churchId,
            'parent_id' => $request->parent_id,
            'created_by' => auth()->id(),
            'name' => $file->getClientOriginalName(),
            'type' => 'file',
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return back()->with('success', 'Файл завантажено');
    }

    /**
     * Download a file
     */
    public function download(Resource $resource)
    {
        if ($resource->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        if (!$resource->isFile() || !$resource->file_path) {
            abort(404);
        }

        return Storage::disk('public')->download($resource->file_path, $resource->name);
    }

    /**
     * Rename a resource
     */
    public function rename(Request $request, Resource $resource)
    {
        if ($resource->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
        if (!auth()->user()->canEdit('resources')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $resource->update(['name' => $validated['name']]);

        return back()->with('success', 'Перейменовано');
    }

    /**
     * Delete a resource
     */
    public function destroy(Resource $resource)
    {
        if ($resource->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
        if (!auth()->user()->canDelete('resources')) {
            abort(403);
        }

        // If it's a file, delete from storage
        if ($resource->isFile() && $resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }

        // If it's a folder, recursively delete children files from storage
        if ($resource->isFolder()) {
            $this->deleteChildrenFiles($resource);
        }

        $resource->delete();

        return back()->with('success', 'Видалено');
    }

    /**
     * Move a resource to another folder
     */
    public function move(Request $request, Resource $resource)
    {
        if ($resource->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
        if (!auth()->user()->canEdit('resources')) {
            abort(403);
        }

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:resources,id',
        ]);

        // Can't move folder into itself or its children
        if ($resource->isFolder() && $validated['parent_id']) {
            $targetParent = Resource::find($validated['parent_id']);
            if ($targetParent && $this->isDescendant($resource, $targetParent)) {
                return back()->with('error', 'Не можна перемістити папку в саму себе');
            }
        }

        // Validate target parent belongs to church
        if ($validated['parent_id']) {
            Resource::where('id', $validated['parent_id'])
                ->where('church_id', $this->getCurrentChurch()->id)
                ->where('type', 'folder')
                ->firstOrFail();
        }

        $resource->update(['parent_id' => $validated['parent_id']]);

        return back()->with('success', 'Переміщено');
    }

    /**
     * Helper: Delete all children files from storage
     */
    private function deleteChildrenFiles(Resource $folder): void
    {
        foreach ($folder->children as $child) {
            if ($child->isFile() && $child->file_path) {
                Storage::disk('public')->delete($child->file_path);
            } elseif ($child->isFolder()) {
                $this->deleteChildrenFiles($child);
            }
        }
    }

    /**
     * Helper: Check if target is a descendant of resource
     */
    private function isDescendant(Resource $resource, Resource $target): bool
    {
        $current = $target;
        while ($current) {
            if ($current->id === $resource->id) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }

    // ==================== Ministry Resources ====================

    /**
     * Display ministry resources
     */
    public function ministryIndex(Ministry $ministry, ?Resource $folder = null)
    {
        Gate::authorize('view-ministry', $ministry);

        $churchId = $this->getCurrentChurch()->id;

        // Validate folder belongs to this ministry
        if ($folder && ($folder->church_id !== $churchId || $folder->ministry_id !== $ministry->id)) {
            abort(404);
        }

        $resources = Resource::where('church_id', $churchId)
            ->where('ministry_id', $ministry->id)
            ->where('parent_id', $folder?->id)
            ->with('creator')
            ->orderByRaw("type = 'folder' DESC")
            ->orderBy('name')
            ->get();

        $breadcrumbs = $folder ? $folder->getBreadcrumbs() : [];

        // Calculate storage usage for ministry
        $storageUsed = Resource::where('ministry_id', $ministry->id)->where('type', 'file')->sum('file_size') ?? 0;
        $storageLimit = Resource::MAX_CHURCH_STORAGE;
        $storagePercent = $storageLimit > 0 ? round(($storageUsed / $storageLimit) * 100, 1) : 0;

        return view('ministries.resources', compact(
            'ministry',
            'resources',
            'folder',
            'breadcrumbs',
            'storageUsed',
            'storageLimit',
            'storagePercent'
        ));
    }

    /**
     * Create folder in ministry resources
     */
    public function ministryCreateFolder(Request $request, Ministry $ministry)
    {
        Gate::authorize('contribute-ministry', $ministry);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:resources,id',
            'icon' => 'nullable|string|max:10',
        ]);

        $churchId = $this->getCurrentChurch()->id;

        // Validate parent belongs to ministry
        if ($validated['parent_id']) {
            Resource::where('id', $validated['parent_id'])
                ->where('church_id', $churchId)
                ->where('ministry_id', $ministry->id)
                ->where('type', 'folder')
                ->firstOrFail();
        }

        Resource::create([
            'church_id' => $churchId,
            'ministry_id' => $ministry->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'created_by' => auth()->id(),
            'name' => $validated['name'],
            'type' => 'folder',
            'icon' => $validated['icon'] ?? null,
        ]);

        return back()->with('success', 'Папку створено');
    }

    /**
     * Create a document in ministry resources
     */
    public function ministryCreateDocument(Request $request, Ministry $ministry)
    {
        Gate::authorize('contribute-ministry', $ministry);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:resources,id',
        ]);

        $churchId = $this->getCurrentChurch()->id;

        if ($validated['parent_id'] ?? null) {
            Resource::where('id', $validated['parent_id'])
                ->where('church_id', $churchId)
                ->where('ministry_id', $ministry->id)
                ->where('type', 'folder')
                ->firstOrFail();
        }

        $resource = Resource::create([
            'church_id' => $churchId,
            'ministry_id' => $ministry->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'created_by' => auth()->id(),
            'name' => $validated['name'],
            'type' => 'document',
            'content' => '',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'id' => $resource->id]);
        }

        return back()->with('success', 'Документ створено');
    }

    /**
     * Update document content
     */
    public function updateDocument(Request $request, Resource $resource)
    {
        if ($resource->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        // Ministry documents require ministry contribution permission
        if ($resource->ministry_id) {
            $ministry = Ministry::findOrFail($resource->ministry_id);
            Gate::authorize('contribute-ministry', $ministry);
        }

        if (!$resource->isDocument()) {
            abort(422, 'Це не документ');
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:2000000',
        ]);

        $data = ['content' => $validated['content'] ?? ''];
        if (!empty($validated['name'])) {
            $data['name'] = $validated['name'];
        }
        $resource->update($data);

        return response()->json(['success' => true]);
    }

    /**
     * Upload file to ministry resources
     */
    public function ministryUpload(Request $request, Ministry $ministry)
    {
        Gate::authorize('contribute-ministry', $ministry);

        $churchId = $this->getCurrentChurch()->id;

        if ($request->parent_id === '') {
            $request->merge(['parent_id' => null]);
        }

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . (Resource::MAX_FILE_SIZE / 1024),
            ],
            'parent_id' => 'nullable|exists:resources,id',
        ]);

        $file = $request->file('file');

        if (!in_array($file->getMimeType(), Resource::ALLOWED_MIMES)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Цей тип файлу не підтримується'], 422);
            }
            return back()->with('error', 'Цей тип файлу не підтримується');
        }

        if (!Resource::canUpload($churchId, $file->getSize())) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Перевищено ліміт сховища'], 422);
            }
            return back()->with('error', 'Перевищено ліміт сховища');
        }

        // Validate parent belongs to ministry
        if ($request->parent_id) {
            Resource::where('id', $request->parent_id)
                ->where('church_id', $churchId)
                ->where('ministry_id', $ministry->id)
                ->where('type', 'folder')
                ->firstOrFail();
        }

        $path = $file->store("resources/{$churchId}/ministry-{$ministry->id}", 'public');

        Resource::create([
            'church_id' => $churchId,
            'ministry_id' => $ministry->id,
            'parent_id' => $request->parent_id,
            'created_by' => auth()->id(),
            'name' => $file->getClientOriginalName(),
            'type' => 'file',
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return back()->with('success', 'Файл завантажено');
    }
}
