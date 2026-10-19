package com.repent.app.ui

import android.content.Context
import androidx.compose.runtime.Composable
import androidx.compose.ui.platform.LocalContext
import com.repent.app.RepentApp

/** Small helper so screens/ViewModels can grab the app-wide repositories. */
@Composable
fun rememberRepentApp(): RepentApp {
    val context = LocalContext.current
    return context.applicationContext as RepentApp
}

fun Context.repentApp(): RepentApp = applicationContext as RepentApp
