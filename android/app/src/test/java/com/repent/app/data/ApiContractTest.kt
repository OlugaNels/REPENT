package com.repent.app.data

import com.google.gson.Gson
import com.repent.app.data.model.*
import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Assert.assertNotNull
import org.junit.Assert.assertTrue
import org.junit.Test

/**
 * "Contract" tests: the files in src/test/resources/api were captured from the real PHP API
 * (the same one that talks to MySQL). If the app's data classes ever drift away from what
 * the API really sends, these tests fail.
 */
class ApiContractTest {
    private val gson = Gson()
    private fun json(name: String) = javaClass.classLoader!!.getResourceAsStream("api/$name.json")!!.bufferedReader().readText()
    private inline fun <reified T> load(name: String): T = gson.fromJson(json(name), T::class.java)

    @Test fun `login response`() {
        val r = load<AuthResponse>("login")
        assertTrue(r.success)
        assertTrue(r.token!!.contains('.'))
        val u = r.user!!
        assertEquals(3, u.id)
        assertEquals("Thandeka Mokoena", u.fullname)
        assertEquals("customer", u.role)
        assertFalse(u.isAdmin)
        assertFalse(u.isGoogle)
        assertEquals("Pretoria", u.city)
        assertEquals("0001", u.postalCode)
        assertEquals("hoodies", u.preferredCategory)
    }

    @Test fun `login error response`() {
        val r = load<AuthResponse>("error_login")
        assertFalse(r.success)
        assertEquals("Incorrect email or password.", r.error)
    }

    @Test fun `google login response`() {
        val r = load<AuthResponse>("google_login")
        assertTrue(r.user!!.isGoogle)
        assertEquals("gina@example.com", r.user!!.email)
        assertNotNull(r.token)
    }

    @Test fun `profile response`() {
        val r = load<UserResponse>("me")
        assertTrue(r.success)
        assertEquals("thandeka.mokoena@example.com", r.user!!.email)
    }

    @Test fun `products list`() {
        val r = load<ProductsResponse>("products")
        assertEquals(12, r.products.size)
        val first = r.products.first()
        assertEquals("Disciples Tee", first.name)
        assertEquals(350.0, first.price, 0.001)
        assertEquals("tshirts", first.category)
        assertEquals("tshirt-white.jpg", first.image)
        assertTrue(r.products.all { it.stock >= 0 && it.price > 0 })
        assertEquals(setOf("tshirts", "hoodies", "caps"), r.products.map { it.category }.toSet())
    }

    @Test fun `single product`() {
        val r = load<ProductResponse>("product")
        assertEquals(1, r.product!!.id)
    }

    @Test fun `devotionals`() {
        val r = load<DevotionalsResponse>("devotionals")
        assertEquals(12, r.devotionals.size)
        assertTrue(r.devotionals.all { it.title.isNotBlank() && it.verse.isNotBlank() && it.content.isNotBlank() })
        assertTrue(r.devotionals.any { it.reference == "Philippians 4:13" })
    }

    @Test fun `my orders`() {
        val r = load<OrdersResponse>("orders")
        assertEquals(2, r.orders.size)
        r.orders.forEach { o ->
            assertTrue(o.items.isNotEmpty())
            val items = o.items.sumOf { it.price * it.quantity }
            assertEquals("order ${o.id} total = items + shipping", o.total, items + o.shipping, 0.01)
        }
        assertTrue(r.orders[0].createdAt!! >= r.orders[1].createdAt!!)
    }

    @Test fun `checkout response`() {
        val r = load<CheckoutResponse>("checkout")
        assertTrue(r.success)
        assertEquals(280.0, r.subtotal!!, 0.001)
        assertEquals(95.0, r.shipping!!, 0.001)
        assertEquals(375.0, r.total!!, 0.001)
        assertTrue(r.orderId!! > 0)
    }

    @Test fun `admin stats`() {
        val s = load<AdminStatsResponse>("admin_stats").stats!!
        assertTrue(s.users >= 12); assertEquals(12, s.products); assertTrue(s.prayers >= 12); assertTrue(s.subscribers >= 12)
        assertTrue(s.revenue > 0)
    }

    @Test fun `admin lists`() {
        assertTrue(load<AdminOrdersResponse>("admin_orders").orders.size >= 12)
        val prayers = load<AdminPrayersResponse>("admin_prayers").prayers
        assertTrue(prayers.size >= 12)
        assertTrue(prayers.any { it.isPrivate } && prayers.any { !it.isPrivate })
        assertTrue(load<AdminSubscribersResponse>("admin_subscribers").subscribers.size >= 12)
    }

    @Test fun `health and newsletter`() {
        val h = load<HealthResponse>("health")
        assertEquals("ok", h.status); assertEquals(12, h.products)
        assertEquals("subscribed", load<SimpleResponse>("newsletter").status)
    }

    @Test fun `request bodies use the field names the API reads`() {
        assertEquals("""{"id_token":"abc"}""", gson.toJson(GoogleLoginRequest("abc")))
        assertEquals("""{"current_password":"a","new_password":"b"}""", gson.toJson(ChangePasswordRequest("a", "b")))
        assertEquals("""{"order_id":5,"status":"shipped"}""", gson.toJson(OrderStatusRequest(5, "shipped")))
        val profile = gson.toJson(ProfileRequest("N", "1", "a", "c", "0002", "caps"))
        assertTrue(profile.contains("\"postal_code\":\"0002\"") && profile.contains("\"preferred_category\":\"caps\""))
        val checkout = gson.toJson(CheckoutRequest(listOf(CheckoutItem(8, 2)), "a", "c", "0002", "082 123 4567", "eft"))
        assertTrue(checkout.contains("\"product_id\":8") && checkout.contains("\"shipping_address\":\"a\"") && checkout.contains("\"payment_method\":\"eft\""))
    }

    @Test fun `unknown or missing fields do not crash parsing`() {
        val u = gson.fromJson("""{"id":1,"fullname":"A","email":"a@b.co","role":"customer","brand_new_field":42}""", User::class.java)
        assertEquals(1, u.id)
        assertEquals(null, u.phone)
    }
}
