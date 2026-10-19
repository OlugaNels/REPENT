package com.repent.app.ui.screens.checkout

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.selection.selectableGroup
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import com.repent.app.ui.components.FormField
import com.repent.app.ui.components.GoldButton
import com.repent.app.ui.components.MessageBanner
import com.repent.app.ui.rememberRepentApp
import com.repent.app.util.CartMath
import com.repent.app.util.Pricing

@Composable
fun CheckoutScreen(
    onOrderPlaced: (orderId: Int, total: Double) -> Unit,
) {
    val app = rememberRepentApp()
    val viewModel = remember { CheckoutViewModel(app.orderRepository, app.cartStore) }
    val cartItems by app.cartStore.cartFlow.collectAsState(initial = emptyList())
    val user by app.sessionManager.userFlow.collectAsState(initial = null)

    val subtotal = CartMath.subtotal(cartItems)
    val shipping = Pricing.shipping(subtotal)
    val total = Pricing.total(subtotal)

    var address by remember { mutableStateOf("") }
    var city by remember { mutableStateOf("") }
    var postalCode by remember { mutableStateOf("") }
    var phone by remember { mutableStateOf("") }
    var paymentMethod by remember { mutableStateOf("eft") }

    // Start from the delivery details saved in Settings (only fills empty boxes, never overwrites typing).
    LaunchedEffect(user?.id) {
        user?.let { u ->
            if (address.isEmpty()) address = u.address.orEmpty()
            if (city.isEmpty()) city = u.city.orEmpty()
            if (postalCode.isEmpty()) postalCode = u.postalCode.orEmpty()
            if (phone.isEmpty()) phone = u.phone.orEmpty()
        }
    }

    Box(Modifier.fillMaxSize(), contentAlignment = Alignment.TopCenter) {
        Column(Modifier.widthIn(max = 640.dp).fillMaxWidth().verticalScroll(rememberScrollState()).padding(20.dp)) {
            Text("Delivery Details", style = MaterialTheme.typography.headlineMedium)
            Spacer(Modifier.height(16.dp))

            viewModel.errorMessage?.let { MessageBanner(it, isError = true, modifier = Modifier.padding(bottom = 12.dp)) }

            FormField(value = address, onValueChange = { address = it }, label = "Street Address", error = viewModel.addressError)
            Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                FormField(value = city, onValueChange = { city = it }, label = "City / Town", error = viewModel.cityError, modifier = Modifier.weight(1f), maxLength = 100)
                FormField(
                    value = postalCode,
                    onValueChange = { postalCode = it },
                    label = "Postal Code",
                    error = viewModel.postalError,
                    modifier = Modifier.weight(1f),
                    maxLength = 10,
                )
            }
            FormField(
                value = phone,
                onValueChange = { phone = it },
                label = "Contact Phone Number",
                error = viewModel.phoneError,
                keyboardType = KeyboardType.Phone,
                imeAction = ImeAction.Done,
                maxLength = 20,
            )

            Spacer(Modifier.height(16.dp))
            Text("Payment Method", style = MaterialTheme.typography.titleMedium)
            Column(Modifier.selectableGroup()) {
                listOf("eft" to "EFT / Bank Transfer", "card" to "Credit / Debit Card").forEach { (value, label) ->
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
                    ) {
                        RadioButton(selected = paymentMethod == value, onClick = { paymentMethod = value })
                        Text(label)
                    }
                }
            }

            Spacer(Modifier.height(24.dp))
            Text("Order Summary", style = MaterialTheme.typography.titleMedium)
            Spacer(Modifier.height(8.dp))
            cartItems.forEach { item ->
                Row(Modifier.fillMaxWidth().padding(vertical = 2.dp), horizontalArrangement = Arrangement.SpaceBetween) {
                    Text("${item.name} × ${item.quantity}", modifier = Modifier.weight(1f))
                    Text(Pricing.rand(item.lineTotal))
                }
            }
            HorizontalDivider(modifier = Modifier.padding(vertical = 8.dp))
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                Text("Shipping")
                Text(if (shipping == 0.0) "FREE" else Pricing.rand(shipping))
            }
            Row(Modifier.fillMaxWidth().padding(top = 4.dp), horizontalArrangement = Arrangement.SpaceBetween) {
                Text("Total", fontWeight = FontWeight.Bold)
                Text(Pricing.rand(total), fontWeight = FontWeight.Bold)
            }

            Spacer(Modifier.height(24.dp))
            GoldButton(
                text = if (viewModel.isSubmitting) "Placing Order…" else "Place Order",
                enabled = !viewModel.isSubmitting && cartItems.isNotEmpty(),
                onClick = {
                    viewModel.placeOrder(cartItems, address, city, postalCode, phone, paymentMethod, onOrderPlaced)
                },
            )
            Spacer(Modifier.height(24.dp))
        }
    }
}
