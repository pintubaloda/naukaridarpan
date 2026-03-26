<?php
namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\{Purchase, ExamPaper, ExamAttempt};
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function dashboard()
    {
        $user    = auth()->user();
        $profile = $user->sellerProfile;

        $recentSales = Purchase::whereHas('examPaper', fn($q) => $q->where('seller_id', $user->id))
            ->where('payment_status', 'paid')
            ->with(['examPaper', 'student'])
            ->orderByDesc('created_at')->take(8)->get();

        $paperCount   = ExamPaper::where('seller_id', $user->id)->count();
        $approvedCount= ExamPaper::where('seller_id', $user->id)->where('status', 'approved')->count();
        $pendingCount = ExamPaper::where('seller_id', $user->id)->where('status', 'pending_review')->count();

        $chartData = Purchase::whereHas('examPaper', fn($q) => $q->where('seller_id', $user->id))
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as sales, SUM(seller_credit) as revenue')
            ->groupBy('date')->orderBy('date')->take(30)->get();

        return view('seller.dashboard', compact('user', 'profile', 'recentSales', 'paperCount', 'approvedCount', 'pendingCount', 'chartData'));
    }

    public function profile()
    {
        return view('seller.profile', ['user' => auth()->user(), 'profile' => auth()->user()->sellerProfile]);
    }

    public function updateProfile(Request $r)
    {
        $r->validate([
            'name'           => 'required|string|max:100',
            'bio'            => 'nullable|string|max:1000',
            'qualification'  => 'nullable|string|max:200',
            'institution'    => 'nullable|string|max:200',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'website'        => 'nullable|url',
            'youtube_channel'=> 'nullable|url',
            'linkedin'       => 'nullable|url',
        ]);

        auth()->user()->update(['name' => $r->name]);
        auth()->user()->sellerProfile->update($r->only(
            'bio','qualification','institution','city','state','website','youtube_channel','linkedin'
        ));

        return back()->with('success', 'Profile updated successfully.');
    }

    public function analytics()
    {
        $user = auth()->user();

        $monthlySales = Purchase::whereHas('examPaper', fn($q) => $q->where('seller_id', $user->id))
            ->where('payment_status', 'paid')
            ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as count, SUM(seller_credit) as revenue")
            ->groupBy('month')->orderBy('month')->take(12)->get();

        $topPapers = ExamPaper::where('seller_id', $user->id)
            ->orderByDesc('total_purchases')->take(5)->get();

        return view('seller.analytics', compact('monthlySales', 'topPapers'));
    }
}
