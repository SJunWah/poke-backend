# # Poke Backend

A modern Laravel application for managing and displaying Pokemon data with a robust tech stack.

## Prerequisites

* PHP 8.1 or higher
* Composer
* Apache web server
* MySQL 5.7 or higher

## Setup Instructions

### 1. Install Dependencies

Install all required PHP packages using Composer:

```bash
composer install
```

### 2. Environment Configuration

1. Copy the example environment file:
   ```bash
   copy .env.example .env
   ```

2. Generate application key:
   ```bash
   php artisan key:generate
   ```

3. Configure your database connection in the `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pokemon
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Database Setup

Create a new database with proper character encoding:

```sql
CREATE DATABASE pokemon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Run Database Migrations

Execute migrations to create all necessary tables:

```bash
php artisan migrate
```

### 5. Populate Pokemon Data

Run the Pokemon scanner command to import Pokemon data:

```bash
php artisan process:pokemon
```

## Running the Application

Start the Laravel development server:

```bash
php artisan serve --port=10000
```

The application will be accessible at: `http://localhost:10000`

## Useful Commands

```bash
# Clear application caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run the test suite
php artisan test

# Generate a new migration
php artisan make:migration create_table_name

# Generate a model with migration
php artisan make:model ModelName -m

# Generate a controller
php artisan make:controller ControllerName

# Run database seeders
php artisan db:seed
```

## Troubleshooting

* **Port already in use**: Change the port number: `php artisan serve --port=8080`
* **Database connection errors**: Verify your `.env` credentials match your MySQL configuration
* **Permission issues**: Ensure `storage` and `bootstrap/cache` directories are writable
