package com.repent.app.data.local

import android.content.Context
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import com.repent.app.data.model.User
import com.google.gson.Gson
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map

private val Context.dataStore by preferencesDataStore(name = "repent_session")

/**
 * Persists the bearer token + logged-in user across app restarts.
 * This is the mobile equivalent of the PHP $_SESSION used by the website.
 */
class SessionManager(private val context: Context) {

    private object Keys {
        val TOKEN = stringPreferencesKey("token")
        val USER_JSON = stringPreferencesKey("user_json")
    }

    private val gson = Gson()

    val tokenFlow: Flow<String?> = context.dataStore.data.map { it[Keys.TOKEN] }

    val userFlow: Flow<User?> = context.dataStore.data.map { prefs ->
        prefs[Keys.USER_JSON]?.let { runCatching { gson.fromJson(it, User::class.java) }.getOrNull() }
    }

    suspend fun saveSession(token: String, user: User) {
        context.dataStore.edit { prefs ->
            prefs[Keys.TOKEN] = token
            prefs[Keys.USER_JSON] = gson.toJson(user)
        }
    }

    suspend fun clearSession() {
        context.dataStore.edit { prefs ->
            prefs.remove(Keys.TOKEN)
            prefs.remove(Keys.USER_JSON)
        }
    }

    suspend fun currentToken(): String? = tokenFlow.first()
    suspend fun currentUser(): User? = userFlow.first()
}
