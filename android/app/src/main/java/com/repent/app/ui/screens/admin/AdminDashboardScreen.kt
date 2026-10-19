package com.repent.app.ui.screens.admin

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
import com.repent.app.data.model.AdminOrder
import com.repent.app.data.model.AdminPrayer
import com.repent.app.data.model.AdminStats
import com.repent.app.data.model.AdminSubscriber
import com.repent.app.data.repository.ApiResult
import com.repent.app.ui.components.ErrorState
import com.repent.app.ui.components.LoadingState
import com.repent.app.ui.rememberRepentApp
import com.repent.app.ui.theme.RepentCream
import com.repent.app.ui.theme.RepentGold

private val tabs = listOf("Overview", "Orders", "Prayers", "Subscribers")

@Composable
fun AdminDashboardScreen() {
    var selectedTab by remember { mutableIntStateOf(0) }

    Column(Modifier.fillMaxSize()) {
        Text(
            "Admin Dashboard",
            style = MaterialTheme.typography.headlineMedium,
            modifier = Modifier.padding(20.dp),
        )
        TabRow(selectedTabIndex = selectedTab) {
            tabs.forEachIndexed { index, title ->
                Tab(
                    selected = selectedTab == index,
                    onClick = { selectedTab = index },
                    text = { Text(title) },
                )
            }
        }
        when (selectedTab) {
            0 -> AdminOverviewTab()
            1 -> AdminOrdersTab()
            2 -> AdminPrayersTab()
            3 -> AdminSubscribersTab()
        }
    }
}

@Composable
private fun AdminOverviewTab() {
    val app = rememberRepentApp()
    var stats by remember { mutableStateOf<AdminStats?>(null) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(Unit) {
        when (val result = app.adminRepository.getStats()) {
            is ApiResult.Success -> stats = result.data
            is ApiResult.Failure -> error = result.message
        }
        loading = false
    }

    when {
        loading -> LoadingState()
        error != null -> ErrorState(error ?: "Could not load stats.")
        stats != null -> {
            val s = stats!!
            LazyColumn(
                Modifier.fillMaxSize().padding(20.dp),
            ) {
                item {
                    Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                        StatCard("Orders", s.orders.toString(), Modifier.weight(1f))
                        StatCard("Users", s.users.toString(), Modifier.weight(1f))
                    }
                    Spacer(Modifier.height(12.dp))
                    Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                        StatCard("Products", s.products.toString(), Modifier.weight(1f))
                        StatCard("Prayer Requests", s.prayers.toString(), Modifier.weight(1f))
                    }
                    Spacer(Modifier.height(12.dp))
                    Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                        StatCard("Subscribers", s.subscribers.toString(), Modifier.weight(1f))
                        StatCard("Revenue", com.repent.app.util.Pricing.rand(s.revenue), Modifier.weight(1f))
                    }
                }
            }
        }
    }
}

@Composable
private fun StatCard(label: String, value: String, modifier: Modifier = Modifier) {
    Column(
        modifier
            .background(RepentCream, RoundedCornerShape(8.dp))
            .padding(16.dp),
    ) {
        Text(value, style = MaterialTheme.typography.headlineMedium, color = RepentGold)
        Text(label, style = MaterialTheme.typography.bodyMedium)
    }
}

@Composable
private fun AdminOrdersTab() {
    val app = rememberRepentApp()
    var orders by remember { mutableStateOf<List<AdminOrder>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(Unit) {
        when (val result = app.adminRepository.getOrders()) {
            is ApiResult.Success -> orders = result.data
            is ApiResult.Failure -> error = result.message
        }
        loading = false
    }

    when {
        loading -> LoadingState()
        error != null -> ErrorState(error ?: "Could not load orders.")
        orders.isEmpty() -> ErrorState("No orders yet.")
        else -> LazyColumn(Modifier.fillMaxSize().padding(20.dp)) {
            items(orders, key = { it.id }) { order ->
                Row(
                    Modifier.fillMaxWidth().padding(vertical = 8.dp),
                    horizontalArrangement = Arrangement.SpaceBetween,
                ) {
                    Column {
                        Text("#${order.id} — ${order.customer}", fontWeight = FontWeight.Medium)
                        Text(order.status.replaceFirstChar { it.uppercase() }, color = RepentGold)
                    }
                    Text("R${order.total.toInt()}", fontWeight = FontWeight.SemiBold)
                }
                Divider()
            }
        }
    }
}

@Composable
private fun AdminPrayersTab() {
    val app = rememberRepentApp()
    var prayers by remember { mutableStateOf<List<AdminPrayer>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(Unit) {
        when (val result = app.adminRepository.getPrayers()) {
            is ApiResult.Success -> prayers = result.data
            is ApiResult.Failure -> error = result.message
        }
        loading = false
    }

    when {
        loading -> LoadingState()
        error != null -> ErrorState(error ?: "Could not load prayer requests.")
        prayers.isEmpty() -> ErrorState("No prayer requests yet.")
        else -> LazyColumn(Modifier.fillMaxSize().padding(20.dp)) {
            items(prayers, key = { it.id }) { p ->
                Column(Modifier.fillMaxWidth().padding(vertical = 8.dp)) {
                    Text("${p.name}${if (p.isPrivate) " (private)" else ""}", fontWeight = FontWeight.Medium)
                    if (!p.subject.isNullOrBlank()) Text(p.subject, color = RepentGold)
                    Text(p.prayer, style = MaterialTheme.typography.bodyMedium)
                }
                Divider()
            }
        }
    }
}

@Composable
private fun AdminSubscribersTab() {
    val app = rememberRepentApp()
    var subs by remember { mutableStateOf<List<AdminSubscriber>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(Unit) {
        when (val result = app.adminRepository.getSubscribers()) {
            is ApiResult.Success -> subs = result.data
            is ApiResult.Failure -> error = result.message
        }
        loading = false
    }

    when {
        loading -> LoadingState()
        error != null -> ErrorState(error ?: "Could not load subscribers.")
        subs.isEmpty() -> ErrorState("No subscribers yet.")
        else -> LazyColumn(Modifier.fillMaxSize().padding(20.dp)) {
            items(subs, key = { it.id }) { sub ->
                Column(Modifier.fillMaxWidth().padding(vertical = 8.dp)) {
                    Text(sub.name, fontWeight = FontWeight.Medium)
                    Text("${sub.email} • ${sub.frequency}", color = RepentGold)
                }
                Divider()
            }
        }
    }
}
