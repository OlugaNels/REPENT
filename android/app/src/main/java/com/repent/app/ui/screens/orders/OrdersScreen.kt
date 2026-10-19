package com.repent.app.ui.screens.orders

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.repent.app.data.model.Order
import com.repent.app.data.repository.ApiResult
import com.repent.app.ui.components.EmptyState
import com.repent.app.ui.components.ErrorState
import com.repent.app.ui.components.LoadingState
import com.repent.app.ui.rememberRepentApp
import com.repent.app.ui.theme.RepentCream
import com.repent.app.ui.theme.RepentGray

@Composable
fun OrdersScreen() {
    val app = rememberRepentApp()
    var orders by remember { mutableStateOf<List<Order>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(Unit) {
        when (val result = app.orderRepository.getMyOrders()) {
            is ApiResult.Success -> orders = result.data
            is ApiResult.Failure -> error = result.message
        }
        loading = false
    }

    when {
        loading -> LoadingState()
        error != null -> ErrorState(error ?: "Could not load your orders.")
        orders.isEmpty() -> EmptyState("📦", "No orders yet", "Your order history will appear here.")
        else -> LazyColumn(Modifier.fillMaxSize().padding(16.dp)) {
            items(orders, key = { it.id }) { order ->
                Column(
                    Modifier
                        .fillMaxWidth()
                        .padding(bottom = 12.dp)
                        .background(RepentCream, RoundedCornerShape(8.dp))
                        .padding(16.dp),
                ) {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text("Order #${order.id}", fontWeight = FontWeight.SemiBold)
                        StatusPill(order.status)
                    }
                    Spacer(Modifier.height(4.dp))
                    order.createdAt?.let { Text(it, color = RepentGray, style = MaterialTheme.typography.bodyMedium) }
                    Spacer(Modifier.height(8.dp))
                    order.items.forEach { item ->
                        Text("${item.productName} × ${item.quantity}", style = MaterialTheme.typography.bodyMedium)
                    }
                    Spacer(Modifier.height(8.dp))
                    Text("Total: R${order.total.toInt()}", fontWeight = FontWeight.SemiBold)
                }
            }
        }
    }
}

@Composable
private fun StatusPill(status: String) {
    Box(
        Modifier
            .background(MaterialTheme.colorScheme.primary, RoundedCornerShape(12.dp))
            .padding(horizontal = 10.dp, vertical = 4.dp),
    ) {
        Text(
            status.replaceFirstChar { it.uppercase() },
            color = MaterialTheme.colorScheme.onPrimary,
            style = MaterialTheme.typography.labelSmall,
        )
    }
}
