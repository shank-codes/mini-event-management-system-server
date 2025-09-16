# Backend Server Setup

This backend server uses Laravel with a PostgreSQL database.

## Prerequisites

- PHP (compatible version for Laravel)
- Composer (PHP package manager)
- PostgreSQL

## Setup Instructions

### 1. Setup PostgreSQL Database

Create a PostgreSQL database named `eventdb`:
```
CREATE DATABASE eventdb;
```

Set the database timezone to UTC if not set by default:
```
ALTER DATABASE eventdb SET timezone TO 'UTC';
```

### 2. Update Environment Variables

Copy the `.env.example` file to `.env` and update the database connection settings:

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=eventdb
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password


### 3. Install PHP Dependencies

Run the following to install PHP packages required by the project:
```
composer install
```

### 4. Run Migrations

Run database migrations to create the necessary tables:
```
php artisan migrate
```

### 5. Start the Server

Start the Laravel development server:
```
php artisan serve
```
By default, the server will be accessible at [http://localhost:8000](http://localhost:8000).

### 6. API Documentation

The Swagger API documentation is available at the following URL once the server is running:
```
http://localhost:8000/api/documentation
```


---

This setup ensures your database operates in UTC timezone and your Laravel backend is ready for development.

