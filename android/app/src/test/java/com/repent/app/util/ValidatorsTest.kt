package com.repent.app.util

import org.junit.Assert.assertEquals
import org.junit.Assert.assertNotNull
import org.junit.Assert.assertNull
import org.junit.Test

class ValidatorsTest {

    @Test fun `full name needs at least two characters`() {
        assertNotNull(Validators.fullName(""))
        assertNotNull(Validators.fullName("   "))
        assertNotNull(Validators.fullName("A"))
        assertNull(Validators.fullName("Al"))
        assertNull(Validators.fullName("  Luthando Mndawe "))
    }

    @Test fun `full name has a maximum length`() {
        assertNotNull(Validators.fullName("x".repeat(101)))
        assertNull(Validators.fullName("x".repeat(100)))
    }

    @Test fun `valid emails are accepted`() {
        listOf("a@b.co", "user.name+tag@example.com", "  admin@repent.co.za  ", "x_y-z@sub.domain.org").forEach {
            assertNull("should accept $it", Validators.email(it))
        }
    }

    @Test fun `invalid emails are rejected`() {
        listOf("", "plainaddress", "@no-user.com", "user@", "user@domain", "user @domain.com", "user@@domain.com", "user@domain.c")
            .forEach { assertNotNull("should reject '$it'", Validators.email(it)) }
    }

    @Test fun `optional email may be blank but not wrong`() {
        assertNull(Validators.email("", required = false))
        assertNotNull(Validators.email("nope", required = false))
    }

    @Test fun `strong password is accepted`() {
        assertNull(Validators.password("Passw0rd"))
        assertNull(Validators.password("abcdefg1"))
        assertNull(Validators.password("Demo@1234"))
    }

    @Test fun `password rules match the server`() {
        assertEquals("Password must be at least 8 characters.", Validators.password("a1b2c3"))
        assertEquals("Password must contain at least one number.", Validators.password("onlyletters"))
        assertEquals("Password must contain at least one letter.", Validators.password("12345678"))
        assertNotNull(Validators.password("a1".repeat(40)))   // 80 chars: over the 72 limit
        assertNull(Validators.password("a1".repeat(36)))      // exactly 72
    }

    @Test fun `passwords must match`() {
        assertNull(Validators.passwordsMatch("Passw0rd", "Passw0rd"))
        assertEquals("Passwords do not match.", Validators.passwordsMatch("Passw0rd", "passw0rd"))
    }

    @Test fun `phone numbers`() {
        listOf("082 123 4567", "+27 82 123 4567", "(012) 555-0100", "0821234567").forEach { assertNull(it, Validators.phone(it)) }
        listOf("", "call me", "12345", "082-ABC-4567", "1".repeat(25)).forEach { assertNotNull("'$it'", Validators.phone(it)) }
        assertNull(Validators.phone("", required = false))
    }

    @Test fun `postal codes`() {
        listOf("0002", "8001", "SW1A 1AA", "123-45").forEach { assertNull(it, Validators.postalCode(it)) }
        listOf("", "12", "!!", "12345678901").forEach { assertNotNull("'$it'", Validators.postalCode(it)) }
    }

    @Test fun `required fields`() {
        assertEquals("Street address is required.", Validators.required("  ", "Street address"))
        assertNull(Validators.required("1 Church St", "Street address"))
    }

    @Test fun `prayer text limits`() {
        assertNotNull(Validators.prayer(""))
        assertNull(Validators.prayer("Please pray for my family."))
        assertNotNull(Validators.prayer("x".repeat(2001)))
    }

    @Test fun `first error returns the first problem only`() {
        assertEquals("b", Validators.firstError(null, "b", "c"))
        assertNull(Validators.firstError(null, null))
    }

    @Test fun `weird input never throws`() {
        val nasty = listOf("\u0000", "'; DROP TABLE users;--", "😀😀😀", "\n\t", "<script>alert(1)</script>", "x".repeat(10_000))
        nasty.forEach {
            Validators.fullName(it); Validators.email(it); Validators.password(it)
            Validators.phone(it); Validators.postalCode(it); Validators.prayer(it)
        }
    }
}
