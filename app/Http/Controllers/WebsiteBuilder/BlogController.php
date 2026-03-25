<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RequiresChurch;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    use RequiresChurch;

    public function index(Request $request)
    {
        $church = $this->getChurchOrFail();

        $posts = $church->blogPosts()
            ->with('category', 'author')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->category, fn ($q, $cat) => $q->where('blog_category_id', $cat))
            ->latest('updated_at')
            ->paginate(20);

        $categories = $church->blogCategories()->ordered()->get();
        $statuses = BlogPost::STATUSES;

        return view('website-builder.blog.index', compact('church', 'posts', 'categories', 'statuses'));
    }

    public function create()
    {
        $church = $this->getChurchOrFail();
        $categories = $church->blogCategories()->ordered()->get();

        return view('website-builder.blog.create', compact('church', 'categories'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->canEdit('website'), 403);
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_posts', 'slug')->where('church_id', $church->id)],
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:2048',
            'blog_category_id' => ['nullable', Rule::exists('blog_categories', 'id')->where('church_id', $church->id)],
            'tags' => 'nullable|array',
            'status' => 'required|string|in:draft,published,scheduled,archived',
            'published_at' => 'nullable|date',
            'scheduled_at' => 'nullable|date|after:now',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'allow_comments' => 'boolean',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
        ]);

        if ($request->hasFile('featured_image')) {
            $stored = ImageService::storeWithHeicConversion($request->file('featured_image'), "churches/{$church->id}/blog");
            $validated['featured_image'] = $stored['path'];
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Ensure unique slug within church
        $baseSlug = $validated['slug'];
        $counter = 1;
        while ($church->blogPosts()->where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug.'-'.$counter++;
        }

        $validated['church_id'] = $church->id;
        $validated['author_id'] = auth()->id();

        // Set published_at if publishing now
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        BlogPost::create($validated);

        return $this->successResponse($request, 'Статтю створено', 'website-builder.blog.index');
    }

    public function edit(BlogPost $blogPost)
    {
        $this->authorizeChurch($blogPost);
        $this->authorize('view', $blogPost);
        $church = $this->getChurchOrFail();
        $categories = $church->blogCategories()->ordered()->get();

        return view('website-builder.blog.edit', compact('church', 'blogPost', 'categories'));
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $this->authorizeChurch($blogPost);
        $this->authorize('update', $blogPost);
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_posts', 'slug')->where('church_id', $church->id)->ignore($blogPost->id)],
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:2048',
            'blog_category_id' => ['nullable', Rule::exists('blog_categories', 'id')->where('church_id', $church->id)],
            'tags' => 'nullable|array',
            'status' => 'required|string|in:draft,published,scheduled,archived',
            'published_at' => 'nullable|date',
            'scheduled_at' => 'nullable|date|after:now',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'allow_comments' => 'boolean',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($blogPost->featured_image) {
                Storage::disk('public')->delete($blogPost->featured_image);
            }
            $stored = ImageService::storeWithHeicConversion($request->file('featured_image'), "churches/{$church->id}/blog");
            $validated['featured_image'] = $stored['path'];
        }

        // Set published_at if publishing for first time
        if ($validated['status'] === 'published' && $blogPost->status !== 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $blogPost->update($validated);

        return $this->successResponse($request, 'Статтю оновлено', 'website-builder.blog.index');
    }

    public function destroy(Request $request, BlogPost $blogPost)
    {
        $this->authorizeChurch($blogPost);
        $this->authorize('delete', $blogPost);

        if ($blogPost->featured_image) {
            Storage::disk('public')->delete($blogPost->featured_image);
        }

        $blogPost->delete();

        return $this->successResponse($request, 'Статтю видалено', 'website-builder.blog.index');
    }

    public function publish(Request $request, BlogPost $blogPost)
    {
        $this->authorizeChurch($blogPost);
        $this->authorize('update', $blogPost);
        $blogPost->publish();

        return $this->successResponse($request, 'Статтю опубліковано');
    }

    // Categories
    public function categoriesIndex()
    {
        $church = $this->getChurchOrFail();
        $categories = $church->blogCategories()->withCount('posts')->ordered()->get();

        return view('website-builder.blog.categories.index', compact('church', 'categories'));
    }

    public function categoryStore(Request $request)
    {
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['church_id'] = $church->id;
        $validated['sort_order'] = $church->blogCategories()->max('sort_order') + 1;

        BlogCategory::create($validated);

        return $this->successResponse($request, 'Категорію створено');
    }

    public function categoryUpdate(Request $request, BlogCategory $category)
    {
        $this->authorizeChurch($category);
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        $category->update($validated);

        return $this->successResponse($request, 'Категорію оновлено');
    }

    public function categoryDestroy(Request $request, BlogCategory $category)
    {
        $this->authorizeChurch($category);
        $this->authorize('delete', $category);

        // Move posts to uncategorized (null)
        $category->posts()->update(['blog_category_id' => null]);
        $category->delete();

        return $this->successResponse($request, 'Категорію видалено');
    }
}
