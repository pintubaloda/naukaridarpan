package com.naukaridarpan.data.repository

import android.content.Context
import androidx.datastore.preferences.core.edit
import com.naukaridarpan.data.api.NaukaridarpanApi
import com.naukaridarpan.data.models.*
import com.naukaridarpan.di.TOKEN_KEY
import com.naukaridarpan.di.dataStore
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map
import retrofit2.Response
import javax.inject.Inject
import javax.inject.Singleton

sealed class Result<out T> {
    data class Success<T>(val data: T) : Result<T>()
    data class Error(val message: String, val code: Int = 0) : Result<Nothing>()
}

@Singleton
class NaukaridarpanRepository @Inject constructor(
    private val api: NaukaridarpanApi,
    @ApplicationContext private val context: Context,
) {
    // ── Token management ─────────────────────────────────────────────
    val tokenFlow: Flow<String?> = context.dataStore.data.map { it[TOKEN_KEY] }

    suspend fun saveToken(token: String) {
        context.dataStore.edit { it[TOKEN_KEY] = token }
    }

    suspend fun clearToken() {
        context.dataStore.edit { it.remove(TOKEN_KEY) }
    }

    // ── Auth ──────────────────────────────────────────────────────────
    suspend fun login(email: String, password: String): Result<AuthResponse> =
        safeCall { api.login(LoginRequest(email, password)) }

    suspend fun register(name: String, email: String, phone: String?, password: String): Result<AuthResponse> =
        safeCall { api.register(RegisterRequest(name, email, phone, password)) }

    suspend fun logout(): Result<MessageResponse> {
        val result = safeCall { api.logout() }
        if (result is Result.Success) clearToken()
        return result
    }

    suspend fun getMe(): Result<UserResponse> = safeCall { api.getMe() }

    // ── Exams ─────────────────────────────────────────────────────────
    suspend fun getExams(
        search: String? = null,
        category: String? = null,
        price: String? = null,
        difficulty: String? = null,
        sort: String? = "popular",
        page: Int = 1,
    ): Result<PagedResponse<ExamPaper>> =
        safeCall { api.getExams(search, category, price, difficulty, null, sort, page) }

    suspend fun getExam(slug: String): Result<ExamPaper> = safeCall { api.getExam(slug) }

    // ── Categories ────────────────────────────────────────────────────
    suspend fun getCategories(): Result<List<Category>> = safeCall { api.getCategories() }

    // ── Blog ──────────────────────────────────────────────────────────
    suspend fun getBlogPosts(category: String? = null, page: Int = 1): Result<PagedResponse<BlogPost>> =
        safeCall { api.getBlogPosts(category, page) }

    suspend fun getBlogPost(slug: String): Result<BlogPost> = safeCall { api.getBlogPost(slug) }

    // ── Student ───────────────────────────────────────────────────────
    suspend fun getMyExams(page: Int = 1): Result<PagedResponse<Purchase>> =
        safeCall { api.getMyExams(page) }

    suspend fun getMyResults(page: Int = 1): Result<PagedResponse<ExamAttempt>> =
        safeCall { api.getMyResults(page) }

    // ── Exam taking ───────────────────────────────────────────────────
    suspend fun getExamQuestions(purchaseId: Int): Result<ExamQuestionsResponse> =
        safeCall { api.getExamQuestions(purchaseId) }

    suspend fun submitExam(purchaseId: Int, answers: Map<String, Any>, tabSwitches: Int): Result<ExamSubmitResponse> =
        safeCall { api.submitExam(purchaseId, ExamSubmitRequest(answers, tabSwitches)) }

    // ── Payment ───────────────────────────────────────────────────────
    suspend fun createOrder(examId: Int): Result<RazorpayOrderResponse> =
        safeCall { api.createOrder(examId) }

    suspend fun verifyPayment(razorpayOrderId: String, paymentId: String, signature: String, orderId: String): Result<MessageResponse> =
        safeCall { api.verifyPayment(PaymentVerifyRequest(razorpayOrderId, paymentId, signature, orderId)) }

    // ── Safe call helper ──────────────────────────────────────────────
    private suspend fun <T> safeCall(call: suspend () -> Response<T>): Result<T> {
        return try {
            val response = call()
            if (response.isSuccessful && response.body() != null) {
                Result.Success(response.body()!!)
            } else {
                Result.Error(response.message() ?: "Unknown error", response.code())
            }
        } catch (e: Exception) {
            Result.Error(e.localizedMessage ?: "Network error")
        }
    }
}
