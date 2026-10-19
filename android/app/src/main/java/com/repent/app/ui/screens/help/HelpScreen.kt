package com.repent.app.ui.screens.help

import androidx.compose.animation.animateContentSize
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ExpandLess
import androidx.compose.material.icons.filled.ExpandMore
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.repent.app.ui.components.SectionLabel
import com.repent.app.ui.theme.RepentGold
import com.repent.app.ui.theme.RepentWhite

private data class HelpTopic(val title: String, val body: String)

/** The built-in documentation. Works offline, so it is available even when the server is not. */
private val topics = listOf(
    HelpTopic(
        "Getting started",
        "REPENT† is a faith-based clothing store with daily devotionals, a prayer wall and a newsletter.\n\n" +
            "• Browse the Shop, read Devotionals and send a Prayer Request without an account.\n" +
            "• You need an account to place an order and to see your order history.\n" +
            "• Use the bottom bar to move between Home, Shop, Devotionals, Cart and Profile.",
    ),
    HelpTopic(
        "Creating an account and signing in",
        "Open Profile and tap Login / Create Account.\n\n" +
            "Email and password: enter your full name, email and a password of at least 8 characters with a letter and a number. " +
            "Your password is stored scrambled (bcrypt) — nobody, including the administrators, can read it.\n\n" +
            "Continue with Google: tap the Google button and pick your account. The same Google account also works on the REPENT† website.\n\n" +
            "Forgot your password? Sign in with Google if you used it before, or ask an administrator to reset it.",
    ),
    HelpTopic(
        "Shopping and checkout",
        "1. Open Shop and filter by T-Shirts, Hoodies or Caps. Tap a product for details.\n" +
            "2. Tap Add to Cart. Change quantities or remove items in the Cart tab.\n" +
            "3. Tap Checkout, enter your delivery details and choose EFT or card.\n" +
            "4. Tap Place Order. You will see your order number.\n\n" +
            "Shipping is free on orders of R800 or more, otherwise R95. Prices and stock are always confirmed by the server at checkout, " +
            "so if something sells out while it is in your cart you will be told before you are charged.",
    ),
    HelpTopic(
        "My orders and what the statuses mean",
        "Profile → My Orders lists everything you have bought.\n\n" +
            "• Pending – received, waiting to be confirmed\n" +
            "• Processing – being packed\n" +
            "• Shipped – on its way to you\n" +
            "• Delivered – arrived\n" +
            "• Cancelled – the order was cancelled and the stock returned",
    ),
    HelpTopic(
        "Devotionals, prayer requests and newsletter",
        "Devotionals: a verse, a reflection and a topic. Tap a card to read it.\n\n" +
            "Prayer Requests: share what is on your heart. Tick \"Keep this request private\" if only the prayer team should see it. Email is optional.\n\n" +
            "Newsletter: subscribe with your name and email and choose daily, weekly or monthly. Subscribing twice is safe.",
    ),
    HelpTopic(
        "Your profile and settings",
        "Profile → Settings lets you:\n\n" +
            "• Change your name, phone, address and the products you prefer — your delivery details are then used as a starting point.\n" +
            "• Change your password (enter the current one first). Changing it signs you out on your other devices.\n" +
            "• Set the server address and test the connection.\n\n" +
            "Google accounts have no REPENT† password, so the password section is hidden for them.",
    ),
    HelpTopic(
        "Problems connecting?",
        "If you see \"Can't reach the server\":\n\n" +
            "1. Make sure the device and the computer running the server are on the SAME Wi-Fi network.\n" +
            "2. Make sure the server is running (XAMPP: Apache and MySQL started).\n" +
            "3. Open Profile → Settings → Server connection, type the computer's address (for example 192.168.0.10:8000) and tap Test.\n" +
            "4. A green \"Connected\" message means the app, the API and the database are all working.\n\n" +
            "On the Android emulator the computer is reachable at 10.0.2.2.",
    ),
    HelpTopic(
        "Administrators",
        "Accounts with the admin role see an Admin Dashboard on the Profile screen: totals for orders, users, products, prayer requests, " +
            "subscribers and revenue, order management (change an order's status), prayer requests and newsletter subscribers. " +
            "The server checks the admin role on every request, so changing the app cannot grant admin access.",
    ),
    HelpTopic(
        "Your privacy and safety",
        "• Passwords are hashed with bcrypt and never sent back to the app.\n" +
            "• Your sign-in token is stored only on this device and is cancelled when you log out or change your password.\n" +
            "• Private prayer requests are visible to the prayer team only.\n" +
            "• Log Out is on the Profile screen.",
    ),
)

@Composable
fun HelpScreen() {
    Box(Modifier.fillMaxSize(), contentAlignment = Alignment.TopCenter) {
        LazyColumn(
            modifier = Modifier.widthIn(max = 720.dp).fillMaxWidth(),
            contentPadding = PaddingValues(20.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp),
        ) {
            item {
                SectionLabel("Help & Documentation")
                Spacer(Modifier.height(4.dp))
                Text("How to use REPENT†", style = MaterialTheme.typography.headlineMedium)
                Spacer(Modifier.height(4.dp))
                Text("Tap a topic to open it.", style = MaterialTheme.typography.bodyMedium)
                Spacer(Modifier.height(8.dp))
            }
            items(topics) { topic -> HelpCard(topic) }
        }
    }
}

@Composable
private fun HelpCard(topic: HelpTopic) {
    var expanded by remember { mutableStateOf(false) }
    Column(
        Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(6.dp))
            .background(RepentWhite)
            .clickable { expanded = !expanded }
            .padding(16.dp)
            .animateContentSize(),
    ) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Text(topic.title, style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.SemiBold, modifier = Modifier.weight(1f))
            Icon(
                if (expanded) Icons.Default.ExpandLess else Icons.Default.ExpandMore,
                contentDescription = if (expanded) "Collapse" else "Expand",
                tint = RepentGold,
            )
        }
        if (expanded) {
            Spacer(Modifier.height(10.dp))
            Text(topic.body, style = MaterialTheme.typography.bodyMedium)
        }
    }
}
