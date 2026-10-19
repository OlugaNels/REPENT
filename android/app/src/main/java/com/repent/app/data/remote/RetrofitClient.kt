package com.repent.app.data.remote

import com.repent.app.BuildConfig
import okhttp3.HttpUrl.Companion.toHttpUrlOrNull
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

object RetrofitClient {

    /** Retrofit needs a fixed base URL; the interceptor below swaps in the real (changeable) one for every call. */
    private const val PLACEHOLDER_BASE = "http://localhost/"

    private val dynamicBaseUrl = Interceptor { chain ->
        val request = chain.request()
        val real = ServerConfig.baseUrl.toHttpUrlOrNull()
        if (real == null) {
            chain.proceed(request)
        } else {
            val url = request.url.newBuilder()
                .scheme(real.scheme)
                .host(real.host)
                .port(real.port)
                .encodedPath(real.encodedPath.trimEnd('/') + request.url.encodedPath)
                .build()
            chain.proceed(request.newBuilder().url(url).build())
        }
    }

    private val unauthorizedWatcher = Interceptor { chain ->
        val request = chain.request()
        val response = chain.proceed(request)
        if (response.code == 401 && request.header("Authorization") != null) {
            ServerConfig.onUnauthorized?.invoke()
        }
        response
    }

    // BASIC logs only "POST /api/login.php 200"; BODY would print passwords and tokens into logcat.
    private val logging = HttpLoggingInterceptor().apply {
        level = if (BuildConfig.DEBUG) HttpLoggingInterceptor.Level.BASIC else HttpLoggingInterceptor.Level.NONE
    }

    private val okHttpClient = OkHttpClient.Builder()
        .addInterceptor(dynamicBaseUrl)
        .addInterceptor(unauthorizedWatcher)
        .addInterceptor(logging)
        .connectTimeout(15, TimeUnit.SECONDS)
        .readTimeout(20, TimeUnit.SECONDS)
        .writeTimeout(20, TimeUnit.SECONDS)
        .build()

    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(PLACEHOLDER_BASE)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }

    /** Helper to build the "Bearer <token>" header value. */
    fun bearer(token: String) = "Bearer $token"
}
