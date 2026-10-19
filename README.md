# REPENT† — website + Android app + API + database

[![CI](https://github.com/OlugaNels/REPENT/actions/workflows/ci.yml/badge.svg)](https://github.com/OlugaNels/REPENT/actions/workflows/ci.yml)
![Kotlin](https://img.shields.io/badge/Kotlin-7F52FF?style=flat-square&logo=kotlin&logoColor=white)
![Jetpack Compose](https://img.shields.io/badge/Jetpack%20Compose-4285F4?style=flat-square&logo=jetpackcompose&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=flat-square&logo=docker&logoColor=white)

A shop with an Android app, a website and a PHP REST API on one shared database. Customers can browse products, use a cart, check out and see their orders, with devotional, prayer and newsletter features alongside. Sign-in works with email and password (bcrypt) or Google, on both the app and the website. The repo includes Docker and Render hosting files (see `docs/HOSTING.md`) and automated tests.

One repository, four parts that share **one MySQL database**:

| Folder / file | What it is |
|---|---|
| `repent_database.sql` | MySQL database: 7 related tables, 12+ sample rows in each |
| `api/` | PHP REST API used by the Android app |
| `pages/`, `includes/`, `index.php` | The website (same database, same Google sign-in) |
| `android/` | The native Kotlin / Jetpack Compose app |
| `tests/` | API + database tests |
| `.github/workflows/ci.yml` | GitHub Actions pipeline (builds the app on every push) |
| `docs/KANBAN.md` | Kanban board cards for Step 3 |

---

## What to do (in order)

### 1. Put the website + API on your laptop (XAMPP)
1. Install **XAMPP**, open the XAMPP Control Panel and **Start Apache and MySQL**.
2. Copy this whole folder to `C:\xampp\htdocs\` and name it **`Repent`** (so you have `C:\xampp\htdocs\Repent\api\...`).
3. Open http://localhost/phpmyadmin → **Import** → choose `repent_database.sql` → **Go**. This creates the database `repent_db`.
4. Check it works: open http://localhost/Repent/api/health.php — you should see `"status":"ok","products":12`.
   The website is at http://localhost/Repent/ .

### 2. Find your laptop's address
Open Command Prompt, type `ipconfig`, and note the **IPv4 Address** (for example `192.168.0.10`).
The tablet and the laptop must be on the **same Wi-Fi**. If the tablet can't connect, allow Apache through the Windows Firewall
(Windows Security → Firewall → Allow an app → *Apache HTTP Server*, tick **Private**).

### 3. Build the app
1. Install **Android Studio**, then choose **Open** and select the `android` folder. Wait for Gradle sync to finish.
2. In the `android` folder copy `local.properties.example` to `local.properties` and set
   `BASE_URL=http://192.168.0.10/Repent/` (your IP from step 2). Leave Google blank for now.
3. Put the tablet in **Developer options → USB debugging**, plug it in, press the green **Run ▶**.
   (Or build an APK: **Build → Build APK(s)**, copy `app-debug.apk` from `android/app/build/outputs/apk/debug/` to the tablet and install it.)
4. In the app open **Profile → Settings → Server connection**, tap **Test**. "Connected … 12 products found" proves app → API → database works.
   You can change the address here at any time — no rebuild needed.

### 4. Google sign-in (needed for the SSO requirement)
1. https://console.cloud.google.com → create a project → **APIs & Services → OAuth consent screen** (External, add your Google account as a test user).
2. **Credentials → Create credentials → OAuth client ID**, make **two**:
   - **Web application**, with *Authorized JavaScript origins* `http://localhost` → copy its **Client ID**.
   - **Android**, package name `com.repent.app`, SHA-1 from Android Studio: Gradle panel → *app → Tasks → android → signingReport* (the **debug** SHA-1).
3. Paste the Web client ID in **two** places:
   - `android/local.properties` → `GOOGLE_WEB_CLIENT_ID=...` (then rebuild/run the app)
   - copy `includes/config.local.example.php` to `includes/config.local.php` and uncomment/fill `define('GOOGLE_WEB_CLIENT_ID', '...');`
4. Test: sign in with Google in the app, then open http://localhost/Repent/pages/login.php and click the Google button with the same account.
   It is the **same user** (one row in the `users` table), which is the "SSO across app and website" requirement.
   The tablet needs a Google account added and Google Play services.

### 5. Show the markers (suggested demo, ~5 minutes)
1. Register an account in the app (try a weak password first to show validation), then log in.
2. Shop → add to cart → checkout → place order.
3. On the laptop open phpMyAdmin → `repent_db` → `orders` and `order_items`: the new order is there. Show that `users.password` is a bcrypt hash, not text.
4. In the app: Profile → Settings → change a detail and change the password; show the new details in phpMyAdmin.
5. Show **Help & Documentation**, Google sign-in, and log in as admin (`admin@repent.co.za` / `admin123`) for the Admin Dashboard.
6. Show GitHub → Actions (green build) and your Kanban board.

**Sample logins:** `admin@repent.co.za / admin123` (admin), `user@repent.co.za / user123`, all others `Demo@1234`.

### 6. GitHub (Step 3)
Create/commit to your repo; every push runs `.github/workflows/ci.yml`: Android unit tests + APK build, and API tests against a real MySQL.
Create the Kanban board from `docs/KANBAN.md`. Download the APK from the finished run's **Artifacts** if you want.

---

## Requirement checklist

| Brief | Where |
|---|---|
| Kotlin app, register/login, passwords encrypted | `android/…/auth`, `api/register.php`, `api/login.php` (bcrypt) |
| SSO Google, works on app **and** website | `api/google_login.php`, `pages/google_login.php`, `includes/google.php` (one `users` table) |
| Profile page: securely change password + personal choices | Android **Settings**, `api/me.php`, `api/change_password.php` (current password required; other devices signed out) |
| No freezing/crashing on wrong input | `Validators.kt` (client) + `api/config.php` (server); every request via `apiCall`, errors → friendly messages |
| Relational DB, correct types, 10+ rows/table | `repent_database.sql` (7 tables, foreign keys, DECIMAL/ENUM/DATETIME) |
| Verified data load/save app ↔ API ↔ DB | Settings → Test; `tests/api_test.php`; `tests/db_integrity_test.php` |
| KANBAN | `docs/KANBAN.md` |
| Pipeline builds the app on every push | `.github/workflows/ci.yml` |
| Automated unit tests | `android/app/src/test` (53 tests), `tests/` (105 API + 17 DB checks) |
| Help page in the app | Profile → **Help & Documentation** |

## Running the tests yourself
- Android: `cd android && ./gradlew testDebugUnitTest`
- API (needs only PHP, no MySQL): `tests/run_api_tests.sh`
- API against MySQL: `php tests/db_integrity_test.php` then `php tests/api_test.php http://localhost/Repent`

## Notes
- Two bugs from the original site were fixed: the seed passwords for `admin@repent.co.za`/`user@repent.co.za` did not match `admin123`/`user123`, and `pages/checkout.php` wrote to
  columns that did not exist (it now matches the new schema).
- Payments are not processed (orders are recorded only), and Google sign-in needs your own Google Cloud client IDs.
