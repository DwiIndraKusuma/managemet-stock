# Inventory & Procurement Management System API

## Overview
Sistem Inventory & Supply Chain Management berbasis API untuk mengelola proses pengadaan dan pengelolaan stok secara terintegrasi.

## Setup Instructions

### 1. Install Dependencies
```bash
composer install
composer require laravel/sanctum
```

**Note:** L5-Swagger v2.1 is not compatible with Laravel 12. For API documentation, consider using:
- **Scribe** (recommended for Laravel 12): `composer require knuckleswtf/scribe`
- **OpenAPI Generator**: Manual setup with swagger-php annotations

### 2. Publish Sanctum
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Seed Database
```bash
php artisan db:seed
```

## Default Users

After seeding, you can login with:

- **Admin Gudang**: admin@example.com / password
- **Supervisor**: spv@example.com / password
- **Technician**: technician1@example.com / password

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
