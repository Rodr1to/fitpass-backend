# Fitpass HOPn - Laravel Backend

This repository contains the Laravel backend and admin panel for the Fitpass HOPn wellness platform. It's built to manage companies, employees, membership plans, and partner facilities like gyms and spas.

This project is part of a larger system that will eventually include a Next.js frontend, but this repository is exclusively for the API and server-side logic.

## Project Status

**Stage 1 (Standalone backend environment & architecture setup): Complete.**
**Stage 2 (Database design & schema implementation): Complete.**
* **Environment:** Full local development environment setup for macOS.
* **Authentication:** Robust user authentication and role-based access control (`employee`, `hr_admin`, `super_admin`).
* **Admin Panel:**
    * Full CRUD (Create, Read, Update, Delete) for **Membership Plans**.
    * Full CRUD for **Companies**.
* **Database:** Complete schema for `users`, `companies`, `membership_plans`, `partners`, `trainers`, `classes`, and `bookings` is migrated.
* **Testing:** Database seeders for default users, plans, and partners are in place.

---

## Development Environment

This project is configured for a **native macOS development environment** using Homebrew.

* **PHP Version:** 8.2+
* **Database:** MySQL
* **Package Manager:** Composer
* **Framework:** Laravel 11

---

## Local Setup Instructions

Follow these steps to get the project running on a new macOS machine.

## 1. Install Core Dependencies
If you don't have them already, install Homebrew, PHP, Composer, and MySQL via the terminal.

```bash
# Install Homebrew (macOS Package Manager)
/bin/bash -c "$(curl -fsSL [https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh](https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh))"

# Install PHP, Composer, and MySQL
brew install php composer mysql

# Start the MySQL service and ensure it runs on login
brew services start mysql

```

## 2. Clone & prepare the project
Clone this repository and configure your local environment.

```bash
# Clone the repository from GitHub
git clone [https://github.com/Rodr1to/fitpass-backend.git](https://github.com/Rodr1to/fitpass-backend.git)

# Navigate into the project directory
cd fitpass-backend

# Install all the required PHP packages
composer install

# Install all JavaScript dependencies (for Vite, used for the admin panel)
npm install

# Copy the environment file template
# IMPORTANT: This project does not commit a .env.example file. You must create it.
touch .env
```

### Now, open the new .env file and paste the contents from the template provided in this README.

```bash
APP_NAME="FitPass HOPn API"
APP_ENV=local
APP_DEBUG=true
APP_URL=[http://127.0.0.1:8000](http://127.0.0.1:8000)
APP_KEY=

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fitpass_hopn
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=log
CACHE_STORE=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# --- API & Frontend Communication Settings ---
FRONTEND_URLS=http://localhost:5173,[https://new-gym-dusky.vercel.app](https://new-gym-dusky.vercel.app)
SANCTUM_STATEFUL_DOMAINS=localhost:5173,new-gym-dusky.vercel.app

```

### Generate a unique application key
```bash
php artisan key:generate
```

## 3. Database setup
Create the database if it doesn't exist

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS fitpass_hopn;"
```

Run the migrations to create all tables and populate them with test data

```bash
php artisan migrate:fresh --seed
```

## 4. Serve the Application
You're all set! To run the application, you need to start both the Vite server and the PHP server in two separate terminal windows, both inside your fitpass-backend directory.

In terminal 1
```bash
npm run dev
```
In terminal 2
```bash
php artisan serve
```

The application will now be available at http://127.0.0.1:8000.

---

## Key Features & Routes

### User Authentication
### * Login: 
```bash
GET /login: Login Page

GET /register: Registration Page
```

### * Dashboard: 
```bash
GET /dashboard: Main dashboard after login.
```

### Admin Panel
The admin panel is protected and can only be accessed by users with the hr_admin or super_admin role.



```bash
GET /admin/plans: View all membership plans.

GET /admin/plans/create: Show form to add a new plan.

GET /admin/companies: View all companies.

GET /admin/companies/create: Show form to add a new company.
```

### API Routes (JSON for frontend)

Public API (Version 1.0):
```bash
GET /api/v1/membership-plans: Get a list of all active membership plans.

GET /api/v1/partners: Get a paginated list of all approved partners. Can be filtered with ?city= and ?type=.
```

---

## Default user credentials

| role        | email                  | password |
|-------------|------------------------|----------|
| Super Admin | superadmin@fitpass.com | password |
| HR Admin    | hr@fitpass.com         | password |
| Employee    | employee@fitpass.com   | password |

