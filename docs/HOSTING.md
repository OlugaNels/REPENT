# Putting REPENT† online (free): website + API on Render, database on Aiven

Task 2 asks the video to show what is stored in the **online-hosted** authentication service, API and database.
REPENT† runs locally on XAMPP, so this guide moves it online. Everything below is free, but you must create the
accounts yourself (the free plans and limits change, so check each pricing page before you start).

```
Phone app  --HTTPS-->  Render (Docker: PHP + Apache)  --TLS-->  Aiven MySQL
                         website + /api/*                         repent_db (7 tables)
Google Cloud (OAuth)  <--  ID-token check  <--  api/google_login.php
```

| Part | Where it lives | What the video shows |
|---|---|---|
| Website + API | Render web service `repent` (built from `Dockerfile`) | `https://<your-service>.onrender.com/` and `/api/health.php` |
| Database | Aiven for MySQL, database `repent_db` | Tables and rows in a SQL client (DBeaver / MySQL Workbench) |
| Authentication | `users` table (bcrypt hashes) + Google Sign-In | The `users` rows (hashed passwords, `auth_provider`) and the Google Cloud OAuth client page |

> Render free web services sleep after 15 minutes without traffic and take about a minute to wake up.
> **Open `/api/health.php` in a browser a minute before you record or demonstrate.**

## 1. Database on Aiven
1. Sign up at https://aiven.io and choose **Create service > MySQL > Free plan**.
2. When it is running, copy **Host, Port, User (avnadmin), Password** from the service overview page.
3. Load the data. In DBeaver (or MySQL Workbench) create a connection with those details and **SSL on**, then run `repent_database.sql`
   (it creates `repent_db` and the 7 tables with 12+ rows each). Command-line alternative:
   `mysql -h HOST -P PORT -u avnadmin -p --ssl-mode=REQUIRED < repent_database.sql`
4. Check: `SELECT COUNT(*) FROM products;` returns 12.

## 2. Website + API on Render
1. Push this repository to GitHub (it is already at https://github.com/OlugaNels/REPENT).
2. https://render.com > **New > Blueprint** > pick the repo. Render reads `render.yaml`. Plan: **Free**.
3. Fill the variables Render asks for: `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS` (from Aiven) and `GOOGLE_WEB_CLIENT_ID`
   (from Google Cloud, README section 4). `DB_SSL=1` and `DB_NAME=repent_db` are already set; `REPENT_APP_SECRET` is generated for you.
4. When the deploy finishes open `https://<your-service>.onrender.com/api/health.php`. You should see `"status":"ok","products":12`.
5. In Google Cloud > Credentials > the **Web application** client, add `https://<your-service>.onrender.com` to *Authorized JavaScript origins*
   so Google sign-in works on the hosted website.

## 3. Point the app at the online server
- Quick: install the existing APK, then **Profile > Settings > Server connection**, enter `https://<your-service>.onrender.com/`, tap **Test**
  (it must say "Connected ... 12 products found").
- Permanent: set `BASE_URL=https://<your-service>.onrender.com/` in `android/local.properties`, or as a repository variable for CI, and rebuild.

## 4. What to show in the video (marker checklist)
1. Browser: website and `/api/health.php` on the onrender.com address (proves the API is online).
2. Phone: register, log in, Google sign-in, shop, checkout, change settings and password, help screens, admin dashboard.
3. SQL client connected to **Aiven**: `users` (bcrypt hashes, a Google row), `orders` and `order_items` (the new order), all 7 tables with 10+ rows.
4. Google Cloud console: the OAuth client used for sign-in.
5. GitHub: the repository, the green **Actions** runs, and the Azure DevOps board.

## Troubleshooting
| Symptom | Fix |
|---|---|
| Health page shows `The database is not available` | `DB_HOST/PORT/USER/PASS` wrong, or Aiven service is powered off. Check Render > Environment and Aiven status. |
| First request is very slow | Render woke the sleeping service. Wait about a minute and retry. |
| App says "Can't reach the server" | The address needs `https://` and a trailing `/`. Run **Test** in Settings. |
