<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\N8nAutomationController;
use App\Http\Controllers\Student\ExamController;
use App\Http\Controllers\MarketplaceController;

/*
|--------------------------------------------------------------------------
| API Routes — Naukaridarpan
| Used by the Android student app (Kotlin + Jetpack Compose)
|--------------------------------------------------------------------------
*/

// ── PUBLIC API ──────────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    // Categories
    Route::get('/categories', function () {
        return response()->json(\App\Models\Category::where('is_active', true)->withCount(['examPapers' => fn($q) => $q->where('status', 'approved')])->orderBy('sort_order')->get());
    });

    // Browse exams
    Route::get('/exams', function (Request $r) {
        $q = \App\Models\ExamPaper::approved()->with(['seller:id,name', 'category:id,name,slug']);
        if ($r->category)    $q->whereHas('category', fn($c) => $c->where('slug', $r->category));
        if ($r->search)      $q->where('title', 'like', '%' . $r->search . '%');
        if ($r->is_free)     $q->where('is_free', true);
        if ($r->difficulty)  $q->where('difficulty', $r->difficulty);
        return response()->json($q->orderByDesc('total_purchases')->paginate(20));
    });

    // Exam detail
    Route::get('/exams/{slug}', function (string $slug) {
        $exam = \App\Models\ExamPaper::where('slug', $slug)->where('status', 'approved')->with(['seller.sellerProfile', 'category'])->firstOrFail();
        return response()->json($exam);
    });

    // Blog posts
    Route::get('/blog', function (Request $r) {
        $q = \App\Models\BlogPost::published()->orderByDesc('published_at');
        if ($r->category) $q->where('category', $r->category);
        return response()->json($q->paginate(15));
    });

    Route::get('/blog/{slug}', function (string $slug) {
        $post = \App\Models\BlogPost::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $post->increment('view_count');
        return response()->json($post);
    });

    // Professor profile
    Route::get('/professors/{username}', function (string $username) {
        $profile = \App\Models\SellerProfile::where('username', $username)->with('user')->firstOrFail();
        $exams   = \App\Models\ExamPaper::approved()->where('seller_id', $profile->user_id)->with('category')->paginate(12);
        return response()->json(['profile' => $profile, 'exams' => $exams]);
    });
});

Route::prefix('v1/automation')->group(function () {
    Route::get('/bootstrap', [N8nAutomationController::class, 'bootstrap']);
    Route::post('/sources/sync', [N8nAutomationController::class, 'syncSources']);
    Route::post('/blog/import', [N8nAutomationController::class, 'importBlogPosts']);
    Route::post('/professor-leads/import', [N8nAutomationController::class, 'importProfessorLeads']);
    Route::post('/exams/import', [N8nAutomationController::class, 'importExamPapers']);
    Route::get('/paper-sources', [N8nAutomationController::class, 'paperSources']);
    Route::get('/exams/pending-answer-keys', [N8nAutomationController::class, 'pendingAnswerKeys']);
    Route::post('/exams/{paper}/answer-key', [N8nAutomationController::class, 'applyExamAnswerKey']);
});

// ── AUTHENTICATED API ───────────────────────────────────────────────────────
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // Auth user
    Route::get('/me', function (Request $r) { return response()->json($r->user()->load('sellerProfile')); });
    Route::post('/logout', function (Request $r) { $r->user()->currentAccessToken()->delete(); return response()->json(['message' => 'Logged out']); });

    // Student: purchased exams
    Route::get('/my-exams', function (Request $r) {
        $purchases = \App\Models\Purchase::where('student_id', $r->user()->id)->where('payment_status', 'paid')->with(['examPaper.category', 'attempts'])->orderByDesc('created_at')->paginate(20);
        return response()->json($purchases);
    });

    // Student: results
    Route::get('/my-results', function (Request $r) {
        $attempts = \App\Models\ExamAttempt::where('student_id', $r->user()->id)->with('examPaper:id,title,max_marks')->orderByDesc('created_at')->paginate(20);
        return response()->json($attempts);
    });

    // Student: get exam questions for a purchased paper
    Route::get('/exam/{purchase}/questions', function (\App\Models\Purchase $purchase, Request $r) {
        if ($purchase->student_id !== $r->user()->id) return response()->json(['error' => 'Forbidden'], 403);
        if (!$purchase->canAttempt()) return response()->json(['error' => 'No retakes remaining'], 403);
        $questions = json_decode($purchase->examPaper->questions_data, true) ?? [];
        shuffle($questions);
        // strip correct answers before sending to app
        foreach ($questions as &$q) { unset($q['correct_answer'], $q['explanation']); }
        return response()->json(['questions' => $questions, 'duration_minutes' => $purchase->examPaper->duration_minutes, 'max_marks' => $purchase->examPaper->max_marks, 'negative_marking' => $purchase->examPaper->negative_marking]);
    });

    // Student: submit exam
    Route::post('/exam/{purchase}/submit', function (\App\Models\Purchase $purchase, Request $r) {
        if ($purchase->student_id !== $r->user()->id) return response()->json(['error' => 'Forbidden'], 403);
        // reuse same scoring logic as web controller
        $attempt = app(\App\Http\Controllers\Student\ExamController::class)->submit($r, $purchase);
        return response()->json(['message' => 'Submitted', 'attempt_id' => session('last_attempt_id')]);
    });

    // Create Razorpay order
    Route::post('/checkout/{examPaper}', function (\App\Models\ExamPaper $examPaper, Request $r) {
        $data = app(\App\Services\Payment\RazorpayService::class)->createOrder($examPaper, $r->user());
        return response()->json($data);
    });

    // Verify payment
    Route::post('/payment/verify', function (Request $r) {
        $ok = app(\App\Services\Payment\RazorpayService::class)->verifyAndCapture(
            $r->razorpay_order_id, $r->razorpay_payment_id, $r->razorpay_signature, $r->order_id
        );
        return response()->json(['success' => $ok]);
    });
});

// ── AUTH ────────────────────────────────────────────────────────────────────
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', function (Request $r) {
        $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (!\Illuminate\Support\Facades\Auth::attempt($r->only('email', 'password'))) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        $user  = \App\Models\User::where('email', $r->email)->first();
        $token = $user->createToken('nd-app')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user->load('sellerProfile')]);
    });

    Route::post('/register', function (Request $r) {
        $r->validate(['name' => 'required|string|max:100', 'email' => 'required|email|unique:users', 'phone' => 'nullable|string|max:15', 'password' => 'required|min:8']);
        $user  = \App\Models\User::create(['name' => $r->name, 'email' => $r->email, 'phone' => $r->phone, 'password' => bcrypt($r->password), 'role' => 'student']);
        $token = $user->createToken('nd-app')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user], 201);
    });
});
