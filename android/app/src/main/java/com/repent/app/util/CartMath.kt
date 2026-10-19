package com.repent.app.util

import com.repent.app.data.model.CartItem
import com.repent.app.data.model.Product

/** Pure cart operations (no Android, no storage), used by CartStore and unit-tested. */
object CartMath {
    const val MAX_QTY = 20

    /** Adds one of [product]. Never goes above the stock on hand or [MAX_QTY]. */
    fun add(cart: List<CartItem>, product: Product): List<CartItem> {
        if (product.stock <= 0) return cart
        val limit = minOf(MAX_QTY, product.stock)
        val existing = cart.firstOrNull { it.productId == product.id }
        return if (existing == null) {
            cart + CartItem(product.id, product.name, product.price, product.image, 1)
        } else if (existing.quantity >= limit) {
            cart
        } else {
            cart.map { if (it.productId == product.id) it.copy(quantity = it.quantity + 1) else it }
        }
    }

    /** Sets a quantity; less than 1 removes the line, more than [MAX_QTY] is capped. */
    fun setQuantity(cart: List<CartItem>, productId: Int, quantity: Int): List<CartItem> =
        if (quantity < 1) remove(cart, productId)
        else cart.map { if (it.productId == productId) it.copy(quantity = minOf(quantity, MAX_QTY)) else it }

    fun remove(cart: List<CartItem>, productId: Int): List<CartItem> = cart.filterNot { it.productId == productId }

    fun subtotal(cart: List<CartItem>): Double = cart.sumOf { it.price * it.quantity }

    fun count(cart: List<CartItem>): Int = cart.sumOf { it.quantity }
}
