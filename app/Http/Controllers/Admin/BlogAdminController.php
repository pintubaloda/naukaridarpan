<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\AI\BlogGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogAdminController extends Controller
{
    public function index()
    {
        $posts = BlogPost::orderByDesc('created_at')->paginate(20);
        return view('admin.blog.index', compact('posts'));
    }

    public function create() { return view('admin.blog.create'); }

    public function store(Request $r)
    {
        $r->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'category'         => 'required|string|max:100',
            'excerpt'          => 'nullable|string|max:500',
            'featured_image'   => 'nullable|string|max:1000',
            'meta_title'       => 'nullable|string|max:80',
            'meta_description' => 'nullable|string|max:200',
            'tags'             => 'nullable|string',
            'status'           => 'required|in:draft,published',
        ]);
        BlogPost::create([
            'author_id'        => auth()->id(),
            'title'            => $r->title,
            'slug'             => Str::slug($r->title) . '-' . Str::random(4),
            'excerpt'          => $r->excerpt,
            'featured_image'   => $r->featured_image,
            'content'          => $r->content,
            'category'         => $r->category,
            'meta_title'       => $r->meta_title ?: $r->title,
            'meta_description' => $r->meta_description,
            'tags'             => $r->tags ? array_map('trim', explode(',', $r->tags)) : [],
            'status'           => $r->status,
            'published_at'     => $r->status === 'published' ? now() : null,
            'is_ai_generated'  => false,
        ]);
        return redirect()->route('admin.blog.index')->with('success', 'Post created.');
    }

    public function edit(BlogPost $post) { return view('admin.blog.edit', compact('post')); }

    public function update(Request $r, BlogPost $post)
    {
        $r->validate(['title'=>'required|string|max:255','content'=>'required|string','status'=>'required|in:draft,published,archived']);
        $post->update([
            'title'            => $r->title,
            'excerpt'          => $r->excerpt,
            'content'          => $r->content,
            'category'         => $r->category,
            'meta_title'       => $r->meta_title,
            'meta_description' => $r->meta_description,
            'featured_image'   => $r->featured_image,
            'tags'             => $r->tags ? array_map('trim', explode(',', $r->tags)) : [],
            'status'           => $r->status,
            'published_at'     => $r->status === 'published' && ! $post->published_at ? now() : $post->published_at,
        ]);
        return back()->with('success', 'Post updated.');
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();
        return redirect()->route('admin.blog.index')->with('success', 'Post deleted.');
    }

    public function generateAI(Request $r)
    {
        $r->validate(['language' => 'nullable|in:English,Hindi']);
        $service = app(BlogGeneratorService::class);
        $post    = $service->generateDailyPost($r->language ?? 'English');
        if ($post) return response()->json(['success' => true, 'post_id' => $post->id, 'title' => $post->title]);
        return response()->json(['success' => false, 'message' => 'AI generation failed. Check API key.'], 500);
    }
}
