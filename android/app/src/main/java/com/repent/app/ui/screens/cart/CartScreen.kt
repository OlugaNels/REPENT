package com.repent.app.ui.screens.cart

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Close
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.repent.app.ui.components.EmptyState
import com.repent.app.ui.components.GoldButton
import com.repent.app.ui.components.productImageUrl
import com.repent.app.ui.rememberRepentApp
import com.repent.app.ui.theme.RepentGray
import com.repent.app.ui.theme.RepentGreen

@Composable
fun CartScreen(
    isLoggedIn: Boolean,
    onContinueShopping: () -> Unit,
    onCheckout: () -> Unit,
    onLoginRequired: () -> Unit,
) {
    val app = rememberRepentApp()
    val viewModel = remember { CartViewModel(app.cartStore) }
    val cartItems by viewModel.cart.collectAsState()

    val subtotal = cartItems.sumOf { it.lineTotal }
    val shipping = cartShipping(subtotal)
    val total = subtotal + shipping

    if (cartItems.isEmpty()) {
        Column(Modifier.fillMaxSize(), horizontalAlignment = Alignment.CenterHorizontally) {
            Spacer(Modifier.height(64.dp))
            EmptyState("🛍️", "Your cart is empty", "You haven't added any items yet.")
            GoldButton(text = "Browse Products", modifier = Modifier.padding(horizontal = 32.dp), onClick = onContinueShopping)
        }
        return
    }

    Column(Modifier.fillMaxSize()) {
        LazyColumn(Modifier.weight(1f).padding(horizontal = 16.dp)) {
            items(cartItems, key = { it.productId }) { item ->
                Row(
                    modifier = Modifier.fillMaxWidth().padding(vertical = 12.dp),
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    AsyncImage(
                        model = productImageUrl(item.image),
                        contentDescription = item.name,
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.size(64.dp).clip(RoundedCornerShape(6.dp)),
                    )
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text(item.name, fontWeight = FontWeight.Medium, maxLines = 1)
                        Text("R${item.price.toInt()} each", color = RepentGray, style = MaterialTheme.typography.bodyMedium)
                        Spacer(Modifier.height(6.dp))
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            OutlinedButton(
                                onClick = { viewModel.decrement(item.productId, item.quantity) },
                                modifier = Modifier.size(32.dp),
                                contentPadding = PaddingValues(0.dp),
                            ) { Text("−") }
                            Text(
                                "${item.quantity}",
                                modifier = Modifier.padding(horizontal = 12.dp),
                            )
                            OutlinedButton(
                                onClick = { viewModel.increment(item.productId, item.quantity) },
                                modifier = Modifier.size(32.dp),
                                contentPadding = PaddingValues(0.dp),
                            ) { Text("+") }
                        }
                    }
                    Column(horizontalAlignment = Alignment.End) {
                        Text("R${item.lineTotal.toInt()}", fontWeight = FontWeight.SemiBold)
                        IconButton(onClick = { viewModel.remove(item.productId) }) {
                            Icon(Icons.Default.Close, contentDescription = "Remove", tint = RepentGray)
                        }
                    }
                }
                Divider()
            }
        }

        // ----- Summary -----
        Column(
            modifier = Modifier.fillMaxWidth().padding(16.dp),
        ) {
            SummaryLine("Subtotal", "R${subtotal.toInt()}")
            SummaryLine(
                "Shipping",
                if (shipping == 0.0) "FREE" else "R${shipping.toInt()}",
                valueColor = if (shipping == 0.0) RepentGreen else null,
            )
            if (shipping > 0) {
                Text(
                    "Add R${(800 - subtotal).toInt()} more for free shipping",
                    color = RepentGray,
                    style = MaterialTheme.typography.bodyMedium,
                    modifier = Modifier.padding(bottom = 8.dp),
                )
            }
            Divider(modifier = Modifier.padding(vertical = 8.dp))
            SummaryLine("Total", "R${total.toInt()}", bold = true)

            Spacer(Modifier.height(12.dp))
            GoldButton(
                text = if (isLoggedIn) "Proceed to Checkout" else "Login to Checkout",
                onClick = { if (isLoggedIn) onCheckout() else onLoginRequired() },
            )
        }
    }
}

@Composable
private fun SummaryLine(label: String, value: String, bold: Boolean = false, valueColor: androidx.compose.ui.graphics.Color? = null) {
    Row(
        modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
        horizontalArrangement = Arrangement.SpaceBetween,
    ) {
        Text(label, fontWeight = if (bold) FontWeight.Bold else FontWeight.Normal)
        Text(
            value,
            fontWeight = if (bold) FontWeight.Bold else FontWeight.Normal,
            color = valueColor ?: MaterialTheme.colorScheme.onBackground,
        )
    }
}
