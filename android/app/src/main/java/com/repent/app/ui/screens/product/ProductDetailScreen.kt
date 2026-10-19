package com.repent.app.ui.screens.product

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
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
    var selectedSize by remember { mutableStateOf("M") }
    var addedMessage by remember { mutableStateOf(false) }

    LaunchedEffect(productId) {
        when (val result = app.productRepository.getProduct(productId)) {
            is ApiResult.Success -> product = result.data
            is ApiResult.Failure -> error = result.message
        }
    }

    when {
        error != null -> ErrorState(error ?: "Could not load product.")
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
                        "R${p.price.toInt()}",
                        style = MaterialTheme.typography.titleLarge,
                        fontWeight = FontWeight.SemiBold,
                    )
                    Spacer(Modifier.height(16.dp))
                    if (!p.description.isNullOrBlank()) {
                        Text(p.description, style = MaterialTheme.typography.bodyLarge)
                        Spacer(Modifier.height(20.dp))
                    }

                    Text("Size", style = MaterialTheme.typography.titleMedium)
                    Spacer(Modifier.height(8.dp))
                    Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        listOf("S", "M", "L", "XL").forEach { size ->
                            val selected = size == selectedSize
                            OutlinedButton(
                                onClick = { selectedSize = size },
                                shape = RoundedCornerShape(4.dp),
                                colors = if (selected) {
                                    ButtonDefaults.outlinedButtonColors(
                                        containerColor = MaterialTheme.colorScheme.primary,
                                        contentColor = MaterialTheme.colorScheme.onPrimary,
                                    )
                                } else {
                                    ButtonDefaults.outlinedButtonColors()
                                },
                            ) { Text(size) }
                        }
                    }

                    Spacer(Modifier.height(12.dp))
                    Text(
                        "${p.stock} in stock",
                        style = MaterialTheme.typography.bodyMedium,
                    )

                    Spacer(Modifier.height(20.dp))
                    if (addedMessage) {
                        Text("✓ Added to your cart!", color = RepentGold, modifier = Modifier.padding(bottom = 8.dp))
                    }
                    GoldButton(
                        text = "Add to Cart",
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
