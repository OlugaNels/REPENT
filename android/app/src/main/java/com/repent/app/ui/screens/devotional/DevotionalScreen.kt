package com.repent.app.ui.screens.devotional

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontStyle
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.repent.app.data.model.Devotional
import com.repent.app.data.repository.ApiResult
import com.repent.app.ui.components.ErrorState
import com.repent.app.ui.components.LoadingState
import com.repent.app.ui.rememberRepentApp
import com.repent.app.ui.theme.RepentCream
import com.repent.app.ui.theme.RepentGold

@Composable
fun DevotionalScreen() {
    val app = rememberRepentApp()
    var devotionals by remember { mutableStateOf<List<Devotional>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    var reloadKey by remember { mutableStateOf(0) }

    // Re-runs when the user taps Retry after a failed load.
    LaunchedEffect(reloadKey) {
        loading = true
        error = null
        when (val result = app.devotionalRepository.getDevotionals()) {
            is ApiResult.Success -> devotionals = result.data
            is ApiResult.Failure -> error = result.message
        }
        loading = false
    }

    when {
        loading -> LoadingState()
        error != null -> ErrorState(error ?: "Could not load devotionals.", onRetry = { reloadKey++ })
        else -> LazyColumn(Modifier.fillMaxSize().padding(16.dp)) {
            items(devotionals, key = { it.id }) { d ->
                Column(
                    Modifier
                        .fillMaxWidth()
                        .padding(bottom = 16.dp)
                        .background(RepentCream, RoundedCornerShape(8.dp))
                        .padding(20.dp),
                ) {
                    if (!d.topic.isNullOrBlank()) {
                        Text(d.topic.uppercase(), color = RepentGold, style = MaterialTheme.typography.labelSmall)
                        Spacer(Modifier.height(6.dp))
                    }
                    Text(d.title, style = MaterialTheme.typography.headlineMedium)
                    Spacer(Modifier.height(10.dp))
                    Text("\"${d.verse}\"", fontStyle = FontStyle.Italic, style = MaterialTheme.typography.bodyLarge)
                    if (!d.reference.isNullOrBlank()) {
                        Text("— ${d.reference}", fontWeight = FontWeight.Medium, style = MaterialTheme.typography.bodyMedium)
                    }
                    Spacer(Modifier.height(12.dp))
                    Text(d.content, style = MaterialTheme.typography.bodyLarge)
                }
            }
        }
    }
}
