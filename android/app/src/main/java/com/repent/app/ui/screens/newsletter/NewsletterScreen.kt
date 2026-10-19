package com.repent.app.ui.screens.newsletter

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import com.repent.app.data.repository.ApiResult
import com.repent.app.ui.components.FormField
import com.repent.app.ui.components.GoldButton
import com.repent.app.ui.components.MessageBanner
import com.repent.app.ui.rememberRepentApp
import com.repent.app.ui.theme.RepentGold
import com.repent.app.util.Validators
import kotlinx.coroutines.launch

@Composable
fun NewsletterScreen() {
    val app = rememberRepentApp()
    val scope = rememberCoroutineScope()
    val user by app.sessionManager.userFlow.collectAsState(initial = null)

    var name by rememberSaveable { mutableStateOf("") }
    var email by rememberSaveable { mutableStateOf("") }
    var frequency by remember { mutableStateOf("weekly") }
    var isSubmitting by remember { mutableStateOf(false) }
    var errorMessage by remember { mutableStateOf<String?>(null) }
    var nameError by remember { mutableStateOf<String?>(null) }
    var emailError by remember { mutableStateOf<String?>(null) }
    var resultStatus by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(user?.id) {
        user?.let { u ->
            if (name.isEmpty()) name = u.fullname
            if (email.isEmpty()) email = u.email
        }
    }

    Box(Modifier.fillMaxSize(), contentAlignment = Alignment.TopCenter) {
        Column(Modifier.widthIn(max = 640.dp).fillMaxWidth().verticalScroll(rememberScrollState()).padding(20.dp)) {
            Text("Stay Connected", style = MaterialTheme.typography.headlineMedium)
            Spacer(Modifier.height(4.dp))
            Text(
                "Subscribe for devotionals, new drops, and encouragement straight to your inbox.",
                style = MaterialTheme.typography.bodyMedium,
            )
            Spacer(Modifier.height(20.dp))

            if (resultStatus != null) {
                Text(
                    if (resultStatus == "already") "You're already subscribed — thank you!" else "You're subscribed! Welcome to the family.",
                    color = RepentGold,
                    style = MaterialTheme.typography.titleMedium,
                )
                return@Column
            }

            errorMessage?.let { MessageBanner(it, isError = true, modifier = Modifier.padding(bottom = 12.dp)) }

            FormField(value = name, onValueChange = { name = it }, label = "Your Name", error = nameError, maxLength = 100)
            FormField(
                value = email,
                onValueChange = { email = it },
                label = "Email Address",
                error = emailError,
                keyboardType = KeyboardType.Email,
                imeAction = ImeAction.Done,
                maxLength = 150,
            )
            Spacer(Modifier.height(8.dp))
            Text("How often?", style = MaterialTheme.typography.titleMedium)
            listOf("daily" to "Daily", "weekly" to "Weekly", "monthly" to "Monthly").forEach { (value, label) ->
                Row(verticalAlignment = Alignment.CenterVertically) {
                    RadioButton(selected = frequency == value, onClick = { frequency = value })
                    Text(label)
                }
            }

            Spacer(Modifier.height(20.dp))
            GoldButton(
                text = if (isSubmitting) "Subscribing…" else "Subscribe",
                enabled = !isSubmitting,
                onClick = {
                    errorMessage = null
                    nameError = Validators.required(name, "Your name")
                    emailError = Validators.email(email)
                    if (nameError != null || emailError != null) return@GoldButton
                    scope.launch {
                        isSubmitting = true
                        when (val result = app.newsletterRepository.subscribe(name.trim(), email.trim().lowercase(), frequency)) {
                            is ApiResult.Success -> resultStatus = result.data
                            is ApiResult.Failure -> errorMessage = result.message
                        }
                        isSubmitting = false
                    }
                },
            )
        }
    }
}
