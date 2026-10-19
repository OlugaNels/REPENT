package com.repent.app.ui.screens.shop

import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.repent.app.data.model.Product
import com.repent.app.data.repository.ApiResult
import com.repent.app.data.repository.ProductRepository
import kotlinx.coroutines.launch

class ShopViewModel(private val repository: ProductRepository) : ViewModel() {

    var products by mutableStateOf<List<Product>>(emptyList())
        private set
    var isLoading by mutableStateOf(false)
        private set
    var errorMessage by mutableStateOf<String?>(null)
        private set
    var category by mutableStateOf("all")
        private set

    fun load(initialCategory: String = "all") {
        category = initialCategory
        fetch()
    }

    fun setCategory(newCategory: String) {
        category = newCategory
        fetch()
    }

    private fun fetch() {
        viewModelScope.launch {
            isLoading = true
            errorMessage = null
            when (val result = repository.getProducts(category)) {
                is ApiResult.Success -> products = result.data
                is ApiResult.Failure -> errorMessage = result.message
            }
            isLoading = false
        }
    }
}
