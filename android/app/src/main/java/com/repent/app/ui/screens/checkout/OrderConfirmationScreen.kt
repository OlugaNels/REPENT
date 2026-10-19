package com.repent.app.ui.screens.checkout

import com.repent.app.util.Pricing
import androidx.compose.foundation.layout.*
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.repent.app.ui.components.GoldButton
import com.repent.app.ui.components.OutlineDarkButton
import com.repent.app.ui.theme.RepentGold

@Composable
fun OrderConfirmationScreen(
    orderId: Int,
    total: Double,
    onViewOrders: () -> Unit,
    onContinueShopping: () -> Unit,
) {
    Column(
        modifier = Modifier.fillMaxSize().padding(32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center,
    ) {
        Text("✓", style = MaterialTheme.typography.displayLarge, color = RepentGold)
        Spacer(Modifier.height(16.dp))
        Text("Order Placed!", style = MaterialTheme.typography.headlineMedium)
        Spacer(Modifier.height(8.dp))
        Text(
            "Thank you for your order. A confirmation has been recorded under your account.",
            style = MaterialTheme.typography.bodyLarge,
            textAlign = androidx.compose.ui.text.style.TextAlign.Center,
        )
        Spacer(Modifier.height(20.dp))
        Text("Order #$orderId", fontWeight = FontWeight.SemiBold)
        Text("Total paid: ${Pricing.rand(total)}", fontWeight = FontWeight.SemiBold)
        Spacer(Modifier.height(32.dp))
        GoldButton(text = "View My Orders", onClick = onViewOrders)
        Spacer(Modifier.height(12.dp))
        OutlineDarkButton(text = "Continue Shopping", onClick = onContinueShopping)
    }
}
