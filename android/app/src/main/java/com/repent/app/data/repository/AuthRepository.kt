package com.repent.app.data.repository

import com.repent.app.data.local.SessionManager
import com.repent.app.data.model.AuthResponse
import com.repent.app.data.model.ChangePasswordRequest
import com.repent.app.data.model.GoogleLoginRequest
import com.repent.app.data.model.LoginRequest
import com.repent.app.data.model.ProfileRequest
import com.repent.app.data.model.RegisterRequest
import com.repent.app.data.model.User
import com.repent.app.data.remote.RetrofitClient

/** A login/register reply is only usable if it really carries a token and a user. */
private fun usable(r: AuthResponse): AuthResponse? =
    if (r.success && !r.token.isNullOrEmpty() && r.user != null) r else null

class AuthRepository(private val sessionManager: SessionManager) {

    private val api get() = RetrofitClient.apiService

    private suspend fun saveIfOk(result: ApiResult<AuthResponse>): ApiResult<Unit> = when (result) {
        is ApiResult.Success -> {
            sessionManager.saveSession(result.data.token!!, result.data.user!!)
            ApiResult.Success(Unit)
        }
        is ApiResult.Failure -> result
    }

    suspend fun register(fullname: String, email: String, password: String): ApiResult<Unit> =
        saveIfOk(apiCall("Registration failed. Please try again.", { api.register(RegisterRequest(fullname, email, password)) }, ::usable))

    suspend fun login(email: String, password: String): ApiResult<Unit> =
        saveIfOk(apiCall("Incorrect email or password.", { api.login(LoginRequest(email, password)) }, ::usable))

    /** [idToken] is the Google ID token from Credential Manager; the server verifies it with Google. */
    suspend fun googleLogin(idToken: String): ApiResult<Unit> =
        saveIfOk(apiCall("Google sign-in failed. Please try again.", { api.googleLogin(GoogleLoginRequest(idToken)) }, ::usable))

    suspend fun updateProfile(request: ProfileRequest): ApiResult<User> {
        val token = sessionManager.currentToken() ?: return ApiResult.Failure("Please sign in first.")
        val result = apiCall("Could not save your details.", { api.updateMe(RetrofitClient.bearer(token), request) }) {
            if (it.success) it.user else null
        }
        if (result is ApiResult.Success) sessionManager.saveSession(token, result.data)
        return result
    }

    /** Changing the password signs every other device out; the server hands back a fresh token for this one. */
    suspend fun changePassword(current: String, new: String): ApiResult<Unit> =
        saveIfOk(
            sessionManager.authed("Could not change your password.", { api.changePassword(it, ChangePasswordRequest(current, new)) }, ::usable),
        )

    /** Re-reads the profile from the server (used when Settings opens). Failures are ignored: the saved copy is still shown. */
    suspend fun refreshProfile() {
        val token = sessionManager.currentToken() ?: return
        val result = apiCall("", { api.getMe(RetrofitClient.bearer(token)) }) { if (it.success) it.user else null }
        if (result is ApiResult.Success) sessionManager.saveSession(token, result.data)
    }

    suspend fun logout() {
        val token = sessionManager.currentToken()
        if (token != null) {
            apiCall("", { api.logout(RetrofitClient.bearer(token)) }) { it }   // best effort; errors are ignored
        }
        sessionManager.clearSession()
    }
}
