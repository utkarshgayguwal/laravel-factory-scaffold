# Laravel Factory Scaffold

Automatically generate factories and seeders for Laravel models with smart data detection. Perfect for rapid prototyping and testing!
---
## Features
- 🚀 Auto-detects table columns and data types  
- 🔗 Handles foreign keys (random IDs 1-5)  
- 📅 Uses Carbon for timestamps (`created_at`, `updated_at`)  
- 📂 Supports subfolder-based models (e.g., `App\Models\HR\User`)  
- 🧩 Faker-powered fake data (emails, names, phones, etc.)  
---
## Installation
```bash
composer require utkarshgayguwal/laravel-factory-scaffold
```
--- 
## Usage
### Basic Command
```bash
php artisan make:scaffold App/Models/User
```
#### Generates:
- database/factories/UserFactory.php
- database/seeders/UserSeeder.php (with 10 fake records)

### Custom Record Count
```bash
php artisan make:scaffold App/Models/User --count=5
```

### Nested Models
For models in subfolders (eg. App\Models\LeaveManagement\Leave):
```bash
php artisan make:scaffold App/Models/LeaveManagement/Leave
```
#### Generates:
- database/factories/LeaveManagement/LeaveFactory.php
- database/seeders/LeaveSeeder.php (with 10 fake records)
---

## Requirements:
- PHP 8.0+
- Laravel 9.x, or above versions
