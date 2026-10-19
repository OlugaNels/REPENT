package com.repent.app.data.repository

import com.repent.app.data.local.SessionManager
import com.repent.app.data.model.*
import com.repent.app.data.remote.RetrofitClient

/** Admin-only calls. The server checks the admin role on every request; the app just shows the result. */
class AdminRepository(private val sessionManager: SessionManager) {
    private val api get() = RetrofitClient.apiService

    suspend fun getStats(): ApiResult<AdminStats> =
        sessionManager.authed("Could not load dashboard stats.", { api.getAdminStats(it) }) { if (it.success) it.stats else null }

    suspend fun getOrders(): ApiResult<List<AdminOrder>> =
        sessionManager.authed("Could not load orders.", { api.getAdminOrders(it) }) { if (it.success) it.orders else null }

    suspend fun updateOrderStatus(orderId: Int, status: String): ApiResult<Unit> =
        sessionManager.authed("Could not update the order.", { api.updateOrderStatus(it, OrderStatusRequest(orderId, status)) }) {
            if (it.success) Unit else null
        }

    suspend fun getPrayers(): ApiResult<List<AdminPrayer>> =
        sessionManager.authed("Could not load prayer requests.", { api.getAdminPrayers(it) }) { if (it.success) it.prayers else null }

    suspend fun getSubscribers(): ApiResult<List<AdminSubscriber>> =
        sessionManager.authed("Could not load subscribers.", { api.getAdminSubscribers(it) }) { if (it.success) it.subscribers else null }

    suspend fun removeSubscriber(id: Int): ApiResult<Unit> =
        sessionManager.authed("Could not remove the subscriber.", { api.deleteSubscriber(it, IdRequest(id)) }) { if (it.success) Unit else null }

    suspend fun getProducts(): ApiResult<List<Product>> =
        sessionManager.authed("Could not load products.", { api.getAdminProducts(it) }) { if (it.success) it.products else null }

    suspend fun deleteProduct(id: Int): ApiResult<Unit> =
        sessionManager.authed("Could not delete the product.", { api.deleteProduct(it, IdRequest(id)) }) { if (it.success) Unit else null }
}
