package com.repent.app.ui.theme

import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable

private val RepentColorScheme = lightColorScheme(
    primary = RepentBlack,
    onPrimary = RepentWhite,
    secondary = RepentGold,
    onSecondary = RepentBlack,
    tertiary = RepentGoldLight,
    background = RepentOffWhite,
    onBackground = RepentBlack,
    surface = RepentWhite,
    onSurface = RepentBlack,
    surfaceVariant = RepentCream,
    onSurfaceVariant = RepentGray,
    error = RepentRed,
    outline = RepentCream,
)

@Composable
fun RepentTheme(content: @Composable () -> Unit) {
    MaterialTheme(
        colorScheme = RepentColorScheme,
        typography = RepentTypography,
        content = content,
    )
}
