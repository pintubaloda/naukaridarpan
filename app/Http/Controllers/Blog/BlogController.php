<?php
namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $r)
    {
        $query = BlogPost::published()->orderByDesc('published_at');
        if ($r->category) $query->where('category', $r->category);
        if ($r->search)   $query->where(fn($q) => $q->where('title','like','%'.$r->search.'%')->orWhere('content','like','%'.$r->search.'%'));
        $posts      = $query->paginate(12)->withQueryString();
        $categories = BlogPost::published()->select('category')->distinct()->pluck('category')->filter();
        $featured   = BlogPost::published()->orderByDesc('view_count')->first();
        return view('blog.index', compact('posts', 'categories', 'featured'));
    }

    public function show(string $slug)
    {
        $post    = BlogPost::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $post->increment('view_count');
        $related = BlogPost::published()->where('category', $post->category)->where('id', '!=', $post->id)->orderByDesc('published_at')->take(3)->get();
        return view('blog.show', compact('post', 'related'));
    }
}
