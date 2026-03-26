<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Http\Controllers\Seller\SellerController;
use App\Http\Controllers\Seller\PaperController;
use App\Http\Controllers\Seller\PayoutController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ExamApprovalController;
use App\Http\Controllers\Admin\KYCController as AdminKYCController;
use App\Http\Controllers\Admin\BlogAdminController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\MarketplaceController;

// ── PUBLIC ──────────────────────────────────────────────────────────────────
Route::get('/',                          [MarketplaceController::class, 'home'])->name('home');
Route::get('/exams',                     [MarketplaceController::class, 'browse'])->name('exams.browse');
Route::get('/exams/{slug}',              [MarketplaceController::class, 'show'])->name('exams.show');
Route::get('/professor/{username}',      [MarketplaceController::class, 'professorProfile'])->name('professor.profile');
Route::get('/category/{slug}',           [MarketplaceController::class, 'category'])->name('category');
Route::get('/blog',                      [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}',               [BlogController::class, 'show'])->name('blog.show');
Route::get('/about',                     [MarketplaceController::class, 'about'])->name('about');
Route::get('/contact',                   [MarketplaceController::class, 'contact'])->name('contact');
Route::post('/contact',                  [MarketplaceController::class, 'contactSubmit'])->name('contact.submit');

// ── AUTH ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',                 [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',                [AuthController::class, 'login']);
    Route::get('/register',              [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',             [AuthController::class, 'register']);
    Route::get('/register/seller',       [AuthController::class, 'showSellerRegister'])->name('register.seller');
    Route::post('/register/seller',      [AuthController::class, 'registerSeller']);
    Route::get('/forgot-password',       [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password',      [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}',[AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password',       [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── STUDENT ──────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard',                         [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/my-exams',                          [StudentController::class, 'myExams'])->name('exams');
    Route::get('/results',                           [StudentController::class, 'results'])->name('results');
    Route::get('/profile',                           [StudentController::class, 'profile'])->name('profile');
    Route::put('/profile',                           [StudentController::class, 'updateProfile'])->name('profile.update');
    Route::get('/wishlist',                          [StudentController::class, 'wishlist'])->name('wishlist');
    Route::post('/checkout/{examPaper}',             [StudentController::class, 'checkout'])->name('checkout');
    Route::get('/payment/success',                   [StudentController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment/failed',                    [StudentController::class, 'paymentFailed'])->name('payment.failed');
    Route::get('/exam/{purchase}/start',             [StudentExamController::class, 'start'])->name('exam.start');
    Route::get('/exam/{purchase}/take',              [StudentExamController::class, 'take'])->name('exam.take');
    Route::post('/exam/{purchase}/submit',           [StudentExamController::class, 'submit'])->name('exam.submit');
    Route::get('/exam-attempt/{attempt}/result',     [StudentExamController::class, 'result'])->name('exam.result');
});

// ── SELLER ───────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard',                         [SellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile',                           [SellerController::class, 'profile'])->name('profile');
    Route::put('/profile',                           [SellerController::class, 'updateProfile'])->name('profile.update');
    Route::get('/analytics',                         [SellerController::class, 'analytics'])->name('analytics');
    Route::get('/papers',                            [PaperController::class, 'index'])->name('papers');
    Route::get('/papers/create',                     [PaperController::class, 'create'])->name('papers.create');
    Route::post('/papers',                           [PaperController::class, 'store'])->name('papers.store');
    Route::get('/papers/{paper}/edit',               [PaperController::class, 'edit'])->name('papers.edit');
    Route::put('/papers/{paper}',                    [PaperController::class, 'update'])->name('papers.update');
    Route::delete('/papers/{paper}',                 [PaperController::class, 'destroy'])->name('papers.destroy');
    Route::post('/papers/{paper}/submit-review',     [PaperController::class, 'submitForReview'])->name('papers.submit');
    Route::get('/papers/{paper}/parse-status',       [PaperController::class, 'parseStatus'])->name('papers.parse-status');
    Route::get('/kyc',                               [PayoutController::class, 'kyc'])->name('kyc');
    Route::post('/kyc',                              [PayoutController::class, 'submitKyc'])->name('kyc.submit');
    Route::get('/payouts',                           [PayoutController::class, 'index'])->name('payouts');
    Route::get('/earnings',                          [PayoutController::class, 'earnings'])->name('earnings');
    Route::post('/payouts/request',                  [PayoutController::class, 'requestPayout'])->name('payouts.request');
});

// ── ADMIN ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',                         [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users',                             [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/toggle',              [AdminController::class, 'toggleUser'])->name('users.toggle');
    Route::get('/settings',                          [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings',                         [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/exams/pending',                     [ExamApprovalController::class, 'pending'])->name('exams.pending');
    Route::post('/exams/{paper}/approve',            [ExamApprovalController::class, 'approve'])->name('exams.approve');
    Route::post('/exams/{paper}/reject',             [ExamApprovalController::class, 'reject'])->name('exams.reject');
    Route::get('/kyc/pending',                       [AdminKYCController::class, 'pending'])->name('kyc.pending');
    Route::post('/kyc/{kyc}/approve',                [AdminKYCController::class, 'approve'])->name('kyc.approve');
    Route::post('/kyc/{kyc}/reject',                 [AdminKYCController::class, 'reject'])->name('kyc.reject');
    Route::get('/payouts',                           [AdminController::class, 'pendingPayouts'])->name('payouts');
    Route::post('/payouts/{payout}/process',         [AdminController::class, 'processPayout'])->name('payouts.process');
    Route::get('/blog',                              [BlogAdminController::class, 'index'])->name('blog.index');
    Route::get('/blog/create',                       [BlogAdminController::class, 'create'])->name('blog.create');
    Route::post('/blog',                             [BlogAdminController::class, 'store'])->name('blog.store');
    Route::get('/blog/{post}/edit',                  [BlogAdminController::class, 'edit'])->name('blog.edit');
    Route::put('/blog/{post}',                       [BlogAdminController::class, 'update'])->name('blog.update');
    Route::delete('/blog/{post}',                    [BlogAdminController::class, 'destroy'])->name('blog.destroy');
    Route::post('/blog/generate-ai',                 [BlogAdminController::class, 'generateAI'])->name('blog.generate');
    Route::get('/scraped',                           [AdminController::class, 'scrapedPapers'])->name('scraped');
    Route::post('/scraped/{paper}/publish',          [AdminController::class, 'publishScraped'])->name('scraped.publish');
    Route::get('/professor-leads',                   [AdminController::class, 'professorLeads'])->name('professor-leads');
    Route::post('/professor-leads/send-mailer',      [AdminController::class, 'sendOnboardingMailer'])->name('professor-leads.mail');
});

// ── WEBHOOKS ─────────────────────────────────────────────────────────────────
Route::post('/webhooks/razorpay', [StudentController::class, 'razorpayWebhook'])->name('webhooks.razorpay');
