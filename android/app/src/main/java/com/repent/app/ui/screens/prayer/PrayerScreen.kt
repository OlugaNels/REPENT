package com.repent.app.ui.screens.prayer

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
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
fun PrayerScreen() {
    val app = rememberRepentApp()
    val scope = rememberCoroutineScope()
    val user by app.sessionManager.userFlow.collectAsState(initial = null)

    var name by rememberSaveable { mutableStateOf("") }
    var email by rememberSaveable { mutableStateOf("") }
    var subject by rememberSaveable { mutableStateOf("") }
    var prayer by rememberSaveable { mutableStateOf("") }
    var isPrivate by rememberSaveable { mutableStateOf(false) }
    var isSubmitting by remember { mutableStateOf(false) }
    var errorMessage by remember { mutableStateOf<String?>(null) }
    var nameError by remember { mutableStateOf<String?>(null) }
    var emailError by remember { mutableStateOf<String?>(null) }
    var prayerError by remember { mutableStateOf<String?>(null) }
    var submitted by remember { mutableStateOf(false) }

    // Signed-in people don't have to retype their name and email.
    LaunchedEffect(user?.id) {
        user?.let { u ->
            if (name.isEmpty()) name = u.fullname.substringBefore(' ')
            if (email.isEmpty()) email = u.email
        }
    }

    if (submitted) {
        Column(
            Modifier.fillMaxSize().padding(32.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center,
        ) {
            Text("🙏", style = MaterialTheme.typography.displayLarge)
            Spacer(Modifier.height(16.dp))
            Text("Your prayer request has been received.", style = MaterialTheme.typography.titleLarge)
            Spacer(Modifier.height(8.dp))
            Text("Our prayer team will be lifting this up.", color = RepentGold)
            Spacer(Modifier.height(24.dp))
            GoldButton(text = "Submit Another Request", onClick = { prayer = ""; subject = ""; submitted = false })
        }
        return
    }

    Box(Modifier.fillMaxSize(), contentAlignment = Alignment.TopCenter) {
        Column(Modifier.widthIn(max = 640.dp).fillMaxWidth().verticalScroll(rememberScrollState()).padding(20.dp)) {
            Text("Prayer Requests", style = MaterialTheme.typography.headlineMedium)
            Spacer(Modifier.height(4.dp))
            Text("Share what's on your heart. You are not alone.", style = MaterialTheme.typography.bodyMedium)
            Spacer(Modifier.height(20.dp))

            errorMessage?.let { MessageBanner(it, isError = true, modifier = Modifier.padding(bottom = 12.dp)) }

            FormField(value = name, onValueChange = { name = it }, label = "Your Name", error = nameError, maxLength = 100)
            FormField(
                value = email,
                onValueChange = { email = it },
                label = "Email (optional)",
                error = emailError,
                keyboardType = KeyboardType.Email,
                maxLength = 150,
            )
            FormField(value = subject, onValueChange = { subject = it }, label = "Subject (optional)", maxLength = 100)
            FormField(
                value = prayer,
                onValueChange = { prayer = it },
                label = "Your Prayer Request",
                error = prayerError,
                singleLine = false,
                minLines = 5,
                maxLength = 2000,
            )
            Row(verticalAlignment = Alignment.CenterVertically) {
                Checkbox(checked = isPrivate, onCheckedChange = { isPrivate = it })
                Text("Keep this request private (only visible to the prayer team)")
            }

            Spacer(Modifier.height(20.dp))
            GoldButton(
                text = if (isSubmitting) "Submitting…" else "Submit Prayer Request",
                enabled = !isSubmitting,
                onClick = {
                    errorMessage = null
                    nameError = Validators.required(name, "Your name")
                    emailError = Validators.email(email, required = false)
                    prayerError = Validators.prayer(prayer)
                    if (nameError != null || emailError != null || prayerError != null) return@GoldButton
                    scope.launch {
                        isSubmitting = true
                        when (val result = app.prayerRepository.submitPrayer(name.trim(), email.trim().ifBlank { null }, subject.trim().ifBlank { null }, prayer.trim(), isPrivate)) {
                            is ApiResult.Success -> submitted = true
                            is ApiResult.Failure -> errorMessage = result.message
                        }
                        isSubmitting = false
                    }
                },
            )
        }
    }
}
