package com.repent.app.ui.screens.settings

import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.repent.app.data.model.ProfileRequest
import com.repent.app.data.remote.ServerConfig
import com.repent.app.data.repository.ApiResult
import com.repent.app.data.repository.AuthRepository
import com.repent.app.data.repository.ServerRepository
import com.repent.app.util.Validators
import kotlinx.coroutines.launch

data class UiMessage(val text: String, val isError: Boolean)

/**
 * Logic behind the Settings screen: edit personal details, change password, set the server address.
 * Every field is validated first; the repositories never throw, so nothing here can crash the screen.
 */
class SettingsViewModel(
    private val auth: AuthRepository,
    private val server: ServerRepository,
) : ViewModel() {

    // ----- personal details -----
    var savingProfile by mutableStateOf(false); private set
    var profileMessage by mutableStateOf<UiMessage?>(null); private set
    var nameError by mutableStateOf<String?>(null); private set
    var phoneError by mutableStateOf<String?>(null); private set
    var postalError by mutableStateOf<String?>(null); private set

    // ----- password -----
    var changingPassword by mutableStateOf(false); private set
    var passwordMessage by mutableStateOf<UiMessage?>(null); private set
    var currentError by mutableStateOf<String?>(null); private set
    var newError by mutableStateOf<String?>(null); private set
    var confirmError by mutableStateOf<String?>(null); private set

    // ----- server address -----
    var testing by mutableStateOf(false); private set
    var serverMessage by mutableStateOf<UiMessage?>(null); private set

    init {
        viewModelScope.launch { auth.refreshProfile() }
    }

    fun saveProfile(name: String, phone: String, address: String, city: String, postal: String, category: String) {
        if (savingProfile) return
        profileMessage = null
        nameError = Validators.fullName(name)
        phoneError = Validators.phone(phone, required = false)
        postalError = Validators.postalCode(postal, required = false)
        if (nameError != null || phoneError != null || postalError != null) return
        viewModelScope.launch {
            savingProfile = true
            val request = ProfileRequest(name.trim(), phone.trim(), address.trim(), city.trim(), postal.trim(), category)
            profileMessage = when (val r = auth.updateProfile(request)) {
                is ApiResult.Success -> UiMessage("Your details were saved.", isError = false)
                is ApiResult.Failure -> UiMessage(r.message, isError = true)
            }
            savingProfile = false
        }
    }

    fun changePassword(current: String, new: String, confirm: String, onDone: () -> Unit) {
        if (changingPassword) return
        passwordMessage = null
        currentError = if (current.isEmpty()) "Please enter your current password." else null
        newError = Validators.password(new) ?: if (new == current && current.isNotEmpty()) "The new password must be different from the current one." else null
        confirmError = if (newError == null) Validators.passwordsMatch(new, confirm) else null
        if (currentError != null || newError != null || confirmError != null) return
        viewModelScope.launch {
            changingPassword = true
            passwordMessage = when (val r = auth.changePassword(current, new)) {
                is ApiResult.Success -> { onDone(); UiMessage("Password changed. Other devices have been signed out.", isError = false) }
                is ApiResult.Failure -> UiMessage(r.message, isError = true)
            }
            changingPassword = false
        }
    }

    fun saveServer(input: String): String {
        val error = ServerConfig.update(input)
        serverMessage = if (error == null) UiMessage("Server address saved: ${ServerConfig.baseUrl}", isError = false) else UiMessage(error, isError = true)
        return ServerConfig.baseUrl
    }

    fun resetServer(): String {
        ServerConfig.resetToDefault()
        serverMessage = UiMessage("Back to the default address: ${ServerConfig.baseUrl}", isError = false)
        return ServerConfig.baseUrl
    }

    fun testConnection() {
        if (testing) return
        serverMessage = null
        viewModelScope.launch {
            testing = true
            serverMessage = when (val r = server.health()) {
                is ApiResult.Success -> UiMessage("Connected. The API and database are working (${r.data.products ?: 0} products found).", isError = false)
                is ApiResult.Failure -> UiMessage(r.message, isError = true)
            }
            testing = false
        }
    }
}
