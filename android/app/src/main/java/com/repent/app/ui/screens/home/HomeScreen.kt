package com.repent.app.ui.screens.home

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.text.font.FontStyle
import androidx.compose.ui.unit.dp
import com.repent.app.data.model.Product
import com.repent.app.data.repository.ApiResult
import com.repent.app.ui.components.ErrorState
import com.repent.app.ui.components.LoadingState
import com.repent.app.ui.components.ProductCard
import com.repent.app.ui.components.SectionLabel
import com.repent.app.ui.rememberRepentApp
import com.repent.app.ui.theme.RepentBlack
import com.repent.app.ui.theme.RepentGold
import com.repent.app.ui.theme.RepentWhite

@Composable
fun HomeScreen(
    onShopClick: () -> Unit,
    onDevotionalClick: () -> Unit,
    onPrayerClick: () -> Unit,
    onNewsletterClick: () -> Unit,
    onProductClick: (Int) -> Unit,
    onAddToCart: (Product) -> Unit,
) {
    val app = rememberRepentApp()
    var featured by remember { mutableStateOf<List<Product>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var loadError by remember { mutableStateOf<String?>(null) }
    var reloadKey by remember { mutableStateOf(0) }

    // Re-runs when the user taps Retry, so a server that was briefly down does not leave the page empty.
    LaunchedEffect(reloadKey) {
        loading = true
        when (val result = app.productRepository.getProducts("all")) {
            is ApiResult.Success -> { featured = result.data.take(6); loadError = null }
            is ApiResult.Failure -> loadError = result.message
        }
        loading = false
    }

    Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState())) {

        // ----- Hero -----
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .background(RepentBlack)
                .padding(horizontal = 24.dp, vertical = 40.dp),
        ) {
            Text(
                "FAITH-BASED CLOTHING & SPIRITUAL GROWTH",
                color = RepentGold,
                style = MaterialTheme.typography.labelSmall,
            )
            Spacer(Modifier.height(12.dp))
            Text(
                "REPENT†",
                color = RepentWhite,
                style = MaterialTheme.typography.displayLarge,
            )
            Spacer(Modifier.height(12.dp))
            Text(
                "\"Do not conform to the pattern of this world, but be transformed by the renewing of your mind.\"",
                color = RepentWhite.copy(alpha = 0.85f),
                fontStyle = FontStyle.Italic,
                style = MaterialTheme.typography.bodyLarge,
            )
            Spacer(Modifier.height(16.dp))
            Text(
                "Wear clothing that carries a message. Every piece we create is designed to reflect biblical values, spark conversations, and honour God in everyday life.",
                color = RepentWhite.copy(alpha = 0.65f),
                style = MaterialTheme.typography.bodyMedium,
            )
            Spacer(Modifier.height(24.dp))
            Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                Button(
                    onClick = onShopClick,
                    colors = ButtonDefaults.buttonColors(containerColor = RepentGold, contentColor = RepentBlack),
                    shape = RoundedCornerShape(4.dp),
                ) { Text("Shop the Collection") }
                OutlinedButton(
                    onClick = onDevotionalClick,
                    colors = ButtonDefaults.outlinedButtonColors(contentColor = RepentWhite),
                    shape = RoundedCornerShape(4.dp),
                ) { Text("Daily Devotional") }
            }
        }

        // ----- Featured products -----
        Column(Modifier.padding(20.dp)) {
            SectionLabel("New Arrivals")
            Text("Featured Collection", style = MaterialTheme.typography.headlineMedium)
            Spacer(Modifier.height(4.dp))
            Text(
                "Clothing that speaks faith louder than words. Each piece carries a message worth wearing.",
                style = MaterialTheme.typography.bodyMedium,
            )
            Spacer(Modifier.height(16.dp))

            if (loading) {
                LoadingState()
            } else if (loadError != null) {
                ErrorState(loadError ?: "Could not load products.", onRetry = { reloadKey++ })
            } else {
                LazyRow(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    items(featured) { product ->
                        ProductCard(
                            product = product,
                            onClick = { onProductClick(product.id) },
                            onAddToCart = { onAddToCart(product) },
                            modifier = Modifier.width(170.dp),
                        )
                    }
                }
            }

            Spacer(Modifier.height(20.dp))
            OutlinedButton(onClick = onShopClick, modifier = Modifier.fillMaxWidth()) {
                Text("View All Products")
            }
        }

        // ----- Get involved -----
        Column(Modifier.padding(20.dp)) {
            SectionLabel("Get Involved")
            Text("More Than Clothing", style = MaterialTheme.typography.headlineMedium)
            Spacer(Modifier.height(16.dp))
            Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                OutlinedButton(onClick = onPrayerClick, modifier = Modifier.weight(1f)) {
                    Text("Prayer Requests")
                }
                OutlinedButton(onClick = onNewsletterClick, modifier = Modifier.weight(1f)) {
                    Text("Newsletter")
                }
            }
            Spacer(Modifier.height(24.dp))
        }
    }
}
