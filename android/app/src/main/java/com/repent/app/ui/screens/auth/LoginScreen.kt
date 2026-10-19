package com.repent.app.ui.screens.auth

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import com.repent.app.BuildConfig
import com.repent.app.ui.components.FormField
import com.repent.app.ui.components.GoldButton
import com.repent.app.ui.components.GoogleButton
import com.repent.app.ui.components.MessageBanner
import com.repent.app.ui.components.PasswordField
import com.repent.app.ui.rememberRepentApp
import com.repent.app.ui.theme.RepentGold

@Composable
fun LoginScreen(
    onLoginSuccess: () -> Unit,
    onNavigateToRegister: () -> Unit,
    onNavigateToHelp: () -> Unit,
) {
    val app = rememberRepentApp()
    val context = LocalContext.current
    val viewModel = remember { AuthViewModel(app.authRepository) }

    var email by rememberSaveable { mutableStateOf("") }
    var password by remember { mutableStateOf("") }

    // Centred, at most 480dp wide, so the form looks right on a tablet as well as a phone.
    Box(Modifier.fillMaxSize(), contentAlignment = Alignment.TopCenter) {
    Column(
        modifier = Modifier
            .widthIn(max = 480.dp)
            .fillMaxWidth()
            .verticalScroll(rememberScrollState())
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Spacer(Modifier.height(32.dp))
        Text("†", style = MaterialTheme.typography.displayLarge, color = RepentGold)
        Spacer(Modifier.height(8.dp))
        Text("Welcome Back", style = MaterialTheme.typography.headlineMedium)
        Text("Sign in to your REPENT† account", style = MaterialTheme.typography.bodyMedium)
        Spacer(Modifier.height(28.dp))

        viewModel.errorMessage?.let { MessageBanner(it, isError = true, modifier = Modifier.padding(bottom = 12.dp)) }

        FormField(
            value = email,
            onValueChange = { email = it },
            label = "Email Address",
            error = viewModel.emailError,
            keyboardType = KeyboardType.Email,
            maxLength = 150,
        )
        PasswordField(
            value = password,
            onValueChange = { password = it },
            label = "Password",
            error = viewModel.passwordError,
            imeAction = ImeAction.Done,
        )
        Spacer(Modifier.height(12.dp))

        GoldButton(
            text = if (viewModel.isLoading) "Please wait…" else "Sign In",
            enabled = !viewModel.isLoading,
            onClick = { viewModel.login(email, password, onLoginSuccess) },
        )

        Spacer(Modifier.height(16.dp))
        Text("or", style = MaterialTheme.typography.bodySmall)
        Spacer(Modifier.height(16.dp))

        GoogleButton(
            enabled = !viewModel.isLoading,
            onClick = {
                viewModel.googleSignIn(
                    getToken = { requestGoogleIdToken(context, BuildConfig.GOOGLE_WEB_CLIENT_ID) },
                    onSuccess = onLoginSuccess,
                )
            },
        )

        Spacer(Modifier.height(16.dp))
        TextButton(onClick = onNavigateToRegister) { Text("New to REPENT†? Create an account") }
        TextButton(onClick = onNavigateToHelp) { Text("Need help?") }
    }
    }
}
