package com.naukaridarpan.data.models

import com.google.gson.annotations.SerializedName

// ── AUTH ──────────────────────────────────────────────────────────────────

data class LoginRequest(val email: String, val password: String)

data class RegisterRequest(val name: String, val email: String, val phone: String?, val password: String)

data class AuthResponse(val token: String, val user: User)

data class MessageResponse(val message: String, val success: Boolean = true)

// ── USER ──────────────────────────────────────────────────────────────────

data class User(
    val id: Int,
    val name: String,
    val email: String,
    val phone: String?,
    val role: String,
    val avatar: String?,
    @SerializedName("is_active") val isActive: Boolean = true,
    @SerializedName("seller_profile") val sellerProfile: SellerProfile?,
)

data class UserResponse(val id: Int, val name: String, val email: String, val role: String)

data class SellerProfile(
    val username: String,
    val bio: String?,
    val qualification: String?,
    val institution: String?,
    val rating: Float = 0f,
    @SerializedName("total_reviews") val totalReviews: Int = 0,
    @SerializedName("total_sales") val totalSales: Int = 0,
    @SerializedName("is_verified") val isVerified: Boolean = false,
    @SerializedName("wallet_balance") val walletBalance: Double = 0.0,
)

// ── CATEGORY ─────────────────────────────────────────────────────────────

data class Category(
    val id: Int,
    val name: String,
    val slug: String,
    val icon: String?,
    @SerializedName("exam_papers_count") val examPapersCount: Int = 0,
)

// ── EXAM PAPER ────────────────────────────────────────────────────────────

data class ExamPaper(
    val id: Int,
    val title: String,
    val slug: String,
    val description: String?,
    val language: String,
    val difficulty: String,
    @SerializedName("total_questions") val totalQuestions: Int,
    @SerializedName("duration_minutes") val durationMinutes: Int,
    @SerializedName("max_marks") val maxMarks: Int,
    @SerializedName("negative_marking") val negativeMarking: Double = 0.0,
    @SerializedName("max_retakes") val maxRetakes: Int,
    @SerializedName("student_price") val studentPrice: Double,
    @SerializedName("is_free") val isFree: Boolean,
    @SerializedName("total_purchases") val totalPurchases: Int = 0,
    val tags: List<String>?,
    val thumbnail: String?,
    val status: String,
    val category: Category?,
    val seller: User?,
)

// ── PURCHASE ──────────────────────────────────────────────────────────────

data class Purchase(
    val id: Int,
    @SerializedName("order_id") val orderId: String,
    @SerializedName("amount_paid") val amountPaid: Double,
    @SerializedName("payment_status") val paymentStatus: String,
    @SerializedName("retakes_used") val retakesUsed: Int,
    @SerializedName("retakes_allowed") val retakesAllowed: Int,
    @SerializedName("exam_paper") val examPaper: ExamPaper?,
    @SerializedName("created_at") val createdAt: String,
) {
    val canAttempt get() = paymentStatus == "paid" && retakesUsed < retakesAllowed
}

// ── EXAM QUESTIONS ────────────────────────────────────────────────────────

data class ExamQuestionsResponse(
    val questions: List<Question>,
    @SerializedName("duration_minutes") val durationMinutes: Int,
    @SerializedName("max_marks") val maxMarks: Int,
    @SerializedName("negative_marking") val negativeMarking: Double,
)

data class Question(
    val serial: Int,
    val type: String,
    val text: String,
    val marks: Int = 1,
    val options: List<Option>?,
    @SerializedName("image_description") val imageDescription: String?,
)

data class Option(val label: String, val text: String)

data class ExamSubmitRequest(
    val answers: Map<String, Any>,
    @SerializedName("tab_switches") val tabSwitches: Int = 0,
)

data class ExamSubmitResponse(val message: String, @SerializedName("attempt_id") val attemptId: Int?)

// ── EXAM ATTEMPT ──────────────────────────────────────────────────────────

data class ExamAttempt(
    val id: Int,
    val status: String,
    val score: Double?,
    val percentage: Double?,
    @SerializedName("correct_answers") val correctAnswers: Int,
    @SerializedName("wrong_answers") val wrongAnswers: Int,
    val unattempted: Int,
    @SerializedName("time_taken_seconds") val timeTakenSeconds: Int?,
    @SerializedName("submitted_at") val submittedAt: String?,
    @SerializedName("exam_paper") val examPaper: ExamPaper?,
)

// ── BLOG ──────────────────────────────────────────────────────────────────

data class BlogPost(
    val id: Int,
    val title: String,
    val slug: String,
    val excerpt: String?,
    val content: String?,
    val category: String?,
    val tags: List<String>?,
    @SerializedName("featured_image") val featuredImage: String?,
    @SerializedName("view_count") val viewCount: Int,
    @SerializedName("published_at") val publishedAt: String?,
    @SerializedName("is_ai_generated") val isAiGenerated: Boolean = false,
)

// ── PROFESSOR ────────────────────────────────────────────────────────────

data class ProfessorResponse(val profile: SellerProfile, val exams: PagedResponse<ExamPaper>)

// ── PAYMENT ───────────────────────────────────────────────────────────────

data class RazorpayOrderResponse(
    @SerializedName("razorpay_order_id") val razorpayOrderId: String?,
    val amount: Int,
    val currency: String,
    @SerializedName("order_id") val orderId: String,
    @SerializedName("key_id") val keyId: String,
    @SerializedName("exam_title") val examTitle: String,
    @SerializedName("prefill_name") val prefillName: String,
    @SerializedName("prefill_email") val prefillEmail: String,
    @SerializedName("prefill_contact") val prefillContact: String,
)

data class PaymentVerifyRequest(
    @SerializedName("razorpay_order_id") val razorpayOrderId: String,
    @SerializedName("razorpay_payment_id") val razorpayPaymentId: String,
    @SerializedName("razorpay_signature") val razorpaySignature: String,
    @SerializedName("order_id") val orderId: String,
)

// ── PAGINATION ────────────────────────────────────────────────────────────

data class PagedResponse<T>(
    val data: List<T>,
    val total: Int,
    @SerializedName("current_page") val currentPage: Int,
    @SerializedName("last_page") val lastPage: Int,
    @SerializedName("per_page") val perPage: Int,
)
