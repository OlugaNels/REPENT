package com.repent.app.data.model

import com.google.gson.annotations.SerializedName

data class User(
    val id: Int,
    val fullname: String,
    val email: String,
    val role: String,
    @SerializedName("auth_provider") val authProvider: String? = null,
    val phone: String? = null,
    val address: String? = null,
    val city: String? = null,
    @SerializedName("postal_code") val postalCode: String? = null,
    @SerializedName("preferred_category") val preferredCategory: String? = null,
) {
    val isAdmin: Boolean get() = role == "admin"
    /** Google accounts have no REPENT† password, so "change password" is hidden for them. */
    val isGoogle: Boolean get() = authProvider == "google"
}

data class AuthResponse(
    val success: Boolean,
    val token: String?,
    val user: User?,
    val error: String?,
)

data class Product(
    val id: Int,
    val name: String,
    val description: String?,
    val price: Double,
    val category: String,
    val image: String?,
    val stock: Int,
    val badge: String?,
)

data class ProductsResponse(
    val success: Boolean,
    val products: List<Product> = emptyList(),
    val error: String?,
)

data class ProductResponse(
    val success: Boolean,
    val product: Product?,
    val error: String?,
)

data class Devotional(
    val id: Int,
    val title: String,
    val verse: String,
    val reference: String?,
    val content: String,
    val topic: String?,
    @SerializedName("created_at") val createdAt: String?,
)

data class DevotionalsResponse(
    val success: Boolean,
    val devotionals: List<Devotional> = emptyList(),
    val error: String?,
)

data class UserResponse(
    val success: Boolean,
    val user: User?,
    val error: String?,
)

data class HealthResponse(
    val success: Boolean,
    val status: String?,
    val products: Int?,
)

// ----- Request bodies (field names match what the PHP API reads) -----
data class LoginRequest(val email: String, val password: String)

data class RegisterRequest(val fullname: String, val email: String, val password: String)

data class GoogleLoginRequest(@SerializedName("id_token") val idToken: String)

data class ProfileRequest(
    val fullname: String,
    val phone: String,
    val address: String,
    val city: String,
    @SerializedName("postal_code") val postalCode: String,
    @SerializedName("preferred_category") val preferredCategory: String,
)

data class ChangePasswordRequest(
    @SerializedName("current_password") val currentPassword: String,
    @SerializedName("new_password") val newPassword: String,
)

data class OrderStatusRequest(
    @SerializedName("order_id") val orderId: Int,
    val status: String,
)

data class IdRequest(val id: Int)

data class SimpleResponse(
    val success: Boolean,
    val status: String? = null,
    val error: String? = null,
)

// ----- Cart (client-side only — mirrors what pages/cart.php stores in the PHP session) -----
data class CartItem(
    val productId: Int,
    val name: String,
    val price: Double,
    val image: String?,
    val quantity: Int,
) {
    val lineTotal: Double get() = price * quantity
}

// ----- Checkout -----
data class CheckoutItem(
    @SerializedName("product_id") val productId: Int,
    val quantity: Int,
)

data class CheckoutRequest(
    val items: List<CheckoutItem>,
    @SerializedName("shipping_address") val shippingAddress: String,
    val city: String,
    @SerializedName("postal_code") val postalCode: String,
    val phone: String,
    @SerializedName("payment_method") val paymentMethod: String,
)

data class CheckoutResponse(
    val success: Boolean,
    @SerializedName("order_id") val orderId: Int?,
    val subtotal: Double?,
    val shipping: Double?,
    val total: Double?,
    val error: String?,
)

data class OrderItem(
    @SerializedName("product_id") val productId: Int,
    @SerializedName("product_name") val productName: String,
    val price: Double,
    val quantity: Int,
)

data class Order(
    val id: Int,
    val total: Double,
    val shipping: Double,
    @SerializedName("shipping_address") val shippingAddress: String?,
    val city: String?,
    @SerializedName("postal_code") val postalCode: String?,
    val phone: String?,
    @SerializedName("payment_method") val paymentMethod: String?,
    val status: String,
    @SerializedName("created_at") val createdAt: String?,
    val items: List<OrderItem> = emptyList(),
)

data class OrdersResponse(
    val success: Boolean,
    val orders: List<Order> = emptyList(),
    val error: String?,
)

// ----- Prayer & Newsletter -----
data class PrayerRequestBody(
    val name: String,
    val email: String?,
    val subject: String?,
    val prayer: String,
    @SerializedName("is_private") val isPrivate: Boolean,
)

data class NewsletterRequestBody(
    val name: String,
    val email: String,
    val frequency: String,
)

// ----- Admin -----
data class AdminStats(
    val orders: Int,
    val users: Int,
    val products: Int,
    val prayers: Int,
    val subscribers: Int = 0,
    val revenue: Double = 0.0,
)

data class AdminStatsResponse(
    val success: Boolean,
    val stats: AdminStats?,
    val error: String?,
)

data class AdminOrder(
    val id: Int,
    val customer: String,
    val total: Double,
    val status: String,
    @SerializedName("created_at") val createdAt: String?,
)

data class AdminOrdersResponse(
    val success: Boolean,
    val orders: List<AdminOrder> = emptyList(),
    val error: String?,
)

data class AdminPrayer(
    val id: Int,
    val name: String,
    val email: String?,
    val subject: String?,
    val prayer: String,
    @SerializedName("is_private") val isPrivate: Boolean,
    @SerializedName("submitted_at") val submittedAt: String?,
)

data class AdminPrayersResponse(
    val success: Boolean,
    val prayers: List<AdminPrayer> = emptyList(),
    val error: String?,
)

data class AdminSubscriber(
    val id: Int,
    val name: String,
    val email: String,
    val frequency: String,
    @SerializedName("subscribed_at") val subscribedAt: String?,
)

data class AdminSubscribersResponse(
    val success: Boolean,
    val subscribers: List<AdminSubscriber> = emptyList(),
    val error: String?,
)
