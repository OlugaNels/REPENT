package com.repent.app.ui.screens.settings

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import com.repent.app.BuildConfig
import com.repent.app.data.remote.ServerConfig
import com.repent.app.ui.components.FormField
import com.repent.app.ui.components.GoldButton
import com.repent.app.ui.components.MessageBanner
import com.repent.app.ui.components.OutlineDarkButton
import com.repent.app.ui.components.PasswordField
import com.repent.app.ui.components.SectionLabel
import com.repent.app.ui.rememberRepentApp
import com.repent.app.ui.theme.RepentGold
import com.repent.app.util.Validators

private val categories = listOf("all" to "All", "tshirts" to "T-Shirts", "hoodies" to "Hoodies", "caps" to "Caps")

@OptIn(ExperimentalMaterial3Api::class, ExperimentalLayoutApi::class)
@Composable
fun SettingsScreen(onNavigateToHelp: () -> Unit) {
    val app = rememberRepentApp()
    val viewModel = remember { SettingsViewModel(app.authRepository, app.serverRepository) }
    val user by app.sessionManager.userFlow.collectAsState(initial = null)

    var name by remember { mutableStateOf("") }
    var phone by remember { mutableStateOf("") }
    var address by remember { mutableStateOf("") }
    var city by remember { mutableStateOf("") }
    var postal by remember { mutableStateOf("") }
    var category by remember { mutableStateOf("all") }
    var filled by remember { mutableStateOf(false) }

    // Fill the form once from the saved profile (and again only if a different person signs in).
    var filledFor by remember { mutableStateOf<Int?>(null) }
    LaunchedEffect(user?.id) {
        val u = user
        if (u != null && filledFor != u.id) {
            name = u.fullname; phone = u.phone.orEmpty(); address = u.address.orEmpty()
            city = u.city.orEmpty(); postal = u.postalCode.orEmpty(); category = u.preferredCategory ?: "all"
            filledFor = u.id; filled = true
        }
    }

    var currentPw by remember { mutableStateOf("") }
    var newPw by remember { mutableStateOf("") }
    var confirmPw by remember { mutableStateOf("") }
    var serverUrl by remember { mutableStateOf(ServerConfig.baseUrl) }

    Box(Modifier.fillMaxSize(), contentAlignment = Alignment.TopCenter) {
        Column(
            Modifier.widthIn(max = 640.dp).fillMaxWidth().verticalScroll(rememberScrollState()).padding(20.dp),
        ) {
            // ---------------- Personal details ----------------
            if (user != null) {
                SectionLabel("Personal details")
                Spacer(Modifier.height(8.dp))
                viewModel.profileMessage?.let { MessageBanner(it.text, it.isError, Modifier.padding(bottom = 8.dp)) }

                FormField(value = user?.email.orEmpty(), onValueChange = {}, label = "Email (cannot be changed)", readOnly = true, enabled = false)
                FormField(value = name, onValueChange = { name = it }, label = "Full Name", error = viewModel.nameError, maxLength = 100)
                FormField(value = phone, onValueChange = { phone = it }, label = "Phone", error = viewModel.phoneError, keyboardType = KeyboardType.Phone, maxLength = 20)
                FormField(value = address, onValueChange = { address = it }, label = "Street Address")
                Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    FormField(value = city, onValueChange = { city = it }, label = "City / Town", modifier = Modifier.weight(1f), maxLength = 100)
                    FormField(value = postal, onValueChange = { postal = it }, label = "Postal Code", modifier = Modifier.weight(1f), error = viewModel.postalError, maxLength = 10)
                }
                Text("I'm mostly interested in", style = MaterialTheme.typography.titleSmall)
                Spacer(Modifier.height(4.dp))
                FlowRow(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    categories.forEach { (value, label) ->
                        FilterChip(selected = category == value, onClick = { category = value }, label = { Text(label) })
                    }
                }
                Spacer(Modifier.height(16.dp))
                GoldButton(
                    text = if (viewModel.savingProfile) "Saving…" else "Save Details",
                    enabled = !viewModel.savingProfile && filled,
                    onClick = { viewModel.saveProfile(name, phone, address, city, postal, category) },
                )

                // ---------------- Password ----------------
                Spacer(Modifier.height(32.dp))
                SectionLabel("Password")
                Spacer(Modifier.height(8.dp))
                if (user?.isGoogle == true) {
                    Text(
                        "You sign in with Google, so there is no REPENT† password to change. Manage your password in your Google account.",
                        style = MaterialTheme.typography.bodyMedium,
                    )
                } else {
                    viewModel.passwordMessage?.let { MessageBanner(it.text, it.isError, Modifier.padding(bottom = 8.dp)) }
                    PasswordField(value = currentPw, onValueChange = { currentPw = it }, label = "Current Password", error = viewModel.currentError)
                    PasswordField(value = newPw, onValueChange = { newPw = it }, label = "New Password", error = viewModel.newError)
                    Text(Validators.PASSWORD_HINT, style = MaterialTheme.typography.bodySmall, modifier = Modifier.padding(bottom = 8.dp))
                    PasswordField(value = confirmPw, onValueChange = { confirmPw = it }, label = "Confirm New Password", error = viewModel.confirmError, imeAction = ImeAction.Done)
                    Spacer(Modifier.height(12.dp))
                    GoldButton(
                        text = if (viewModel.changingPassword) "Changing…" else "Change Password",
                        enabled = !viewModel.changingPassword,
                        onClick = {
                            viewModel.changePassword(currentPw, newPw, confirmPw) { currentPw = ""; newPw = ""; confirmPw = "" }
                        },
                    )
                }
                Spacer(Modifier.height(32.dp))
            }

            // ---------------- Server ----------------
            SectionLabel("Server connection")
            Spacer(Modifier.height(8.dp))
            Text(
                "The app talks to the REPENT† API (PHP + MySQL). If your laptop's address changes, update it here — no rebuild needed.",
                style = MaterialTheme.typography.bodySmall,
            )
            Spacer(Modifier.height(8.dp))
            viewModel.serverMessage?.let { MessageBanner(it.text, it.isError, Modifier.padding(bottom = 8.dp)) }
            FormField(
                value = serverUrl,
                onValueChange = { serverUrl = it },
                label = "Server address (e.g. 192.168.0.10:8000)",
                keyboardType = KeyboardType.Uri,
                imeAction = ImeAction.Done,
                maxLength = 200,
            )
            Row(horizontalArrangement = Arrangement.spacedBy(12.dp), modifier = Modifier.fillMaxWidth()) {
                OutlineDarkButton("Save", Modifier.weight(1f)) { serverUrl = viewModel.saveServer(serverUrl) }
                OutlineDarkButton(if (viewModel.testing) "Testing…" else "Test", Modifier.weight(1f)) {
                    serverUrl = viewModel.saveServer(serverUrl)
                    viewModel.testConnection()
                }
            }
            TextButton(onClick = { serverUrl = viewModel.resetServer() }) { Text("Reset to default") }

            // ---------------- Help / About ----------------
            Spacer(Modifier.height(24.dp))
            OutlineDarkButton("Help & Documentation", onClick = onNavigateToHelp)
            Spacer(Modifier.height(16.dp))
            Text(
                "REPENT† for Android · version ${BuildConfig.VERSION_NAME}",
                style = MaterialTheme.typography.bodySmall,
                color = RepentGold,
                fontWeight = FontWeight.Medium,
            )
            Spacer(Modifier.height(24.dp))
        }
    }
}
