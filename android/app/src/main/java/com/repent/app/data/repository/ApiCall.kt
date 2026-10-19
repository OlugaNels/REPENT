package com.repent.app.data.repository

import com.repent.app.data.local.SessionManager
import com.repent.app.data.remote.ApiErrors
import com.repent.app.data.remote.RetrofitClient
import kotlinx.coroutines.CancellationException
import retrofit2.Response

sealed class ApiResult<out T> {
    data class Success<T>(val data: T) : ApiResult<T>()
    data class Failure(val message: String) : ApiResult<Nothing>()
}

/**
 * The ONE place every network request goes through, so no screen can crash on a bad reply:
 *  - an error status (400/401/409/422/500...) becomes a Failure carrying the server's own message
 *  - no connection, a timeout, a wrong server address or an unreadable reply become a friendly Failure
 *  - only coroutine cancellation (the user leaving the screen) is allowed to pass through
 */
internal suspend fun <T, R : Any> apiCall(
    fallback: String,
    call: suspend () -> Response<T>,
    map: (T) -> R?,
): ApiResult<R> = try {
    val response = call()
    if (response.isSuccessful) {
        val mapped = response.body()?.let(map)
        if (mapped != null) ApiResult.Success(mapped) else ApiResult.Failure(fallback)
    } else {
        ApiResult.Failure(ApiErrors.forStatus(response.code(), response.errorBody()?.string(), fallback))
    }
} catch (e: CancellationException) {
    throw e
} catch (e: Exception) {
    ApiResult.Failure(ApiErrors.forException(e))
}

/** Same as [apiCall] but adds the saved sign-in token; fails politely when nobody is signed in. */
internal suspend fun <T, R : Any> SessionManager.authed(
    fallback: String,
    call: suspend (bearer: String) -> Response<T>,
    map: (T) -> R?,
): ApiResult<R> {
    val token = currentToken() ?: return ApiResult.Failure("Please sign in first.")
    return apiCall(fallback, { call(RetrofitClient.bearer(token)) }, map)
}
