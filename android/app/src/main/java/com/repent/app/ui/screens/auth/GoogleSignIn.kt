package com.repent.app.ui.screens.auth

import android.content.Context
import androidx.credentials.CredentialManager
import androidx.credentials.CustomCredential
import androidx.credentials.GetCredentialRequest
import androidx.credentials.exceptions.GetCredentialCancellationException
import androidx.credentials.exceptions.GetCredentialException
import androidx.credentials.exceptions.NoCredentialException
import com.google.android.libraries.identity.googleid.GetGoogleIdOption
import com.google.android.libraries.identity.googleid.GoogleIdTokenCredential
import com.google.android.libraries.identity.googleid.GoogleIdTokenParsingException
import kotlinx.coroutines.CancellationException

sealed class GoogleResult {
    data class Token(val idToken: String) : GoogleResult()
    data class Failed(val message: String) : GoogleResult()
    data object Cancelled : GoogleResult()
}

/**
 * Asks Android (Credential Manager) to let the person pick a Google account and returns a Google ID token.
 * The app never sees the Google password. The token is then sent to our own API (api/google_login.php),
 * which verifies it with Google, so the same Google account works on the website too.
 *
 * [webClientId] is the "Web application" OAuth client ID from Google Cloud Console (see README).
 * [context] must be an Activity context (LocalContext.current inside MainActivity is).
 */
suspend fun requestGoogleIdToken(context: Context, webClientId: String): GoogleResult {
    if (webClientId.isBlank()) {
        return GoogleResult.Failed("Google sign-in isn't set up in this build yet. Add GOOGLE_WEB_CLIENT_ID to local.properties (see the README), or sign in with your email and password.")
    }
    return try {
        val option = GetGoogleIdOption.Builder()
            .setServerClientId(webClientId)
            .setFilterByAuthorizedAccounts(false)   // show every Google account on the device, not only ones used before
            .setAutoSelectEnabled(false)
            .build()
        val request = GetCredentialRequest.Builder().addCredentialOption(option).build()
        val result = CredentialManager.create(context).getCredential(context, request)
        val credential = result.credential
        if (credential is CustomCredential && credential.type == GoogleIdTokenCredential.TYPE_GOOGLE_ID_TOKEN_CREDENTIAL) {
            GoogleResult.Token(GoogleIdTokenCredential.createFrom(credential.data).idToken)
        } else {
            GoogleResult.Failed("Google sign-in returned something unexpected. Please try again.")
        }
    } catch (e: GetCredentialCancellationException) {
        GoogleResult.Cancelled
    } catch (e: NoCredentialException) {
        GoogleResult.Failed("No Google account was found on this device. Add one under Settings > Accounts, then try again.")
    } catch (e: GetCredentialException) {
        GoogleResult.Failed("Google sign-in didn't work (${e.type}). Check that this device has Google Play services and the app's SHA-1 is registered (see the README).")
    } catch (e: GoogleIdTokenParsingException) {
        GoogleResult.Failed("Google sign-in returned an invalid token. Please try again.")
    } catch (e: CancellationException) {
        throw e
    } catch (e: Exception) {
        GoogleResult.Failed("Google sign-in failed. Please try again.")
    }
}
