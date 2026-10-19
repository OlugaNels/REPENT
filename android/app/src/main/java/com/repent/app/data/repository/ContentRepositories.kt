package com.repent.app.data.repository

import com.repent.app.data.model.Devotional
import com.repent.app.data.model.HealthResponse
import com.repent.app.data.model.NewsletterRequestBody
import com.repent.app.data.model.PrayerRequestBody
import com.repent.app.data.model.Product
import com.repent.app.data.remote.RetrofitClient

class ProductRepository {
    private val api get() = RetrofitClient.apiService

    suspend fun getProducts(category: String = "all"): ApiResult<List<Product>> =
        apiCall("Could not load products.", { api.getProducts(category) }) { if (it.success) it.products else null }

    suspend fun getProduct(id: Int): ApiResult<Product> =
        apiCall("Product not found.", { api.getProduct(id) }) { if (it.success) it.product else null }
}

class DevotionalRepository {
    private val api get() = RetrofitClient.apiService

    suspend fun getDevotionals(): ApiResult<List<Devotional>> =
        apiCall("Could not load devotionals.", { api.getDevotionals() }) { if (it.success) it.devotionals else null }
}

class PrayerRepository {
    private val api get() = RetrofitClient.apiService

    suspend fun submitPrayer(
        name: String,
        email: String?,
        subject: String?,
        prayer: String,
        isPrivate: Boolean,
    ): ApiResult<Unit> =
        apiCall("Could not submit your prayer request.", { api.submitPrayer(PrayerRequestBody(name, email, subject, prayer, isPrivate)) }) {
            if (it.success) Unit else null
        }
}

class NewsletterRepository {
    private val api get() = RetrofitClient.apiService

    /** Returns "subscribed" or "already" on success. */
    suspend fun subscribe(name: String, email: String, frequency: String): ApiResult<String> =
        apiCall("Subscription failed. Please try again.", { api.subscribeNewsletter(NewsletterRequestBody(name, email, frequency)) }) {
            if (it.success) (it.status ?: "subscribed") else null
        }
}

/** Used by Settings > "Test connection": proves the app can reach the API and the MySQL database behind it. */
class ServerRepository {
    private val api get() = RetrofitClient.apiService

    suspend fun health(): ApiResult<HealthResponse> =
        apiCall("Could not reach the server.", { api.health() }) { if (it.success && it.status == "ok") it else null }
}
