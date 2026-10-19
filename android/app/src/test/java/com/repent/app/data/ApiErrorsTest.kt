package com.repent.app.data

import com.repent.app.data.remote.ApiErrors
import com.google.gson.JsonSyntaxException
import org.junit.Assert.assertEquals
import org.junit.Assert.assertNull
import org.junit.Assert.assertTrue
import org.junit.Test
import java.net.ConnectException
import java.net.SocketTimeoutException
import java.net.UnknownHostException

class ApiErrorsTest {
    @Test fun `reads the server's error message`() {
        assertEquals("Incorrect email or password.", ApiErrors.fromBody("""{"success":false,"error":"Incorrect email or password."}"""))
    }

    @Test fun `ignores bodies that are not our JSON`() {
        assertNull(ApiErrors.fromBody(null))
        assertNull(ApiErrors.fromBody(""))
        assertNull(ApiErrors.fromBody("<html><body>404 Not Found</body></html>"))
        assertNull(ApiErrors.fromBody("""{"success":true}"""))
        assertNull(ApiErrors.fromBody("""{"error":""}"""))
        assertNull(ApiErrors.fromBody("""{"error":{"nested":true}}"""))
        assertNull(ApiErrors.fromBody("[1,2,3]"))
    }

    @Test fun `server message wins over the status text`() {
        assertEquals("Not enough stock for Cap — Coral (only 3 left).",
            ApiErrors.forStatus(409, """{"success":false,"error":"Not enough stock for Cap — Coral (only 3 left)."}""", "fallback"))
    }

    @Test fun `status codes without a JSON body still give a useful message`() {
        assertTrue(ApiErrors.forStatus(401, "", "x").contains("sign in"))
        assertTrue(ApiErrors.forStatus(403, null, "x").contains("permission"))
        assertTrue(ApiErrors.forStatus(404, "<html>", "x").contains("Settings"))
        assertTrue(ApiErrors.forStatus(503, "<html>", "x").contains("server"))
        assertEquals("custom fallback", ApiErrors.forStatus(418, null, "custom fallback"))
    }

    @Test fun `network problems become friendly text`() {
        assertTrue(ApiErrors.forException(UnknownHostException("x")).contains("Can't reach the server"))
        assertTrue(ApiErrors.forException(ConnectException("refused")).contains("Can't reach the server"))
        assertTrue(ApiErrors.forException(SocketTimeoutException()).contains("too long"))
        assertTrue(ApiErrors.forException(JsonSyntaxException("bad")).contains("didn't understand"))
        assertEquals("Something went wrong. Please try again.", ApiErrors.forException(RuntimeException("boom")))
    }
}
