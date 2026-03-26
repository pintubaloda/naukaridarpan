<?php
namespace App\Http\Controllers;
use App\Models\{ExamPaper,Category,User,BlogPost};
use Illuminate\Http\Request;
class MarketplaceController extends Controller {
    public function home() {
        $featuredExams = ExamPaper::approved()->with(['seller.sellerProfile','category'])->where('is_free',false)->orderByDesc('total_purchases')->take(8)->get();
        $freeExams     = ExamPaper::approved()->where('is_free',true)->with(['seller.sellerProfile','category'])->take(6)->get();
        $categories    = Category::whereNull('parent_id')->where('is_active',true)->withCount(['examPapers'=>fn($q)=>$q->where('status','approved')])->orderBy('sort_order')->get();
        $topSellers    = User::where('role','seller')->with('sellerProfile')->whereHas('sellerProfile',fn($q)=>$q->where('is_verified',true))->take(6)->get();
        $latestPosts   = BlogPost::published()->orderByDesc('published_at')->take(3)->get();
        $stats=['total_exams'=>ExamPaper::approved()->count(),'total_sellers'=>User::where('role','seller')->count(),'total_students'=>User::where('role','student')->count(),'free_exams'=>ExamPaper::approved()->where('is_free',true)->count()];
        return view('home',compact('featuredExams','freeExams','categories','topSellers','latestPosts','stats'));
    }
    public function browse(Request $r) {
        $query = ExamPaper::approved()->with(['seller.sellerProfile','category']);
        if($r->category) $query->whereHas('category',fn($q)=>$q->where('slug',$r->category));
        if($r->search)   $query->where(fn($q)=>$q->where('title','like','%'.$r->search.'%')->orWhere('description','like','%'.$r->search.'%'));
        if($r->difficulty) $query->where('difficulty',$r->difficulty);
        if($r->language)   $query->where('language',$r->language);
        if($r->price==='free') $query->where('is_free',true);
        elseif($r->price==='paid') $query->where('is_free',false);
        match($r->sort??'popular'){
            'newest'    =>$query->orderByDesc('created_at'),
            'price_asc' =>$query->orderBy('student_price'),
            'price_desc'=>$query->orderByDesc('student_price'),
            default     =>$query->orderByDesc('total_purchases')
        };
        $exams=$query->paginate(16)->withQueryString();
        $categories=Category::where('is_active',true)->withCount(['examPapers'=>fn($q)=>$q->where('status','approved')])->get();
        return view('browse',compact('exams','categories'));
    }
    public function show(string $slug) {
        $exam=ExamPaper::where('slug',$slug)->where('status','approved')->with(['seller.sellerProfile','category'])->firstOrFail();
        $purchased=false;
        if(auth()->check()) $purchased=$exam->purchases()->where('student_id',auth()->id())->where('payment_status','paid')->exists();
        $relatedExams=ExamPaper::approved()->where('category_id',$exam->category_id)->where('id','!=',$exam->id)->with('seller.sellerProfile')->take(4)->get();
        return view('exam-detail',compact('exam','purchased','relatedExams'));
    }
    public function professorProfile(string $username) {
        $profile=\App\Models\SellerProfile::where('username',$username)->with('user')->firstOrFail();
        $exams=ExamPaper::approved()->where('seller_id',$profile->user_id)->with('category')->orderByDesc('total_purchases')->paginate(12);
        return view('professor-profile',compact('profile','exams'));
    }
    public function category(string $slug) {
        $category=Category::where('slug',$slug)->where('is_active',true)->firstOrFail();
        $exams=ExamPaper::approved()->where('category_id',$category->id)->with(['seller.sellerProfile','category'])->orderByDesc('total_purchases')->paginate(16);
        return view('category',compact('category','exams'));
    }
    public function about()   { return view('about'); }
    public function contact() { return view('contact'); }
    public function contactSubmit(Request $r){ $r->validate(['name'=>'required','email'=>'required|email','message'=>'required']); return back()->with('success','Message sent! We will get back within 24 hours.'); }
}
