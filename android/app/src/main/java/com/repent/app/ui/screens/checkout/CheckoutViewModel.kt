package com.repent.app.ui.screens.checkout

import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.repent.app.data.local.CartStore
import com.repent.app.data.model.CartItem
import com.repent.app.data.model.CheckoutItem
import com.repent.app.data.repository.ApiResult
import com.repent.app.data.repository.OrderRepository
import com.repent.app.util.Validators
import kotlinx.coroutines.launch

class CheckoutViewModel(
    private val orderRepository: OrderRepository,
    private val cartStore: CartStore,
) : ViewModel() {

    var isSubmitting by mutableStateOf(false)
        private set
    var errorMessage by mutableStateOf<String?>(null)
        private set
    var addressError by mutableStateOf<String?>(null)
        private set
    var cityError by mutableStateOf<String?>(null)
        private set
    var postalError by mutableStateOf<String?>(null)
        private set
    var phoneError by mutableStateOf<String?>(null)
        private set

    fun placeOrder(
        cartItems: List<CartItem>,
        address: String,
        city: String,
        postalCode: String,
        phone: String,
        paymentMethod: String,
        onSuccess: (orderId: Int, total: Double) -> Unit,
    ) {
        if (isSubmitting) return          // ignore a second tap while the first order is on its way
        errorMessage = null
        addressError = Validators.required(address, "Delivery address")
        cityError = Validators.required(city, "City / Town")
        postalError = Validators.postalCode(postalCode)
        phoneError = Validators.phone(phone)
        if (cartItems.isEmpty()) {
            errorMessage = "Your cart is empty."
            return
        }
        if (listOf(addressError, cityError, postalError, phoneError).any { it != null }) return

        viewModelScope.launch {
            isSubmitting = true
            val items = cartItems.map { CheckoutItem(it.productId, it.quantity) }
            when (val result = orderRepository.checkout(items, address.trim(), city.trim(), postalCode.trim(), phone.trim(), paymentMethod)) {
                is ApiResult.Success -> {
                    cartStore.clearCart()
                    isSubmitting = false
                    onSuccess(result.data.orderId ?: 0, result.data.total ?: 0.0)
                    return@launch
                }
                is ApiResult.Failure -> errorMessage = result.message
            }
            isSubmitting = false
        }
    }
}
