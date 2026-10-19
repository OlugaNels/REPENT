package com.repent.app.ui.screens.shop

import androidx.compose.foundation.horizontalScroll
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.repent.app.data.model.Product
import com.repent.app.ui.components.ErrorState
import com.repent.app.ui.components.LoadingState
import com.repent.app.ui.components.ProductCard
import com.repent.app.ui.rememberRepentApp

private val categories = listOf(
    "all" to "All",
    "tshirts" to "T-Shirts",
    "hoodies" to "Hoodies & Crewnecks",
    "caps" to "Caps",
)

@Composable
fun ShopScreen(
    initialCategory: String = "all",
    onProductClick: (Int) -> Unit,
    onAddToCart: (Product) -> Unit,
) {
    val app = rememberRepentApp()
    val viewModel = remember { ShopViewModel(app.productRepository) }

    LaunchedEffect(Unit) { viewModel.load(initialCategory) }

    Column(Modifier.fillMaxSize()) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .horizontalScroll(rememberScrollState())
                .padding(horizontal = 16.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp),
        ) {
            categories.forEach { (value, label) ->
                FilterChip(
                    selected = viewModel.category == value,
                    onClick = { viewModel.selectCategory(value) },
                    label = { Text(label) },
                )
            }
        }

        when {
            viewModel.isLoading -> LoadingState()
            viewModel.errorMessage != null -> ErrorState(
                viewModel.errorMessage ?: "Something went wrong.",
                onRetry = { viewModel.selectCategory(viewModel.category) },
            )
            viewModel.products.isEmpty() -> ErrorState("No products found in this category.")
            else -> LazyVerticalGrid(
                columns = GridCells.Fixed(2),
                contentPadding = PaddingValues(16.dp),
                horizontalArrangement = Arrangement.spacedBy(12.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp),
                modifier = Modifier.fillMaxSize(),
            ) {
                items(viewModel.products) { product ->
                    ProductCard(
                        product = product,
                        onClick = { onProductClick(product.id) },
                        onAddToCart = { onAddToCart(product) },
                    )
                }
            }
        }
    }
}
