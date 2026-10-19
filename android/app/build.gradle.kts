import java.util.Properties

plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

// Settings that differ per machine live in android/local.properties (never committed to git):
//   BASE_URL=http://192.168.0.10:8000/
//   GOOGLE_WEB_CLIENT_ID=1234567890-abc.apps.googleusercontent.com
// They can also be given as environment variables (used by the CI pipeline).
val localProps = Properties().apply {
    val f = rootProject.file("local.properties")
    if (f.exists()) f.inputStream().use { load(it) }
}
fun setting(name: String, default: String): String =
    (localProps.getProperty(name) ?: System.getenv(name) ?: default).trim().replace("\\", "").replace("\"", "")

android {
    namespace = "com.repent.app"
    compileSdk = 34

    defaultConfig {
        applicationId = "com.repent.app"
        minSdk = 24
        targetSdk = 34
        versionCode = 1
        versionName = "1.0"

        // Default server address. It can also be changed inside the app (Profile > Settings),
        // so a wrong value here never needs a rebuild. 10.0.2.2 = the computer, as seen from the emulator.
        buildConfigField("String", "BASE_URL", "\"${setting("BASE_URL", "http://10.0.2.2:8000/")}\"")

        // The "Web application" OAuth client ID from Google Cloud Console (see README, "Google sign-in").
        // Empty = the Google button explains that it is not set up yet instead of crashing.
        buildConfigField("String", "GOOGLE_WEB_CLIENT_ID", "\"${setting("GOOGLE_WEB_CLIENT_ID", "")}\"")
    }

    buildTypes {
        release {
            isMinifyEnabled = false
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
        }
    }

    buildFeatures {
        compose = true
        buildConfig = true
    }

    composeOptions {
        kotlinCompilerExtensionVersion = "1.5.14"
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }
    kotlinOptions {
        jvmTarget = "17"
    }

    testOptions {
        unitTests.isReturnDefaultValues = true
    }

    packaging {
        resources {
            excludes += "/META-INF/{AL2.0,LGPL2.1}"
        }
    }
}

dependencies {
    val composeBom = platform("androidx.compose:compose-bom:2024.06.00")
    implementation(composeBom)
    androidTestImplementation(composeBom)

    implementation("androidx.core:core-ktx:1.13.1")
    implementation("androidx.lifecycle:lifecycle-runtime-ktx:2.8.4")
    implementation("androidx.lifecycle:lifecycle-viewmodel-compose:2.8.4")
    implementation("androidx.activity:activity-compose:1.9.1")

    implementation("androidx.compose.ui:ui")
    implementation("androidx.compose.ui:ui-graphics")
    implementation("androidx.compose.ui:ui-tooling-preview")
    implementation("androidx.compose.material3:material3")
    implementation("androidx.compose.material:material-icons-extended")

    implementation("androidx.navigation:navigation-compose:2.7.7")

    // Sign in with Google (Credential Manager)
    implementation("androidx.credentials:credentials:1.3.0")
    implementation("androidx.credentials:credentials-play-services-auth:1.3.0")
    implementation("com.google.android.libraries.identity.googleid:googleid:1.1.1")

    // Networking
    implementation("com.squareup.retrofit2:retrofit:2.11.0")
    implementation("com.squareup.retrofit2:converter-gson:2.11.0")
    implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")

    // Local token / cart persistence
    implementation("androidx.datastore:datastore-preferences:1.1.1")

    // Image loading
    implementation("io.coil-kt:coil-compose:2.6.0")

    // Coroutines
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-android:1.8.1")

    debugImplementation("androidx.compose.ui:ui-tooling")
    debugImplementation("androidx.compose.ui:ui-test-manifest")

    testImplementation("junit:junit:4.13.2")
    testImplementation("com.google.code.gson:gson:2.10.1")
    androidTestImplementation("androidx.test.ext:junit:1.2.1")
    androidTestImplementation("androidx.test.espresso:espresso-core:3.6.1")
}
