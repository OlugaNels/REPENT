package com.repent.app.util

/**
 * Input rules used by every form in the app. Each function returns an error
 * message to show under the field, or null when the value is fine. Keeping this
 * in one plain-Kotlin object means wrong input is caught before any network
 * call, can never crash the UI, and is covered by unit tests.
 *
 * The password and phone rules match the server (api/config.php), so the app
 * and the API always agree.
 */
object Validators {

    const val PASSWORD_HINT = "At least 8 characters, with a letter and a number."

    private val EMAIL = Regex("^[A-Za-z0-9._%+\\-]+@[A-Za-z0-9.\\-]+\\.[A-Za-z]{2,}$")
    private val PHONE = Regex("^[0-9 +\\-()]{7,20}$")
    private val POSTAL = Regex("^[A-Za-z0-9 \\-]{3,10}$")

    fun fullName(value: String): String? {
        val v = value.trim()
        return when {
            v.length < 2 -> "Please enter your full name."
            v.length > 100 -> "Name is too long (100 characters at most)."
            else -> null
        }
    }

    fun email(value: String, required: Boolean = true): String? {
        val v = value.trim()
        return when {
            v.isEmpty() -> if (required) "Please enter your email address." else null
            v.length > 150 -> "Email address is too long."
            !EMAIL.matches(v) -> "Please enter a valid email address."
            else -> null
        }
    }

    /** Same rule as the server: 8-72 characters, at least one letter and one number. */
    fun password(value: String): String? = when {
        value.length < 8 -> "Password must be at least 8 characters."
        value.length > 72 -> "Password must be at most 72 characters."
        value.none { it.isLetter() } -> "Password must contain at least one letter."
        value.none { it.isDigit() } -> "Password must contain at least one number."
        else -> null
    }

    fun passwordsMatch(password: String, confirm: String): String? =
        if (password != confirm) "Passwords do not match." else null

    fun phone(value: String, required: Boolean = true): String? {
        val v = value.trim()
        return when {
            v.isEmpty() -> if (required) "A contact phone number is required." else null
            !PHONE.matches(v) -> "Please enter a valid phone number (digits, spaces, + - and brackets)."
            else -> null
        }
    }

    fun postalCode(value: String, required: Boolean = true): String? {
        val v = value.trim()
        return when {
            v.isEmpty() -> if (required) "Postal code is required." else null
            !POSTAL.matches(v) -> "Please enter a valid postal code."
            else -> null
        }
    }

    fun required(value: String, fieldName: String): String? =
        if (value.isBlank()) "$fieldName is required." else null

    fun prayer(value: String): String? {
        val v = value.trim()
        return when {
            v.isEmpty() -> "Please enter your prayer request."
            v.length > 2000 -> "Your prayer request is too long (2000 characters at most)."
            else -> null
        }
    }

    /** Returns the first non-null message, so a form can show one clear error at a time. */
    fun firstError(vararg errors: String?): String? = errors.firstOrNull { it != null }
}
