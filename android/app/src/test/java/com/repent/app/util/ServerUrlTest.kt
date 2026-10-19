package com.repent.app.util

import org.junit.Assert.assertEquals
import org.junit.Assert.assertNull
import org.junit.Test

class ServerUrlTest {
    @Test fun `adds http and a trailing slash`() {
        assertEquals("http://192.168.0.10:8000/", ServerUrl.normalize("192.168.0.10:8000"))
        assertEquals("http://10.0.2.2:8000/", ServerUrl.normalize(" http://10.0.2.2:8000 "))
    }

    @Test fun `keeps https and a sub-folder`() {
        assertEquals("https://repent.example.com/", ServerUrl.normalize("https://repent.example.com"))
        assertEquals("http://192.168.1.5/Repent/", ServerUrl.normalize("192.168.1.5/Repent"))
        assertEquals("http://192.168.1.5/Repent/", ServerUrl.normalize("http://192.168.1.5/Repent/"))
    }

    @Test fun `scheme is lower-cased`() {
        assertEquals("http://localhost:8000/", ServerUrl.normalize("HTTP://localhost:8000"))
    }

    @Test fun `rejects unusable input`() {
        listOf("", "   ", "ftp://host", "http://", "http:// host", "http://exa mple.com", "http://host:99999", "http://host:0",
            "http://.bad", "http://bad..host", "not a url", "http://host/path?query=1")
            .forEach { assertNull("should reject '$it'", ServerUrl.normalize(it)) }
    }

    @Test fun `normalising twice gives the same answer`() {
        val once = ServerUrl.normalize("192.168.0.10:8000")!!
        assertEquals(once, ServerUrl.normalize(once))
    }
}
