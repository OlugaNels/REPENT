package com.repent.app

import android.app.Application
import com.repent.app.data.local.CartStore
import com.repent.app.data.local.SessionManager
import com.repent.app.data.remote.ServerConfig
import com.repent.app.data.repository.AdminRepository
import com.repent.app.data.repository.AuthRepository
import com.repent.app.data.repository.DevotionalRepository
import com.repent.app.data.repository.NewsletterRepository
import com.repent.app.data.repository.OrderRepository
import com.repent.app.data.repository.PrayerRepository
import com.repent.app.data.repository.ProductRepository
import com.repent.app.data.repository.ServerRepository
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.launch

/**
 * Simple hand-rolled service locator. A student/demo project doesn't
 * need Hilt/Dagger — this keeps one instance of each repository alive
 * for the whole app and hands them out to the screens.
 */
class RepentApp : Application() {

    private val appScope = CoroutineScope(SupervisorJob() + Dispatchers.Main)

    lateinit var sessionManager: SessionManager
        private set
    lateinit var cartStore: CartStore
        private set

    lateinit var authRepository: AuthRepository
        private set
    lateinit var productRepository: ProductRepository
        private set
    lateinit var devotionalRepository: DevotionalRepository
        private set
    lateinit var prayerRepository: PrayerRepository
        private set
    lateinit var newsletterRepository: NewsletterRepository
        private set
    lateinit var orderRepository: OrderRepository
        private set
    lateinit var adminRepository: AdminRepository
        private set
    lateinit var serverRepository: ServerRepository
        private set

    override fun onCreate() {
        super.onCreate()
        ServerConfig.init(this)

        sessionManager = SessionManager(this)
        cartStore = CartStore(this)

        authRepository = AuthRepository(sessionManager)
        productRepository = ProductRepository()
        devotionalRepository = DevotionalRepository()
        prayerRepository = PrayerRepository()
        newsletterRepository = NewsletterRepository()
        orderRepository = OrderRepository(sessionManager)
        adminRepository = AdminRepository(sessionManager)
        serverRepository = ServerRepository()

        // If the server rejects our saved token (expired, or the password was changed on another device),
        // forget it so the app shows the signed-out state instead of failing on every screen.
        ServerConfig.onUnauthorized = { appScope.launch { sessionManager.clearSession() } }
    }
}
