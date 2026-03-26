package com.naukaridarpan.ui.theme

import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.Font
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.sp

// ── Naukaridarpan Color Palette ───────────────────────────────────────────
val Saffron        = Color(0xFFE8650A)
val SaffronLight   = Color(0xFFFDF0E6)
val SaffronDark    = Color(0xFFC75508)
val Teal           = Color(0xFF0D5C63)
val TealLight      = Color(0xFFE8F4F5)
val TealDark       = Color(0xFF0A4950)
val Gold           = Color(0xFFD4A017)
val GoldLight      = Color(0xFFFBF4DC)
val Cream          = Color(0xFFFFFBF5)
val InkPrimary     = Color(0xFF2D3748)
val InkSecondary   = Color(0xFF4A5568)
val InkLight       = Color(0xFF718096)
val BorderColor    = Color(0xFFE8E0D5)
val SuccessGreen   = Color(0xFF276749)
val ErrorRed       = Color(0xFFC53030)

private val NaukaridarpanColorScheme = lightColorScheme(
    primary          = Saffron,
    onPrimary        = Color.White,
    primaryContainer = SaffronLight,
    onPrimaryContainer = SaffronDark,
    secondary        = Teal,
    onSecondary      = Color.White,
    secondaryContainer = TealLight,
    onSecondaryContainer = TealDark,
    tertiary         = Gold,
    onTertiary       = Color.White,
    background       = Cream,
    onBackground     = InkPrimary,
    surface          = Color.White,
    onSurface        = InkPrimary,
    surfaceVariant   = Color(0xFFF5F0EB),
    onSurfaceVariant = InkSecondary,
    outline          = BorderColor,
    error            = ErrorRed,
    onError          = Color.White,
)

val HindFontFamily = FontFamily.Default  // Use system default; in production add custom font files

val NaukaridarpanTypography = Typography(
    headlineLarge  = TextStyle(fontFamily = HindFontFamily, fontWeight = FontWeight.Normal, fontSize = 28.sp),
    headlineMedium = TextStyle(fontFamily = HindFontFamily, fontWeight = FontWeight.Normal, fontSize = 22.sp),
    headlineSmall  = TextStyle(fontFamily = HindFontFamily, fontWeight = FontWeight.Normal, fontSize = 18.sp),
    titleLarge     = TextStyle(fontFamily = HindFontFamily, fontWeight = FontWeight.SemiBold, fontSize = 18.sp),
    titleMedium    = TextStyle(fontFamily = HindFontFamily, fontWeight = FontWeight.SemiBold, fontSize = 15.sp),
    bodyLarge      = TextStyle(fontFamily = HindFontFamily, fontWeight = FontWeight.Normal, fontSize = 16.sp, lineHeight = 24.sp),
    bodyMedium     = TextStyle(fontFamily = HindFontFamily, fontWeight = FontWeight.Normal, fontSize = 14.sp, lineHeight = 20.sp),
    bodySmall      = TextStyle(fontFamily = HindFontFamily, fontWeight = FontWeight.Normal, fontSize = 12.sp),
    labelLarge     = TextStyle(fontFamily = HindFontFamily, fontWeight = FontWeight.SemiBold, fontSize = 14.sp),
    labelSmall     = TextStyle(fontFamily = HindFontFamily, fontWeight = FontWeight.Medium, fontSize = 11.sp),
)

@Composable
fun NaukaridarpanTheme(content: @Composable () -> Unit) {
    MaterialTheme(
        colorScheme = NaukaridarpanColorScheme,
        typography  = NaukaridarpanTypography,
        content     = content,
    )
}
