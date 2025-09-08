# Gomaa/Base

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gomaa/base.svg)](https://packagist.org/packages/gomaa/base)
[![Total Downloads](https://img.shields.io/packagist/dt/gomaa/base.svg)](https://packagist.org/packages/gomaa/base)

`gomaa/base` is a Laravel package that provides **base commands and helpers** to speed up development.  
It helps you quickly generate **DTOs, Mappers, Services, and more** following clean architecture principles.

---

## 🚀 Installation

Install the package via Composer:

```bash
composer require gomaa/base
```
## ⚡ Available Commands

### 1. Generate CRUD Files

You can quickly generate a full CRUD module (Model, Migration, Controller, Service, Routes, etc.) using:

```bash
php artisan crud:all
```
This will create a full module for the **Product** entity with the following structure:

```json
"Product": {
    "fillables": {
      "id": "bigIncrements",
      "category_id": "unsignedBigInteger",
      "name": "string",
      "slug": "string|unique",
      "description": "text|nullable",
      "price": "decimal(10,2)",
      "stock": "integer",
      "is_active": "boolean|default:true",
      "created_at": "timestamp",
      "updated_at": "timestamp"
    }
  },
```
### 2. Route Registration

Make sure your `routes/api.php` includes the following snippet to automatically load all module routes:

```php
foreach (glob(base_path('app/Http/Modules').'/*/Route/index.php') as $routeFile) {
    require $routeFile;
}
```
This ensures that all generated CRUD routes are registered automatically.

### ⚡ What `crud:all Post` Generates

Running the command:

```bash
php artisan crud:all Post
```
Will automatically generate a full module for the Post entity, including:

Model (with defined $fillable properties)
Migration (with proper schema fields)
Controller (with CRUD endpoints)
Service (for business logic)
Route (auto-registered in routes/api.php)
Mapper (to map between Model ↔ DTO)
DTO (Data Transfer Object for the entity)
Requests (Form Request classes for Create, Update, Show, etc.)
Example Post definition

```json
{
  "Post": {
    "fillables": {
      "id": "int",
      "title": "string",
      "content": "text",
      "user_id": "unsignedBigInteger",
      "is_published": "boolean"
    }
  }
}
```
## 🛠 Requirements

- PHP >= 8.1  
- Laravel >= 10.x  
- Composer >= 2.x  

## 🤝 Contributing

Pull requests are welcome.  
For major changes, please open an issue first to discuss what you would like to change.

If you're contributing, don't forget to run the following:

```bash
composer update
git commit -m "Your changes"
git push origin main
git tag 1.0.1
git push -u origin --tags
```