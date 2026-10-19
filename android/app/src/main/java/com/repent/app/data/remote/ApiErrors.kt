package com.repent.app.data.remote

import com.google.gson.JsonObject
import com.google.gson.JsonParseException
import com.google.gson.JsonParser
import java.net.ConnectException
import java.net.SocketTimeoutException
import java.net.UnknownHostException
import javax.net.ssl.SSLException

/**
 * Turns anything that can go wrong with a request into one short, friendly sentence.
 * The PHP API always answers errors as {"success":false,"error":"..."}, so that message
 * is shown as-is. If the server is down, unreachable, or sends HTML instead of JSON
 * (for example a wrong address in Settings), the user gets a clear hint instead of a crash.
 */
object ApiErrors {

    /** Pulls "error" out of a JSON error body, or null if the body is not our JSON. */
    fun fromBody(body: String?): String? {
        if (body.isNullOrBlank()) return null
        return try {
            val el = JsonParser.parseString(body)
            val msg = (el as? JsonObject)?.get("error")
            if (msg != null && msg.isJsonPrimitive) msg.asString.takeIf { it.isNotBlank() } else null
        } catch (e: JsonParseException) {
            null
        } catch (e: IllegalStateException) {
            null
        }
    }

    fun forStatus(code: Int, body: String?, fallback: String): String {
        fromBody(body)?.let { return it }
        return when (code) {
            401 -> "Please sign in again."
            403 -> "You don't have permission to do that."
            404 -> "The server could not find that. Check the server address in Settings."
            408, 504 -> "The server took too long to answer. Please try again."
            in 500..599 -> "The server had a problem. Please try again in a moment."
            else -> fallback
        }
    }

    fun forException(e: Throwable): String = when (e) {
        is UnknownHostException, is ConnectException ->
            "Can't reach the server. Check your Wi-Fi and the server address in Settings."
        is SocketTimeoutException -> "The server took too long to answer. Please try again."
        is SSLException -> "A secure connection could not be made. Check the server address in Settings."
        is JsonParseException, is IllegalStateException ->
            "The server sent a reply the app didn't understand. Check the server address in Settings."
        else -> "Something went wrong. Please try again."
    }
}
