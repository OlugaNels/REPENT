package com.repent.app.util

/**
 * Shipping and totals. The server (api/checkout.php) recalculates all of this
 * from the database, so these numbers are only the preview shown in the cart.
 * Rule: free shipping from R800, otherwise a flat R95.
 */
object Pricing {
    const val FREE_SHIPPING_FROM = 800.0
    const val FLAT_SHIPPING = 95.0

    fun shipping(subtotal: Double): Double = when {
        subtotal <= 0.0 -> 0.0
        subtotal >= FREE_SHIPPING_FROM -> 0.0
        else -> FLAT_SHIPPING
    }

    fun total(subtotal: Double): Double = subtotal + shipping(subtotal)

    /** "R350" for whole amounts, "R349.50" otherwise. */
    fun rand(amount: Double): String {
        val cents = Math.round(amount * 100)
        return if (cents % 100 == 0L) "R${cents / 100}" else "R${cents / 100}.${(cents % 100).toString().padStart(2, '0')}"
    }
}
