# Quizzone (Local Development)

This document explains how to run and test the Quizzone application locally using **Laravel Sail** (Docker). It includes steps to install dependencies, configure the environment, run real-time services (Reverb), and execute the test suite.

## Requirements

- Docker and Docker Compose (Desktop or Engine)
- Git

*Note: PHP and Composer are NOT required locally as they run inside Docker, but having them helps.*

**Tech Stack:**
- PHP 8.4
- Laravel 11
- MySQL 8.0
- Vue.js + Inertia
- Laravel Reverb (WebSockets)
- Laravel Nova (Admin Panel)

## 🚀 Quick Setup

### 1. Clone the repository

```bash
git clone <repo-url> quizzone-vue
cd quizzone-vue
```

### 2. Install Dependencies
If you have PHP/Composer locally:
```bash
composer install
```

**If you DON'T have PHP locally**, use this Docker command to install dependencies via a temporary container:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### 3. Environment Configuration
Copy the example file:
```bash
cp .env.example .env
```

Ensure these critical settings in `.env`:

```ini
APP_URL=http://localhost

# Database (Sail Defaults)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=quizzone
DB_USERNAME=sail
DB_PASSWORD=password

# Reverb (Real-time)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

# Nova License (Add yours if not present)
NOVA_LICENSE_KEY=...
```

### 4. Laravel Nova Authentication
Since this project uses Laravel Nova, you need to authorize Composer.
Create a file named `auth.json` in the root project folder (it is ignored by git):

```json
{
    "http-basic": {
        "nova.laravel.com": {
            "username": "YOUR_NOVA_EMAIL",
            "password": "YOUR_NOVA_LICENSE_KEY"
        }
    }
}
```

### 5. Start the Application
Start the containers in the background:

```bash
./vendor/bin/sail up -d
```

### 6. Finalize Setup
Run these commands inside the running container:

```bash
# Install Node dependencies & Build assets
./vendor/bin/sail npm install
./vendor/bin/sail npm run build

# Setup Database
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --force
./vendor/bin/sail artisan db:seed --force

# Publish Nova Assets (if UI looks broken)
./vendor/bin/sail artisan nova:publish
```

---

## 🎮 Accessing the App

- **Frontend (Game):** [http://localhost](http://localhost)
- **Backoffice (Nova):** [http://localhost/nova](http://localhost/nova)

**Default Credentials (from Seeder):**
- **Admin:** `c@beliven.com` / `password`
- **Player1:** `p1@beliven.com` / `password`
- **Player2:** `p2@beliven.com` / `password`

---

## 📡 Reverb (WebSockets)

For real-time features (Buzzers, Timer sync) to work, the Reverb server must be running.
Run this command in a separate terminal window:

```bash
./vendor/bin/sail artisan reverb:start
```
*Note: If you configured Supervisor in Sail, this might run automatically, but manual start is recommended for debugging.*

---

## 🧪 Running Tests & Quality Tools

We use **Pest** for testing and **Rector** for code quality.

```bash
# Run all tests
./vendor/bin/sail artisan test

# Run specific test file
./vendor/bin/sail artisan test tests/Feature/QuizServiceTest.php

```

---

## 🛠 Troubleshooting

**1. Nova 403 Forbidden:**
If you can't access `/nova`, ensure you are logged in as a user with `role: 'admin'`. Players are blocked from the backoffice.

**2. Timer not syncing / Buzzer not working:**
Check the browser console. If you see WebSocket connection errors, ensure `reverb:start` is running and `REVERB_HOST` is set to `localhost` in `.env`.

**3. "Vite manifest not found":**
You forgot to build the frontend. Run `./vendor/bin/sail npm run build`.

**4. Permission denied on storage/logs:**
Run: `sudo chown -R $USER:$USER .`