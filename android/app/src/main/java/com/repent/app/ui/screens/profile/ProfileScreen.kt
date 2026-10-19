package com.repent.app.ui.screens.profile

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.repent.app.data.model.User
import com.repent.app.ui.components.GoldButton
import com.repent.app.ui.components.OutlineDarkButton
import com.repent.app.ui.rememberRepentApp
import com.repent.app.ui.theme.RepentGold
import kotlinx.coroutines.launch

@Composable
fun ProfileScreen(
    onNavigateToLogin: () -> Unit,
    onNavigateToOrders: () -> Unit,
    onNavigateToAdmin: () -> Unit,
    onNavigateToSettings: () -> Unit,
    onNavigateToHelp: () -> Unit,
    onLoggedOut: () -> Unit,
) {
    val app = rememberRepentApp()
    val scope = rememberCoroutineScope()
    val user by app.sessionManager.userFlow.collectAsState(initial = null)

    Column(Modifier.fillMaxSize().padding(24.dp)) {
        if (user == null) {
            Column(
                Modifier.fillMaxSize(),
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.Center,
            ) {
                Text("You're not signed in", style = MaterialTheme.typography.titleLarge)
                Spacer(Modifier.height(16.dp))
                GoldButton(text = "Login / Create Account", onClick = onNavigateToLogin)
                Spacer(Modifier.height(12.dp))
                OutlineDarkButton(text = "Help & Documentation", onClick = onNavigateToHelp)
            }
        } else {
            Column(Modifier.fillMaxSize().verticalScroll(rememberScrollState())) {
            ProfileContent(
                user = user!!,
                onNavigateToOrders = onNavigateToOrders,
                onNavigateToAdmin = onNavigateToAdmin,
                onNavigateToSettings = onNavigateToSettings,
                onNavigateToHelp = onNavigateToHelp,
                onLogout = {
                    scope.launch {
                        app.authRepository.logout()
                        onLoggedOut()
                    }
                },
            )
            }
        }
    }
}

@Composable
private fun ProfileContent(
    user: User,
    onNavigateToOrders: () -> Unit,
    onNavigateToAdmin: () -> Unit,
    onNavigateToSettings: () -> Unit,
    onNavigateToHelp: () -> Unit,
    onLogout: () -> Unit,
) {
    Text("My Account", style = MaterialTheme.typography.headlineMedium)
    Spacer(Modifier.height(16.dp))
    Text(user.fullname, style = MaterialTheme.typography.titleLarge)
    Text(user.email, color = RepentGold)
    Spacer(Modifier.height(24.dp))

    OutlineDarkButton(text = "My Orders", onClick = onNavigateToOrders)
    Spacer(Modifier.height(12.dp))
    OutlineDarkButton(text = "Settings", onClick = onNavigateToSettings)
    Spacer(Modifier.height(12.dp))
    OutlineDarkButton(text = "Help & Documentation", onClick = onNavigateToHelp)
    Spacer(Modifier.height(12.dp))
    if (user.isAdmin) {
        OutlineDarkButton(text = "Admin Dashboard", onClick = onNavigateToAdmin)
        Spacer(Modifier.height(12.dp))
    }
    OutlineDarkButton(text = "Log Out", onClick = onLogout)
}
