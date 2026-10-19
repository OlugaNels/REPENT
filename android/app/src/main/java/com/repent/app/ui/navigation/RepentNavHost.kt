package com.repent.app.ui.navigation

import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.MenuBook
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.ShoppingBag
import androidx.compose.material.icons.filled.ShoppingCart
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.repent.app.ui.components.RepentTopBar
import com.repent.app.ui.rememberRepentApp
import com.repent.app.ui.screens.admin.AdminDashboardScreen
import com.repent.app.ui.screens.auth.LoginScreen
import com.repent.app.ui.screens.auth.RegisterScreen
import com.repent.app.ui.screens.cart.CartScreen
import com.repent.app.ui.screens.checkout.CheckoutScreen
import com.repent.app.ui.screens.checkout.OrderConfirmationScreen
import com.repent.app.ui.screens.devotional.DevotionalScreen
import com.repent.app.ui.screens.help.HelpScreen
import com.repent.app.ui.screens.home.HomeScreen
import com.repent.app.ui.screens.newsletter.NewsletterScreen
import com.repent.app.ui.screens.orders.OrdersScreen
import com.repent.app.ui.screens.prayer.PrayerScreen
import com.repent.app.ui.screens.product.ProductDetailScreen
import com.repent.app.ui.screens.profile.ProfileScreen
import com.repent.app.ui.screens.settings.SettingsScreen
import com.repent.app.ui.screens.shop.ShopScreen
import com.repent.app.ui.screens.splash.SplashScreen
import kotlinx.coroutines.launch

private data class BottomNavItem(
    val screen: Screen,
    val label: String,
    val icon: androidx.compose.ui.graphics.vector.ImageVector,
)

private val bottomNavItems = listOf(
    BottomNavItem(Screen.Home, "Home", Icons.Default.Home),
    BottomNavItem(Screen.Shop, "Shop", Icons.Default.ShoppingBag),
    BottomNavItem(Screen.Devotionals, "Devotionals", Icons.Default.MenuBook),
    BottomNavItem(Screen.Cart, "Cart", Icons.Default.ShoppingCart),
    BottomNavItem(Screen.Profile, "Profile", Icons.Default.Person),
)

@Composable
fun RepentNavHost(navController: NavHostController) {
    val app = rememberRepentApp()
    val cartItems by app.cartStore.cartFlow.collectAsState(initial = emptyList())
    val cartCount = cartItems.sumOf { it.quantity }
    val user by app.sessionManager.userFlow.collectAsState(initial = null)

    val backStackEntry by navController.currentBackStackEntryAsState()
    val currentRoute = backStackEntry?.destination?.route
    val showBottomBar = bottomNavItems.any { it.screen.route == currentRoute } ||
        currentRoute?.startsWith("shop") == true

    val scope = androidx.compose.runtime.rememberCoroutineScope()

    Scaffold(
        bottomBar = {
            if (showBottomBar) {
                NavigationBar {
                    bottomNavItems.forEach { item ->
                        val selected = currentRoute == item.screen.route ||
                            (item.screen == Screen.Shop && currentRoute?.startsWith("shop") == true)
                        NavigationBarItem(
                            selected = selected,
                            onClick = {
                                val route = if (item.screen == Screen.Shop) Screen.Shop.withCategory() else item.screen.route
                                navController.navigate(route) {
                                    popUpTo(Screen.Home.route) { saveState = true }
                                    launchSingleTop = true
                                    restoreState = true
                                }
                            },
                            icon = { Icon(item.icon, contentDescription = item.label) },
                            label = { Text(item.label) },
                        )
                    }
                }
            }
        },
    ) { paddingValues ->
        NavHost(
            navController = navController,
            startDestination = Screen.Splash.route,
            modifier = Modifier.padding(paddingValues),
        ) {
            composable(Screen.Splash.route) {
                SplashScreen(onFinished = {
                    navController.navigate(Screen.Home.route) {
                        popUpTo(Screen.Splash.route) { inclusive = true }
                    }
                })
            }

            composable(Screen.Home.route) {
                HomeScreen(
                    onShopClick = { navController.navigate(Screen.Shop.withCategory()) },
                    onDevotionalClick = { navController.navigate(Screen.Devotionals.route) },
                    onPrayerClick = { navController.navigate(Screen.Prayer.route) },
                    onNewsletterClick = { navController.navigate(Screen.Newsletter.route) },
                    onProductClick = { id -> navController.navigate(Screen.ProductDetail.withId(id)) },
                    onAddToCart = { product -> scope.launch { app.cartStore.addItem(product) } },
                )
            }

            composable(
                route = Screen.Shop.route,
                arguments = listOf(navArgument("category") { type = NavType.StringType; defaultValue = "all" }),
            ) { entry ->
                val category = entry.arguments?.getString("category") ?: "all"
                Column {
                    RepentTopBar(title = "Shop", cartCount = cartCount, onCartClick = { navController.navigate(Screen.Cart.route) })
                    ShopScreen(
                        initialCategory = category,
                        onProductClick = { id -> navController.navigate(Screen.ProductDetail.withId(id)) },
                        onAddToCart = { product -> scope.launch { app.cartStore.addItem(product) } },
                    )
                }
            }

            composable(
                route = Screen.ProductDetail.route,
                arguments = listOf(navArgument("productId") { type = NavType.IntType }),
            ) { entry ->
                val id = entry.arguments?.getInt("productId") ?: 0
                Column {
                    RepentTopBar(
                        title = "Product",
                        cartCount = cartCount,
                        onCartClick = { navController.navigate(Screen.Cart.route) },
                        navigationIcon = { BackIcon(navController) },
                    )
                    ProductDetailScreen(
                        productId = id,
                        onAddToCart = { product -> scope.launch { app.cartStore.addItem(product) } },
                    )
                }
            }

            composable(Screen.Cart.route) {
                Column {
                    RepentTopBar(title = "My Cart", navigationIcon = { BackIcon(navController) })
                    CartScreen(
                        isLoggedIn = user != null,
                        onContinueShopping = { navController.navigate(Screen.Shop.withCategory()) },
                        onCheckout = { navController.navigate(Screen.Checkout.route) },
                        onLoginRequired = { navController.navigate(Screen.Login.route) },
                    )
                }
            }

            composable(Screen.Checkout.route) {
                Column {
                    RepentTopBar(title = "Checkout", navigationIcon = { BackIcon(navController) })
                    CheckoutScreen(
                        onOrderPlaced = { orderId, total ->
                            navController.navigate(Screen.OrderConfirmation.withResult(orderId, total)) {
                                popUpTo(Screen.Home.route)
                            }
                        },
                    )
                }
            }

            composable(
                route = Screen.OrderConfirmation.route,
                arguments = listOf(
                    navArgument("orderId") { type = NavType.IntType },
                    navArgument("total") { type = NavType.FloatType },
                ),
            ) { entry ->
                val orderId = entry.arguments?.getInt("orderId") ?: 0
                val total = entry.arguments?.getFloat("total")?.toDouble() ?: 0.0
                OrderConfirmationScreen(
                    orderId = orderId,
                    total = total,
                    onViewOrders = {
                        navController.navigate(Screen.Orders.route) { popUpTo(Screen.Home.route) }
                    },
                    onContinueShopping = {
                        navController.navigate(Screen.Home.route) { popUpTo(Screen.Home.route) { inclusive = true } }
                    },
                )
            }

            composable(Screen.Orders.route) {
                Column {
                    RepentTopBar(title = "My Orders", navigationIcon = { BackIcon(navController) })
                    OrdersScreen()
                }
            }

            composable(Screen.Devotionals.route) {
                Column {
                    RepentTopBar(title = "Devotionals")
                    DevotionalScreen()
                }
            }

            composable(Screen.Prayer.route) {
                Column {
                    RepentTopBar(title = "Prayer Requests", navigationIcon = { BackIcon(navController) })
                    PrayerScreen()
                }
            }

            composable(Screen.Newsletter.route) {
                Column {
                    RepentTopBar(title = "Newsletter", navigationIcon = { BackIcon(navController) })
                    NewsletterScreen()
                }
            }

            composable(Screen.Login.route) {
                Column {
                    RepentTopBar(title = "Sign In", navigationIcon = { BackIcon(navController) })
                    LoginScreen(
                        onLoginSuccess = { navController.popBackStack() },
                        onNavigateToRegister = { navController.navigate(Screen.Register.route) },
                        onNavigateToHelp = { navController.navigate(Screen.Help.route) },
                    )
                }
            }

            composable(Screen.Register.route) {
                Column {
                    RepentTopBar(title = "Create Account", navigationIcon = { BackIcon(navController) })
                    RegisterScreen(
                        // Registering from the Login screen leaves Login and Register stacked; drop both.
                        onRegisterSuccess = {
                            if (!navController.popBackStack(Screen.Login.route, inclusive = true)) navController.popBackStack()
                        },
                        onNavigateToLogin = { navController.popBackStack() },
                    )
                }
            }

            composable(Screen.Profile.route) {
                ProfileScreen(
                    onNavigateToLogin = { navController.navigate(Screen.Login.route) },
                    onNavigateToOrders = { navController.navigate(Screen.Orders.route) },
                    onNavigateToAdmin = { navController.navigate(Screen.Admin.route) },
                    onNavigateToSettings = { navController.navigate(Screen.Settings.route) },
                    onNavigateToHelp = { navController.navigate(Screen.Help.route) },
                    onLoggedOut = { },
                )
            }

            composable(Screen.Settings.route) {
                Column {
                    RepentTopBar(title = "Settings", navigationIcon = { BackIcon(navController) })
                    SettingsScreen(onNavigateToHelp = { navController.navigate(Screen.Help.route) })
                }
            }

            composable(Screen.Help.route) {
                Column {
                    RepentTopBar(title = "Help", navigationIcon = { BackIcon(navController) })
                    HelpScreen()
                }
            }

            composable(Screen.Admin.route) {
                Column {
                    RepentTopBar(title = "Admin", navigationIcon = { BackIcon(navController) })
                    AdminDashboardScreen()
                }
            }
        }
    }
}

@Composable
private fun BackIcon(navController: NavHostController) {
    IconButton(onClick = { navController.popBackStack() }) {
        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Back")
    }
}
