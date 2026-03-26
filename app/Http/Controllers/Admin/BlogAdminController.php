<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\PlatformSetting;
use App\Services\AI\BlogGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Please login again.'], 401);
        }
        $r->validate([
            'language' => 'nullable|in:English,Hindi',
            'topic'    => 'required|string|max:200',
            'category' => 'required|string|max:100',
        ]);
        $service = app(BlogGeneratorService::class);
        $data    = $service->generateDraft($r->topic, $r->language ?? 'English', $r->category);
        if ($data) return response()->json(['success' => true, 'data' => $data]);
        return response()->json(['success' => false, 'message' => 'AI generation failed. Check API key.'], 500);
    }

    public function searchImages(Request $r)
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Please login again.'], 401);
        }
        $r->validate([
            'query'  => 'required|string|max:200',
            'source' => 'required|in:google,pexels',
        ]);
        $query  = $r->query('query');
        $source = $r->query('source');

        if ($source === 'google') {
            $key = PlatformSetting::get('google_cse_api_key');
            $cx  = PlatformSetting::get('google_cse_cx');
            if (! $key || ! $cx) return response()->json(['success' => false, 'message' => 'Google CSE not configured.'], 422);
            $resp = Http::timeout(30)->get('https://www.googleapis.com/customsearch/v1', [
                'key' => $key,
                'cx' => $cx,
                'searchType' => 'image',
                'q' => $query,
                'num' => 10,
                'safe' => 'active',
            ]);
            $items = collect($resp->json('items', []))->map(function ($i) {
                return [
                    'thumb' => $i['image']['thumbnailLink'] ?? $i['link'] ?? null,
                    'url'   => $i['link'] ?? null,
                    'title' => $i['title'] ?? '',
                ];
            })->filter(fn($i) => $i['url'])->values();
            return response()->json(['success' => true, 'items' => $items]);
        }

        $pexelsKey = PlatformSetting::get('pexels_api_key');
        if (! $pexelsKey) return response()->json(['success' => false, 'message' => 'Pexels not configured.'], 422);
        $resp = Http::withHeaders(['Authorization' => $pexelsKey])->timeout(30)->get('https://api.pexels.com/v1/search', [
            'query' => $query,
            'per_page' => 10,
        ]);
        $items = collect($resp->json('photos', []))->map(function ($p) {
            return [
                'thumb' => $p['src']['medium'] ?? $p['src']['small'] ?? null,
                'url'   => $p['src']['large'] ?? $p['src']['original'] ?? null,
                'title' => $p['alt'] ?? '',
            ];
        })->filter(fn($i) => $i['url'])->values();
        return response()->json(['success' => true, 'items' => $items]);
    }

    public function attachImage(Request $r)
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Please login again.'], 401);
        }
        $r->validate(['image_url' => 'required|url']);
        $url = $r->input('image_url');
        $resp = Http::timeout(60)->get($url);
        if (! $resp->successful()) return response()->json(['success' => false, 'message' => 'Failed to download image.'], 422);
        $mime = $resp->header('Content-Type') ?? 'image/jpeg';
        $ext = str_contains($mime, 'png') ? 'png' : (str_contains($mime, 'webp') ? 'webp' : 'jpg');
        $path = 'blog/' . Str::uuid() . '.' . $ext;
        Storage::disk('public')->put($path, $resp->body());
        return response()->json(['success' => true, 'url' => Storage::url($path)]);
    }
}
