# Laravel Vue Data Table

A Laravel 12 application with Vue 3, Inertia.js, and Tailwind CSS featuring user management with DataTable functionality.

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- npm

## Installation

### Quick Setup

```bash
# Clone the repository
git clone <repository-url>
cd lara-vue-data

# Run the setup script (installs dependencies, creates .env, generates key, runs migrations, builds assets)
composer setup
```

### Manual Setup

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database and run migrations
touch database/database.sqlite
php artisan migrate

# Install Node dependencies
npm install

# Build frontend assets
npm run build
```

## Running the Application

### Development Mode

```bash
composer dev
```

This starts all services concurrently:
- Laravel development server (http://localhost:8000)
- Queue worker
- Log viewer (Pail)
- Vite dev server with HMR

### Production Mode

```bash
npm run build
php artisan serve
```

## Default Login

After running migrations, you can register a new account at `/register`.

## Features

- User authentication (login, register, password reset)
- User management CRUD with DataTable
- Sortable and searchable data tables
- Responsive design with Tailwind CSS

## Tech Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Vue 3, Inertia.js, TypeScript
- **Styling:** Tailwind CSS 4
- **Build Tool:** Vite
- **Database:** SQLite (default)

## Testing

```bash
composer test
```
