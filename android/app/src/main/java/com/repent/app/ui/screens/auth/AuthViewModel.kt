package com.repent.app.ui.screens.auth

import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.repent.app.data.repository.ApiResult
import com.repent.app.data.repository.AuthRepository
import com.repent.app.util.Validators
import kotlinx.coroutines.launch

/**
 * Login / register / Google sign-in logic. Every input is checked with [Validators] BEFORE
 * any network call, and the repository never throws, so wrong input or a dead server can't crash the screen.
 */
class AuthViewModel(private val repository: AuthRepository) : ViewModel() {

    var isLoading by mutableStateOf(false)
        private set
    var errorMessage by mutableStateOf<String?>(null)
        private set
    var nameError by mutableStateOf<String?>(null)
        private set
    var emailError by mutableStateOf<String?>(null)
        private set
    var passwordError by mutableStateOf<String?>(null)
        private set
    var confirmError by mutableStateOf<String?>(null)
        private set

    private fun clearErrors() {
        errorMessage = null; nameError = null; emailError = null; passwordError = null; confirmError = null
    }

    fun login(email: String, password: String, onSuccess: () -> Unit) {
        if (isLoading) return
        clearErrors()
        emailError = Validators.email(email)
        passwordError = if (password.isEmpty()) "Please enter your password." else null
        if (emailError != null || passwordError != null) return
        run(onSuccess) { repository.login(email.trim().lowercase(), password) }
    }

    fun register(fullname: String, email: String, password: String, confirm: String, onSuccess: () -> Unit) {
        if (isLoading) return
        clearErrors()
        nameError = Validators.fullName(fullname)
        emailError = Validators.email(email)
        passwordError = Validators.password(password)
        confirmError = if (passwordError == null) Validators.passwordsMatch(password, confirm) else null
        if (listOf(nameError, emailError, passwordError, confirmError).any { it != null }) return
        run(onSuccess) { repository.register(fullname.trim(), email.trim().lowercase(), password) }
    }

    /** [getToken] opens the Google account picker (needs an Activity), then we hand the token to our server. */
    fun googleSignIn(getToken: suspend () -> GoogleResult, onSuccess: () -> Unit) {
        if (isLoading) return
        clearErrors()
        viewModelScope.launch {
            isLoading = true
            when (val google = getToken()) {
                is GoogleResult.Token -> when (val result = repository.googleLogin(google.idToken)) {
                    is ApiResult.Success -> { isLoading = false; onSuccess(); return@launch }
                    is ApiResult.Failure -> errorMessage = result.message
                }
                is GoogleResult.Failed -> errorMessage = google.message
                GoogleResult.Cancelled -> Unit
            }
            isLoading = false
        }
    }

    private fun run(onSuccess: () -> Unit, block: suspend () -> ApiResult<Unit>) {
        viewModelScope.launch {
            isLoading = true
            when (val result = block()) {
                is ApiResult.Success -> { isLoading = false; onSuccess(); return@launch }
                is ApiResult.Failure -> errorMessage = result.message
            }
            isLoading = false
        }
    }
}
