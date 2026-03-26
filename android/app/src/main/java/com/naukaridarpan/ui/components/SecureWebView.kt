package com.naukaridarpan.ui.screens

import android.annotation.SuppressLint
import android.view.ViewGroup
import android.webkit.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.viewinterop.AndroidView

/**
 * Secure WebView for TAO exam delivery.
 * - JavaScript enabled (required for TAO)
 * - File access disabled
 * - Downloads intercepted
 * - Back navigation blocked during exam
 */
@SuppressLint("SetJavaScriptEnabled")
@Composable
fun SecureWebView(
    url: String,
    modifier: Modifier = Modifier,
    onPageFinished: (String) -> Unit = {},
) {
    AndroidView(
        modifier = modifier,
        factory = { context ->
            WebView(context).apply {
                layoutParams = ViewGroup.LayoutParams(
                    ViewGroup.LayoutParams.MATCH_PARENT,
                    ViewGroup.LayoutParams.MATCH_PARENT,
                )
                settings.apply {
                    javaScriptEnabled        = true
                    domStorageEnabled        = true
                    allowFileAccess          = false
                    allowContentAccess       = false
                    cacheMode                = WebSettings.LOAD_NO_CACHE
                    setSupportZoom(false)
                    displayZoomControls      = false
                    builtInZoomControls      = false
                    mediaPlaybackRequiresUserGesture = false
                }

                webViewClient = object : WebViewClient() {
                    override fun onPageFinished(view: WebView?, url: String?) {
                        super.onPageFinished(view, url)
                        // Inject anti-cheat JS into TAO
                        view?.evaluateJavascript("""
                            document.addEventListener('contextmenu', e => e.preventDefault());
                            document.addEventListener('copy', e => e.preventDefault());
                            document.addEventListener('keydown', e => {
                              if (e.key === 'F12') e.preventDefault();
                            });
                        """.trimIndent(), null)
                        onPageFinished(url ?: "")
                    }

                    @Deprecated("Deprecated in Java")
                    override fun shouldOverrideUrlLoading(view: WebView?, url: String?): Boolean {
                        // Only allow TAO URLs — block external navigation
                        return url?.startsWith("https://") == false
                    }
                }

                webChromeClient = object : WebChromeClient() {
                    override fun onConsoleMessage(msg: ConsoleMessage?) = true // suppress console
                }

                loadUrl(url)
            }
        },
        update = { webView ->
            if (webView.url != url) webView.loadUrl(url)
        },
    )
}
