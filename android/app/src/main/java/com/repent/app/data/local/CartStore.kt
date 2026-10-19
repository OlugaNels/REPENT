package com.repent.app.data.local

import android.content.Context
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import com.repent.app.data.model.CartItem
import com.repent.app.data.model.Product
import com.repent.app.util.CartMath
import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map

private val Context.cartDataStore by preferencesDataStore(name = "repent_cart")

/**
 * The website keeps the cart in the PHP $_SESSION, keyed by product id
 * (see pages/cart.php). A mobile app has no server session to lean on,
 * so the cart lives on-device instead and is only sent to the server
 * once, at checkout (api/checkout.php), which re-validates prices and
 * stock from the database anyway.
 */
class CartStore(private val context: Context) {

    private object Keys {
        val CART_JSON = stringPreferencesKey("cart_json")
    }

    private val gson = Gson()
    private val listType = object : TypeToken<List<CartItem>>() {}.type

    val cartFlow: Flow<List<CartItem>> = context.cartDataStore.data.map { prefs ->
        prefs[Keys.CART_JSON]?.let {
            runCatching { gson.fromJson<List<CartItem>>(it, listType) }.getOrNull()
        } ?: emptyList()
    }

    suspend fun addItem(product: Product) {
        updateCart { CartMath.add(it, product) }
    }

    suspend fun updateQuantity(productId: Int, quantity: Int) {
        updateCart { CartMath.setQuantity(it, productId, quantity) }
    }

    suspend fun removeItem(productId: Int) {
        updateCart { CartMath.remove(it, productId) }
    }

    suspend fun clearCart() {
        context.cartDataStore.edit { it.remove(Keys.CART_JSON) }
    }

    private suspend fun updateCart(transform: (List<CartItem>) -> List<CartItem>) {
        context.cartDataStore.edit { prefs ->
            val current = prefs[Keys.CART_JSON]?.let {
                runCatching { gson.fromJson<List<CartItem>>(it, listType) }.getOrNull()
            } ?: emptyList()
            prefs[Keys.CART_JSON] = gson.toJson(transform(current))
        }
    }
}
