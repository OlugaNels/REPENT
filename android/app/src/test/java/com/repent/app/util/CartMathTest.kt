package com.repent.app.util

import com.repent.app.data.model.Product
import org.junit.Assert.assertEquals
import org.junit.Assert.assertSame
import org.junit.Assert.assertTrue
import org.junit.Test

class CartMathTest {
    private fun product(id: Int, price: Double = 280.0, stock: Int = 10) =
        Product(id, "Product $id", "desc", price, "caps", "cap-$id.jpg", stock, null)

    @Test fun `adding a new product creates a line of 1`() {
        val cart = CartMath.add(emptyList(), product(8))
        assertEquals(1, cart.size)
        assertEquals(1, cart[0].quantity)
        assertEquals(8, cart[0].productId)
    }

    @Test fun `adding the same product again increases the quantity`() {
        val p = product(8)
        val cart = CartMath.add(CartMath.add(emptyList(), p), p)
        assertEquals(1, cart.size)
        assertEquals(2, cart[0].quantity)
    }

    @Test fun `cannot add more than the stock`() {
        val p = product(8, stock = 2)
        var cart = emptyList<com.repent.app.data.model.CartItem>()
        repeat(5) { cart = CartMath.add(cart, p) }
        assertEquals(2, cart[0].quantity)
    }

    @Test fun `cannot add a sold-out product`() {
        val cart = CartMath.add(emptyList(), product(8, stock = 0))
        assertTrue(cart.isEmpty())
    }

    @Test fun `cannot exceed the maximum quantity`() {
        val p = product(8, stock = 100)
        var cart = emptyList<com.repent.app.data.model.CartItem>()
        repeat(30) { cart = CartMath.add(cart, p) }
        assertEquals(CartMath.MAX_QTY, cart[0].quantity)
    }

    @Test fun `setting a quantity below 1 removes the line`() {
        val cart = CartMath.add(emptyList(), product(8))
        assertTrue(CartMath.setQuantity(cart, 8, 0).isEmpty())
        assertTrue(CartMath.setQuantity(cart, 8, -3).isEmpty())
    }

    @Test fun `setting a quantity changes only that line and is capped`() {
        var cart = CartMath.add(emptyList(), product(8))
        cart = CartMath.add(cart, product(9))
        cart = CartMath.setQuantity(cart, 8, 4)
        assertEquals(4, cart.first { it.productId == 8 }.quantity)
        assertEquals(1, cart.first { it.productId == 9 }.quantity)
        assertEquals(CartMath.MAX_QTY, CartMath.setQuantity(cart, 8, 999).first { it.productId == 8 }.quantity)
    }

    @Test fun `removing an unknown product changes nothing`() {
        val cart = CartMath.add(emptyList(), product(8))
        assertEquals(cart, CartMath.remove(cart, 12345))
    }

    @Test fun `subtotal and count add up`() {
        var cart = CartMath.add(emptyList(), product(8, price = 280.0))
        cart = CartMath.add(cart, product(8, price = 280.0))
        cart = CartMath.add(cart, product(1, price = 350.0))
        assertEquals(910.0, CartMath.subtotal(cart), 0.001)
        assertEquals(3, CartMath.count(cart))
        assertEquals(0.0, Pricing.shipping(CartMath.subtotal(cart)), 0.001)
    }

    @Test fun `operations do not modify the original list`() {
        val original = CartMath.add(emptyList(), product(8))
        val copy = original.toList()
        CartMath.add(original, product(8)); CartMath.remove(original, 8); CartMath.setQuantity(original, 8, 5)
        assertEquals(copy, original)
        assertSame(original, CartMath.remove(original, 999).let { original })
    }
}
