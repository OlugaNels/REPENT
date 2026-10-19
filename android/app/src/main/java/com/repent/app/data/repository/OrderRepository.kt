package com.repent.app.data.repository

import com.repent.app.data.local.SessionManager
import com.repent.app.data.model.CheckoutItem
import com.repent.app.data.model.CheckoutRequest
import com.repent.app.data.model.CheckoutResponse
import com.repent.app.data.model.Order
import com.repent.app.data.remote.RetrofitClient

class OrderRepository(private val sessionManager: SessionManager) {
    private val api get() = RetrofitClient.apiService

    suspend fun checkout(
        items: List<CheckoutItem>,
        shippingAddress: String,
        city: String,
        postalCode: String,
        phone: String,
        paymentMethod: String,
    ): ApiResult<CheckoutResponse> =
        sessionManager.authed(
            "Could not process your order.",
            { api.checkout(it, CheckoutRequest(items, shippingAddress, city, postalCode, phone, paymentMethod)) },
        ) { if (it.success) it else null }

    suspend fun getMyOrders(): ApiResult<List<Order>> =
        sessionManager.authed("Could not load your orders.", { api.getMyOrders(it) }) { if (it.success) it.orders else null }
}
