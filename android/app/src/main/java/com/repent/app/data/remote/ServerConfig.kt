package com.repent.app.data.remote

import android.content.Context
import android.content.SharedPreferences
import com.repent.app.BuildConfig
import com.repent.app.util.ServerUrl

/**
 * Where the PHP API lives. The default comes from the build (BASE_URL in local.properties),
 * but it can be changed at runtime on the Settings screen, so when the laptop's IP address
 * changes (new Wi-Fi, new network) you do NOT need to rebuild the app.
 */
object ServerConfig {
    private const val PREFS = "repent_settings"
    private const val KEY_URL = "server_url"

    @Volatile var baseUrl: String = BuildConfig.BASE_URL
        private set

    /** Called when the server says our saved sign-in is no longer valid (HTTP 401 on a signed-in request). */
    @Volatile var onUnauthorized: (() -> Unit)? = null

    private var prefs: SharedPreferences? = null

    fun init(context: Context) {
        val p = context.getSharedPreferences(PREFS, Context.MODE_PRIVATE)
        prefs = p
        p.getString(KEY_URL, null)?.let { saved -> ServerUrl.normalize(saved)?.let { baseUrl = it } }
    }

    /** Saves a new server address. Returns an error message, or null when it was accepted. */
    fun update(input: String): String? {
        val normalised = ServerUrl.normalize(input)
            ?: return "That doesn't look like a valid address. Example: 192.168.0.10:8000"
        baseUrl = normalised
        prefs?.edit()?.putString(KEY_URL, normalised)?.apply()
        return null
    }

    fun resetToDefault() {
        baseUrl = BuildConfig.BASE_URL
        prefs?.edit()?.remove(KEY_URL)?.apply()
    }
}
