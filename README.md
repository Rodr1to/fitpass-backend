# Fitpass HOPn - Laravel Backend

This repository contains the Laravel backend and admin panel for the Fitpass HOPn wellness platform. It's built to manage companies, employees, membership plans, and partner facilities like gyms and spas.

This project is part of a larger system that will eventually include a Next.js frontend, but this repository is exclusively for the API and server-side logic.

## Project Status

**Sprint 1: Complete.**
* Core Laravel project setup.
* User authentication system with three roles (`employee`, `hr_admin`, `super_admin`).
* Full CRUD (Create, Read, Update, Delete) functionality for Membership Plans.
* Admin panel for managing plans, protected by role-based middleware.
* Database seeders for default plans and users.

---

## Development Environment

This project is configured for a **native macOS development environment** using Homebrew.

* **PHP Version:** 8.1+
* **Database:** MySQL
* **Package Manager:** Composer
* **Framework:** Laravel 11

---

## Local Setup Instructions

Follow these steps to get the project running on a new macOS machine.

### 1. Install Core Dependencies
If you don't have them already, install Homebrew, PHP, Composer, and MySQL.

```bash
# Install Homebrew (if you don't have it)
/bin/bash -c "$(curl -fsSL [https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh](https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh))"

# Install PHP, Composer, and MySQL
brew install php composer mysql

# Start the MySQL service and ensure it runs on login
brew services start mysql

```

### 2. Install Core Dependencies
Clone this repository and configure your local environment.

```bash
# Clone the repository from GitHub
git clone [https://github.com/Rodr1to/fitpass-hopn-laravel.git](https://github.com/Rodr1to/fitpass-hopn-laravel.git)

# Navigate into the project directory
cd fitpass-hopn-laravel

# Install all the required PHP packages
composer install

# Copy the example environment file
cp .env.example .env

# Generate a unique application key
php artisan key:generate
```

### 3. Database setup
You need to create the database and update your .env file to connect to it.

First, open the .env file in your code editor and ensure the database credentials are set correctly for a default Homebrew MySQL installation:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fitpass_hopn
DB_USERNAME=root
DB_PASSWORD=
```

Now, create the database and run the migrations with seed data.

```bash
# Log in to the MySQL client
mysql -u root

# Inside the MySQL prompt, create the database
CREATE DATABASE fitpass_hopn;
exit;

# Run the migrations to create all tables and populate them with test data
php artisan migrate:fresh --seed
```

### 4. Serve the Application
You're all set! Start the local development server.

```bash
php artisan serve
```

The application will now be available at http://127.0.0.1:8000.

---

## Key Features & Routes

### User Authentication
### * Login: 
```bash
http://127.0.0.1:8000/login
```

### * Register: 
```bash
http://127.0.0.1:8000/register
```

### * Dashboard: 
```bash
http://127.0.0.1:8000/dashboard
```

### Admin Panel
The admin panel is protected and can only be accessed by users with the hr_admin or super_admin role.

### * Admin Base URL: 
```bash
http://127.0.0.1:8000/admin
```

### * Membership Plan Management: 
```bash
http://127.0.0.1:8000/admin/plans
```

### * Add New Plan Form: 
```bash
http://127.0.0.1:8000/admin/plans/create
```

---

### Default user credentials

| role        | email                  | password |
|-------------|------------------------|----------|
| Super Admin | superadmin@fitpass.com | password |
| HR Admin    | hr@fitpass.com         | password |
| Employee    | employee@fitpass.com   | password |

