package com.repent.app.util

/**
 * Cleans up the server address typed on the Settings screen so a typo can never
 * crash the app. "192.168.0.10:8000" becomes "http://192.168.0.10:8000/".
 * A sub-folder is kept: "http://10.0.0.5/Repent" becomes "http://10.0.0.5/Repent/".
 */
object ServerUrl {
    private val PATTERN = Regex("^(https?)://([A-Za-z0-9.\\-]+)(?::(\\d{1,5}))?(/[^\\s?#]*)?$", RegexOption.IGNORE_CASE)

    /** Returns the normalised base URL (always ends with "/"), or null if the input is not a usable address. */
    fun normalize(input: String): String? {
        var v = input.trim()
        if (v.isEmpty()) return null
        if (!v.contains("://")) v = "http://$v"
        val m = PATTERN.matchEntire(v) ?: return null
        val port = m.groupValues[3]
        if (port.isNotEmpty() && port.toInt() !in 1..65535) return null
        val host = m.groupValues[2]
        if (host.startsWith(".") || host.endsWith(".") || host.contains("..")) return null
        var path = m.groupValues[4]
        if (!path.endsWith("/")) path += "/"
        return "${m.groupValues[1].lowercase()}://$host${if (port.isNotEmpty()) ":$port" else ""}$path"
    }
}
