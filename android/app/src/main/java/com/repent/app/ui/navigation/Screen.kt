package com.repent.app.ui.navigation

sealed class Screen(val route: String) {
    data object Splash : Screen("splash")
    data object Home : Screen("home")
    data object Login : Screen("login")
    data object Register : Screen("register")
    data object Shop : Screen("shop?category={category}") {
        fun withCategory(category: String = "all") = "shop?category=$category"
    }
    data object ProductDetail : Screen("product/{productId}") {
        fun withId(id: Int) = "product/$id"
    }
    data object Cart : Screen("cart")
    data object Checkout : Screen("checkout")
    data object OrderConfirmation : Screen("order_confirmation/{orderId}/{total}") {
        fun withResult(orderId: Int, total: Double) = "order_confirmation/$orderId/$total"
    }
    data object Orders : Screen("orders")
    data object Devotionals : Screen("devotionals")
    data object Prayer : Screen("prayer")
    data object Newsletter : Screen("newsletter")
    data object Profile : Screen("profile")
    data object Admin : Screen("admin")
    data object Settings : Screen("settings")
    data object Help : Screen("help")
}
