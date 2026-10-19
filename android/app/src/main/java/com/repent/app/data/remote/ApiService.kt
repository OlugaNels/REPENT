package com.repent.app.data.remote

import com.repent.app.data.model.*
import retrofit2.Response
import retrofit2.http.*

/** One function per PHP endpoint in /api. Authenticated calls take the "Bearer <token>" header. */
interface ApiService {

    @GET("api/health.php")
    suspend fun health(): Response<HealthResponse>

    // ----- Auth -----
    @POST("api/register.php")
    suspend fun register(@Body body: RegisterRequest): Response<AuthResponse>

    @POST("api/login.php")
    suspend fun login(@Body body: LoginRequest): Response<AuthResponse>

    @POST("api/google_login.php")
    suspend fun googleLogin(@Body body: GoogleLoginRequest): Response<AuthResponse>

    @POST("api/logout.php")
    suspend fun logout(@Header("Authorization") auth: String): Response<SimpleResponse>

    // ----- Profile -----
    @GET("api/me.php")
    suspend fun getMe(@Header("Authorization") auth: String): Response<UserResponse>

    @PUT("api/me.php")
    suspend fun updateMe(@Header("Authorization") auth: String, @Body body: ProfileRequest): Response<UserResponse>

    @POST("api/change_password.php")
    suspend fun changePassword(@Header("Authorization") auth: String, @Body body: ChangePasswordRequest): Response<AuthResponse>

    // ----- Catalogue -----
    @GET("api/products.php")
    suspend fun getProducts(@Query("category") category: String = "all"): Response<ProductsResponse>

    @GET("api/product.php")
    suspend fun getProduct(@Query("id") id: Int): Response<ProductResponse>

    // ----- Devotionals -----
    @GET("api/devotionals.php")
    suspend fun getDevotionals(): Response<DevotionalsResponse>

    // ----- Prayer & Newsletter -----
    @POST("api/prayer.php")
    suspend fun submitPrayer(@Body body: PrayerRequestBody): Response<SimpleResponse>

    @POST("api/newsletter.php")
    suspend fun subscribeNewsletter(@Body body: NewsletterRequestBody): Response<SimpleResponse>

    // ----- Checkout & Orders (auth required) -----
    @POST("api/checkout.php")
    suspend fun checkout(
        @Header("Authorization") auth: String,
        @Body body: CheckoutRequest,
    ): Response<CheckoutResponse>

    @GET("api/orders.php")
    suspend fun getMyOrders(@Header("Authorization") auth: String): Response<OrdersResponse>

    // ----- Admin (admin token required) -----
    @GET("api/admin/stats.php")
    suspend fun getAdminStats(@Header("Authorization") auth: String): Response<AdminStatsResponse>

    @GET("api/admin/orders.php")
    suspend fun getAdminOrders(@Header("Authorization") auth: String): Response<AdminOrdersResponse>

    @PUT("api/admin/orders.php")
    suspend fun updateOrderStatus(
        @Header("Authorization") auth: String,
        @Body body: OrderStatusRequest,
    ): Response<SimpleResponse>

    @GET("api/admin/prayers.php")
    suspend fun getAdminPrayers(@Header("Authorization") auth: String): Response<AdminPrayersResponse>

    @GET("api/admin/subscribers.php")
    suspend fun getAdminSubscribers(@Header("Authorization") auth: String): Response<AdminSubscribersResponse>

    @HTTP(method = "DELETE", path = "api/admin/subscribers.php", hasBody = true)
    suspend fun deleteSubscriber(
        @Header("Authorization") auth: String,
        @Body body: IdRequest,
    ): Response<SimpleResponse>

    @GET("api/admin/products.php")
    suspend fun getAdminProducts(@Header("Authorization") auth: String): Response<ProductsResponse>

    @HTTP(method = "DELETE", path = "api/admin/products.php", hasBody = true)
    suspend fun deleteProduct(
        @Header("Authorization") auth: String,
        @Body body: IdRequest,
    ): Response<SimpleResponse>
}
