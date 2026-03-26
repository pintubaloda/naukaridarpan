package com.naukaridarpan.data.api

import com.naukaridarpan.data.models.*
import retrofit2.Response
import retrofit2.http.*

interface NaukaridarpanApi {

    // ── AUTH ──────────────────────────────────────────────────────────
    @POST("auth/login")
    suspend fun login(@Body body: LoginRequest): Response<AuthResponse>

    @POST("auth/register")
    suspend fun register(@Body body: RegisterRequest): Response<AuthResponse>

    @POST("logout")
    suspend fun logout(): Response<MessageResponse>

    @GET("me")
    suspend fun getMe(): Response<UserResponse>

    // ── EXAMS ─────────────────────────────────────────────────────────
    @GET("exams")
    suspend fun getExams(
        @Query("search") search: String? = null,
        @Query("category") category: String? = null,
        @Query("price") price: String? = null,
        @Query("difficulty") difficulty: String? = null,
        @Query("language") language: String? = null,
        @Query("sort") sort: String? = "popular",
        @Query("page") page: Int = 1,
    ): Response<PagedResponse<ExamPaper>>

    @GET("exams/{slug}")
    suspend fun getExam(@Path("slug") slug: String): Response<ExamPaper>

    // ── CATEGORIES ───────────────────────────────────────────────────
    @GET("categories")
    suspend fun getCategories(): Response<List<Category>>

    // ── BLOG ──────────────────────────────────────────────────────────
    @GET("blog")
    suspend fun getBlogPosts(
        @Query("category") category: String? = null,
        @Query("page") page: Int = 1,
    ): Response<PagedResponse<BlogPost>>

    @GET("blog/{slug}")
    suspend fun getBlogPost(@Path("slug") slug: String): Response<BlogPost>

    // ── PROFESSOR ────────────────────────────────────────────────────
    @GET("professors/{username}")
    suspend fun getProfessor(@Path("username") username: String): Response<ProfessorResponse>

    // ── STUDENT ───────────────────────────────────────────────────────
    @GET("my-exams")
    suspend fun getMyExams(@Query("page") page: Int = 1): Response<PagedResponse<Purchase>>

    @GET("my-results")
    suspend fun getMyResults(@Query("page") page: Int = 1): Response<PagedResponse<ExamAttempt>>

    // ── EXAM TAKING ───────────────────────────────────────────────────
    @GET("exam/{purchaseId}/questions")
    suspend fun getExamQuestions(@Path("purchaseId") purchaseId: Int): Response<ExamQuestionsResponse>

    @POST("exam/{purchaseId}/submit")
    suspend fun submitExam(
        @Path("purchaseId") purchaseId: Int,
        @Body body: ExamSubmitRequest,
    ): Response<ExamSubmitResponse>

    // ── PAYMENT ───────────────────────────────────────────────────────
    @POST("checkout/{examId}")
    suspend fun createOrder(@Path("examId") examId: Int): Response<RazorpayOrderResponse>

    @POST("payment/verify")
    suspend fun verifyPayment(@Body body: PaymentVerifyRequest): Response<MessageResponse>
}
