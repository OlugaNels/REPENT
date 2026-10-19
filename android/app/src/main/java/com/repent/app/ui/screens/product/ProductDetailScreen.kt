package com.repent.app.ui.screens.product

import com.repent.app.util.Pricing
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.repent.app.data.model.Product
import com.repent.app.data.repository.ApiResult
import com.repent.app.ui.components.ErrorState
import com.repent.app.ui.components.GoldButton
import com.repent.app.ui.components.LoadingState
import com.repent.app.ui.components.productImageUrl
import com.repent.app.ui.rememberRepentApp
import com.repent.app.ui.theme.RepentGold

@Composable
fun ProductDetailScreen(
    productId: Int,
    onAddToCart: (Product) -> Unit,
) {
    val app = rememberRepentApp()
    var product by remember { mutableStateOf<Product?>(null) }
    var error by remember { mutableStateOf<String?>(null) }
    var addedMessage by remember { mutableStateOf(false) }
    var reloadKey by remember { mutableStateOf(0) }

    // Re-runs when the user taps Retry after a failed load.
    LaunchedEffect(productId, reloadKey) {
        error = null
        when (val result = app.productRepository.getProduct(productId)) {
            is ApiResult.Success -> product = result.data
            is ApiResult.Failure -> error = result.message
        }
    }

    when {
        error != null -> ErrorState(error ?: "Could not load product.", onRetry = { reloadKey++ })
        product == null -> LoadingState()
        else -> {
            val p = product!!
            Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState())) {
                AsyncImage(
                    model = productImageUrl(p.image),
                    contentDescription = p.name,
                    contentScale = ContentScale.Crop,
                    modifier = Modifier.fillMaxWidth().height(320.dp),
                )
                Column(Modifier.padding(20.dp)) {
                    if (!p.badge.isNullOrBlank()) {
                        Text(p.badge, color = RepentGold, style = MaterialTheme.typography.labelSmall)
                        Spacer(Modifier.height(6.dp))
                    }
                    Text(p.name, style = MaterialTheme.typography.headlineMedium)
                    Spacer(Modifier.height(8.dp))
                    Text(
                        Pricing.rand(p.price),
                        style = MaterialTheme.typography.titleLarge,
                        fontWeight = FontWeight.SemiBold,
                    )
                    Spacer(Modifier.height(16.dp))
                    if (!p.description.isNullOrBlank()) {
                        Text(p.description, style = MaterialTheme.typography.bodyLarge)
                        Spacer(Modifier.height(20.dp))
                    }

                    Text(
                        if (p.stock > 0) "${p.stock} in stock" else "Sold out",
                        style = MaterialTheme.typography.bodyMedium,
                    )

                    Spacer(Modifier.height(20.dp))
                    if (addedMessage) {
                        Text("✓ Added to your cart!", color = RepentGold, modifier = Modifier.padding(bottom = 8.dp))
                    }
                    GoldButton(
                        text = if (p.stock > 0) "Add to Cart" else "Sold out",
                        enabled = p.stock > 0,
                        onClick = {
                            onAddToCart(p)
                            addedMessage = true
                        },
                    )
                }
            }
        }
    }
}
