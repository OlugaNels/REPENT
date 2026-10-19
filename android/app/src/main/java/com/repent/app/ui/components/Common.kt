package com.repent.app.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ShoppingCart
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import coil.compose.AsyncImage
import com.repent.app.data.remote.ServerConfig
import com.repent.app.data.model.Product
import com.repent.app.ui.theme.RepentBlack
import com.repent.app.ui.theme.RepentGold
import com.repent.app.ui.theme.RepentGray
import com.repent.app.ui.theme.RepentWhite
import com.repent.app.util.Pricing

/** Builds the full image URL from a product's `image` filename, served by the PHP app itself. */
fun productImageUrl(image: String?): String {
    if (image.isNullOrBlank()) return ""
    return ServerConfig.baseUrl.trimEnd('/') + "/assets/images/" + image
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun RepentTopBar(
    title: String,
    cartCount: Int = 0,
    onCartClick: (() -> Unit)? = null,
    navigationIcon: @Composable (() -> Unit)? = null,
) {
    TopAppBar(
        title = { Text(title, fontWeight = FontWeight.Medium) },
        navigationIcon = { navigationIcon?.invoke() },
        actions = {
            if (onCartClick != null) {
                BadgedBox(
                    badge = { if (cartCount > 0) Badge { Text(cartCount.toString()) } },
                    modifier = Modifier.padding(end = 8.dp),
                ) {
                    IconButton(onClick = onCartClick) {
                        Icon(Icons.Default.ShoppingCart, contentDescription = "Cart")
                    }
                }
            }
        },
        colors = TopAppBarDefaults.topAppBarColors(
            containerColor = MaterialTheme.colorScheme.background,
            titleContentColor = RepentBlack,
        ),
    )
}

@Composable
fun GoldButton(
    text: String,
    modifier: Modifier = Modifier,
    enabled: Boolean = true,
    onClick: () -> Unit,
) {
    Button(
        onClick = onClick,
        enabled = enabled,
        modifier = modifier.fillMaxWidth().height(48.dp),
        shape = RoundedCornerShape(4.dp),
        colors = ButtonDefaults.buttonColors(
            containerColor = RepentGold,
            contentColor = RepentBlack,
            disabledContainerColor = RepentGold.copy(alpha = 0.4f),
        ),
    ) {
        Text(text, fontWeight = FontWeight.SemiBold, letterSpacing = 0.5.sp)
    }
}

@Composable
fun OutlineDarkButton(
    text: String,
    modifier: Modifier = Modifier,
    onClick: () -> Unit,
) {
    OutlinedButton(
        onClick = onClick,
        modifier = modifier.fillMaxWidth().height(48.dp),
        shape = RoundedCornerShape(4.dp),
        colors = ButtonDefaults.outlinedButtonColors(contentColor = RepentBlack),
    ) {
        Text(text, fontWeight = FontWeight.Medium)
    }
}

@Composable
fun SectionLabel(text: String) {
    Text(
        text = text.uppercase(),
        style = MaterialTheme.typography.labelSmall,
        color = RepentGold,
    )
}

@Composable
fun LoadingState(modifier: Modifier = Modifier) {
    Box(modifier = modifier.fillMaxWidth().padding(48.dp), contentAlignment = Alignment.Center) {
        CircularProgressIndicator(color = RepentGold)
    }
}

@Composable
fun ErrorState(message: String, modifier: Modifier = Modifier, onRetry: (() -> Unit)? = null) {
    Column(
        modifier = modifier.fillMaxWidth().padding(32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Text(message, color = MaterialTheme.colorScheme.error, textAlign = TextAlign.Center)
        if (onRetry != null) {
            Spacer(Modifier.height(12.dp))
            TextButton(onClick = onRetry) { Text("Retry") }
        }
    }
}

@Composable
fun ProductCard(
    product: Product,
    onClick: () -> Unit,
    onAddToCart: () -> Unit,
    modifier: Modifier = Modifier,
) {
    Column(
        modifier = modifier
            .clip(RoundedCornerShape(6.dp))
            .background(RepentWhite)
            .clickable(onClick = onClick),
    ) {
        Box {
            AsyncImage(
                model = productImageUrl(product.image),
                contentDescription = product.name,
                contentScale = ContentScale.Crop,
                // A soft grey block shows while the photo loads (or if it cannot be loaded).
                modifier = Modifier.fillMaxWidth().aspectRatio(1f).background(RepentGray.copy(alpha = 0.15f)),
            )
            if (!product.badge.isNullOrBlank()) {
                Box(
                    modifier = Modifier
                        .align(Alignment.TopStart)
                        .padding(8.dp)
                        .clip(RoundedCornerShape(3.dp))
                        .background(RepentBlack)
                        .padding(horizontal = 8.dp, vertical = 4.dp),
                ) {
                    Text(product.badge, color = RepentGold, fontSize = 10.sp)
                }
            }
        }
        Column(Modifier.padding(12.dp)) {
            Text(product.name, style = MaterialTheme.typography.titleMedium, maxLines = 1)
            Spacer(Modifier.height(4.dp))
            Text(
                Pricing.rand(product.price),
                color = RepentBlack,
                fontWeight = FontWeight.SemiBold,
            )
            Spacer(Modifier.height(8.dp))
            OutlinedButton(
                onClick = onAddToCart,
                enabled = product.stock > 0,
                modifier = Modifier.fillMaxWidth().height(36.dp),
                shape = RoundedCornerShape(4.dp),
                colors = ButtonDefaults.outlinedButtonColors(contentColor = RepentBlack),
            ) {
                Text(if (product.stock > 0) "Add to Cart" else "Sold out", fontSize = 12.sp)
            }
        }
    }
}

@Composable
fun EmptyState(icon: String, title: String, subtitle: String? = null) {
    Column(
        modifier = Modifier.fillMaxWidth().padding(48.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Text(icon, fontSize = 40.sp)
        Spacer(Modifier.height(12.dp))
        Text(title, style = MaterialTheme.typography.titleLarge, textAlign = TextAlign.Center)
        if (subtitle != null) {
            Spacer(Modifier.height(4.dp))
            Text(subtitle, color = RepentGray, textAlign = TextAlign.Center)
        }
    }
}
