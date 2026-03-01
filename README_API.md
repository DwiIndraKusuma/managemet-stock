# Inventory & Procurement Management System API

## Overview
Sistem Inventory & Supply Chain Management berbasis API untuk mengelola proses pengadaan dan pengelolaan stok secara terintegrasi.

## Requirements

Sebelum memulai instalasi, pastikan komputer Anda sudah terinstall:

- **PHP**  v8.5.0
- **Composer** (PHP dependency manager)
- **Node.js** >= 18.x dan **npm**
- **Web Server** (Apache/Nginx) atau gunakan Laravel built-in server
- **Git** (untuk clone repository)

### Recommended Development Environment:
- **Laragon** (Windows) - All-in-one package
- **XAMPP** (Windows/Mac/Linux)
- **Laravel Valet** (Mac)
- **Laravel Homestead** (Virtual Machine)

## Installation Steps

### Step 1: Clone Repository

```bash
git clone <repository-url>
cd erp
```

Atau jika sudah memiliki project, pastikan Anda berada di directory project.

### Step 2: Install PHP Dependencies

```bash
composer install
```

Ini akan menginstall semua PHP dependencies yang diperlukan termasuk Laravel framework dan Laravel Sanctum.

### Step 3: Install Node.js Dependencies

```bash
npm install
```

Ini akan menginstall dependencies untuk frontend (Vite, Tailwind CSS, Axios).

### Step 4: Setup Environment File

Copy file `.env.example` menjadi `.env`:

```bash
# Windows
copy .env.example .env

# Linux/Mac
cp .env.example .env
```

### Step 5: Generate Application Key

```bash
php artisan key:generate
```

Ini akan menghasilkan `APP_KEY` yang unik untuk aplikasi Anda.

### Step 6: Configure Database

Buka file `.env` dan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_inventory
DB_USERNAME=root
DB_PASSWORD=
```

**Catatan:** 
- Ganti `DB_DATABASE` dengan nama database yang Anda inginkan
- Ganti `DB_USERNAME` dan `DB_PASSWORD` sesuai dengan konfigurasi MySQL Anda
- Pastikan database sudah dibuat di MySQL sebelum menjalankan migration

**Membuat Database di MySQL:**

```sql
CREATE DATABASE erp_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Atau menggunakan command line:

```bash
mysql -u root -p -e "CREATE DATABASE erp_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 7: Publish Sanctum Configuration

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### Step 8: Run Database Migrations

```bash
php artisan migrate
```

Ini akan membuat semua tabel yang diperlukan di database.

### Step 9: Seed Database (Optional but Recommended)

```bash
php artisan db:seed
```

Ini akan mengisi database dengan data awal:
- Roles (admin_gudang, spv, technician)
- Default users untuk testing
- Sample categories dan units

### Step 10: Build Frontend Assets

Untuk development:
```bash
npm run dev
```

Untuk production:
```bash
npm run build
```

**Catatan:** Untuk development, biarkan `npm run dev` berjalan di terminal terpisah agar Vite bisa hot-reload saat ada perubahan.

### Step 11: Start Development Server

Buka terminal baru dan jalankan:

```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

**Atau gunakan Laragon/XAMPP:**
- Pastikan virtual host sudah dikonfigurasi
- Akses melalui URL yang sudah dikonfigurasi (misal: `http://erp.test`)

### Step 12: Verify Installation

1. Buka browser dan akses `http://localhost:8000` atau URL aplikasi Anda
2. Anda akan diarahkan ke halaman login
3. Gunakan credentials default untuk login (lihat section Default Users di bawah)

## Troubleshooting

### Error: "Class 'PDO' not found"
- Install PHP PDO extension: `sudo apt-get install php-pdo php-mysql` (Linux) atau enable di `php.ini`

### Error: "SQLSTATE[HY000] [2002] Connection refused"
- Pastikan MySQL service sudah running
- Cek `DB_HOST` dan `DB_PORT` di file `.env`

### Error: "Vite manifest not found"
- Jalankan `npm run build` atau `npm run dev`
- Pastikan `public/build` directory ada

### Error: "Class 'App\Http\Middleware\CheckRole' not found"
- Jalankan `composer dump-autoload`

### Permission Denied (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Clear Cache
Jika ada masalah dengan cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Quick Setup Script (Optional)

Untuk mempercepat setup, Anda bisa membuat script:

**Windows (setup.bat):**
```batch
@echo off
echo Installing dependencies...
call composer install
call npm install
echo Copying .env file...
if not exist .env copy .env.example .env
echo Generating key...
call php artisan key:generate
echo Publishing Sanctum...
call php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
echo Running migrations...
call php artisan migrate
echo Seeding database...
call php artisan db:seed
echo Building assets...
call npm run build
echo Setup complete! Run 'php artisan serve' to start the server.
pause
```

**Linux/Mac (setup.sh):**
```bash
#!/bin/bash
echo "Installing dependencies..."
composer install
npm install
echo "Copying .env file..."
cp .env.example .env
echo "Generating key..."
php artisan key:generate
echo "Publishing Sanctum..."
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
echo "Running migrations..."
php artisan migrate
echo "Seeding database..."
php artisan db:seed
echo "Building assets..."
npm run build
echo "Setup complete! Run 'php artisan serve' to start the server."
```

Jalankan script:
```bash
# Windows
setup.bat

# Linux/Mac
chmod +x setup.sh
./setup.sh
```

## Development Workflow

### Running Development Server

Terminal 1 - Laravel Server:
```bash
php artisan serve
```

Terminal 2 - Vite Dev Server (untuk hot-reload):
```bash
npm run dev
```

### Accessing the Application

- **Web Interface**: `http://localhost:8000`
- **API Base URL**: `http://localhost:8000/api`

## API Documentation

**Note:** L5-Swagger v2.1 is not compatible with Laravel 12. For API documentation, consider using:
- **Scribe** (recommended for Laravel 12): 
  ```bash
  composer require knuckleswtf/scribe
  php artisan vendor:publish --tag=scribe-config
  php artisan scribe:generate
  ```
  Access at: `http://your-domain/docs`
- **OpenAPI Generator**: Manual setup with swagger-php annotations

## Default Users

Setelah menjalankan `php artisan db:seed`, Anda bisa login dengan credentials berikut:

- **Admin Gudang**: 
  - Email: `admin@example.com`
  - Password: `password`
  - Akses: Full access ke semua modul

- **Supervisor (SPV)**: 
  - Email: `spv@example.com`
  - Password: `password`
  - Akses: View master data, Approve requests/POs/opnames

- **Technician**: 
  - Email: `technician1@example.com`
  - Password: `password`
  - Akses: View master data, Create/edit own requests

**Penting:** Untuk production, pastikan untuk mengubah semua password default!

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `POST /api/auth/forgot-password` - Forgot Password
- `POST /api/auth/reset-password` - Reset Password

### Items Management
- `GET /api/items` - List items
- `POST /api/items` - Create item
- `GET /api/items/{id}` - Get item
- `PUT /api/items/{id}` - Update item
- `DELETE /api/items/{id}` - Delete item

### Categories
- `GET /api/categories` - List categories
- `POST /api/categories` - Create category
- `GET /api/categories/{id}` - Get category
- `PUT /api/categories/{id}` - Update category
- `DELETE /api/categories/{id}` - Delete category

### Units
- `GET /api/units` - List units
- `POST /api/units` - Create unit
- `GET /api/units/{id}` - Get unit
- `PUT /api/units/{id}` - Update unit
- `DELETE /api/units/{id}` - Delete unit

### Requests
- `GET /api/requests` - List requests
- `POST /api/requests` - Create request
- `GET /api/requests/{id}` - Get request
- `PUT /api/requests/{id}` - Update request
- `POST /api/requests/{id}/submit` - Submit request
- `POST /api/requests/{id}/approve` - Approve request
- `POST /api/requests/{id}/reject` - Reject request

### Purchase Orders
- `GET /api/purchase-orders` - List purchase orders
- `POST /api/purchase-orders` - Create purchase order
- `GET /api/purchase-orders/{id}` - Get purchase order
- `PUT /api/purchase-orders/{id}` - Update purchase order
- `POST /api/purchase-orders/{id}/approve` - Approve PO
- `POST /api/purchase-orders/{id}/send-to-vendor` - Send to vendor
- `POST /api/purchase-orders/{id}/confirm` - Confirm PO
- `POST /api/purchase-orders/{id}/cancel` - Cancel PO

### Receivings
- `GET /api/receivings` - List receivings
- `POST /api/receivings` - Create receiving
- `GET /api/receivings/{id}` - Get receiving
- `PUT /api/receivings/{id}` - Update receiving

### Inventory
- `GET /api/inventory` - List inventory
- `GET /api/inventory/{id}` - Get inventory
- `GET /api/inventory/{id}/movements` - Get stock movements

### Stock Opnames
- `GET /api/stock-opnames` - List stock opnames
- `POST /api/stock-opnames` - Create stock opname
- `GET /api/stock-opnames/{id}` - Get stock opname
- `PUT /api/stock-opnames/{id}` - Update stock opname
- `POST /api/stock-opnames/{id}/submit` - Submit opname
- `POST /api/stock-opnames/{id}/approve` - Approve opname

### Users
- `GET /api/users` - List users
- `POST /api/users` - Create user
- `GET /api/users/{id}` - Get user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user
- `POST /api/users/{id}/activate` - Activate user
- `POST /api/users/{id}/deactivate` - Deactivate user

## API Response Format

### Success Response
```json
{
    "success": true,
    "message": "Success message",
    "data": {},
    "meta": {}
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {}
}
```

## Authentication

All protected endpoints require authentication using Laravel Sanctum. Include the token in the Authorization header:

```
Authorization: Bearer {token}
```

## Roles

- **admin_gudang**: Full access to all modules
- **spv**: Can approve requests and purchase orders
- **technician**: Can create and manage requests

## Project Structure

```
erp/
├── app/
│   ├── Contracts/          # Repository Interfaces
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/       # API Controllers
│   │   │   └── Web/       # Web Controllers (Views)
│   │   └── Middleware/    # Custom Middleware
│   ├── Models/            # Eloquent Models
│   ├── Repositories/      # Repository Implementations
│   ├── Services/          # Business Logic Layer
│   └── Traits/            # Reusable Traits
├── database/
│   ├── migrations/        # Database Migrations
│   └── seeders/          # Database Seeders
├── resources/
│   ├── views/            # Blade Templates
│   ├── js/               # JavaScript Files
│   └── css/              # CSS Files
├── routes/
│   ├── api.php           # API Routes
│   └── web.php           # Web Routes
└── public/               # Public Assets
```

## Development Tips

### 1. Database Reset
Jika ingin reset database dan seed ulang:
```bash
php artisan migrate:fresh --seed
```

### 2. Create New Migration
```bash
php artisan make:migration create_table_name
```

### 3. Create New Model
```bash
php artisan make:model ModelName
```

### 4. Create New Controller
```bash
php artisan make:controller Api/ControllerName
```

### 5. Clear All Cache
```bash
php artisan optimize:clear
```

### 6. View Routes
```bash
php artisan route:list
```

### 7. Tinker (Laravel REPL)
```bash
php artisan tinker
```

## API Documentation

For API documentation, you can:

1. **Use Postman/Insomnia** - Import the API endpoints manually
2. **Use Scribe** (recommended for Laravel 12):
   ```bash
   composer require knuckleswtf/scribe
   php artisan vendor:publish --tag=scribe-config
   php artisan scribe:generate
   ```
   Access at: `http://your-domain/docs`

3. **Manual Documentation** - Use the endpoint list provided in this README

## Additional Resources

- **Laravel Documentation**: https://laravel.com/docs
- **Laravel Sanctum**: https://laravel.com/docs/sanctum
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Vite**: https://vitejs.dev/guide

## Support

Jika mengalami masalah saat instalasi atau development, pastikan:
1. Semua requirements sudah terinstall dengan versi yang sesuai
2. Database sudah dibuat dan dikonfigurasi dengan benar
3. File `.env` sudah dikonfigurasi
4. Semua dependencies sudah terinstall (`composer install` dan `npm install`)
5. Cache sudah di-clear jika ada masalah
