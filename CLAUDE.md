# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 8 inventory management system ("Sistema de Inventario") with role-based access control. The system manages inventory items, stock levels, suppliers, production processes, and provides analytics and reporting capabilities.

## Development Commands

### PHP/Laravel Commands
```bash
# Start Laravel development server
php artisan serve

# Run database migrations
php artisan migrate

# Seed the database
php artisan db:seed

# Create new migration
php artisan make:migration create_table_name

# Create new controller
php artisan make:controller ControllerName

# Create new model
php artisan make:model ModelName

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Generate application key
php artisan key:generate

# Run tests
vendor/bin/phpunit
# Or using artisan
php artisan test
```

### Frontend Asset Commands
```bash
# Install dependencies
npm install

# Development build with file watching
npm run dev
npm run watch

# Production build
npm run prod

# Development server with hot reloading
npm run hot
```

## Architecture

### User Roles & Access Control
The system uses Spatie Laravel Permission for role-based access control with the following roles:
- **Admin**: Full system access, user management, inventory configuration
- **Supervisor**: Inventory oversight, stock limits management, user administration
- **Worker**: Basic inventory operations, stock adjustments, movement logging

### Core Domain Models

**Items (app/Models/Item.php)**
- Central inventory entity supporting both simple items and hierarchical kits
- Types: 'element', 'component', 'kit'
- Tracks stock levels (min_stock, max_stock, current_stock)
- Hierarchical relationships through ItemItems pivot model
- Automatic stock status calculation (bajo_stock, en_minimo, normal, sobre_stock)

**Categories (app/Models/Category.php)**
- Organizes items by type and purpose
- Supports different category types for elements, components, and kits

**Users (app/Models/User.php)**
- Extended with role-based permissions using Spatie\Permission
- Uses Laravel Jetstream for authentication and profile management
- Additional fields: apellido_p, apellido_m, tel, sucursal_id, contract dates

**Suppliers (app/Models/Supplier.php)**
- Manages vendor information and relationships with items
- Tracks pricing and lead times through pivot tables

### Key Controllers

**AdminController**: Complete system administration, user management, inventory oversight
**WorkerController**: Daily inventory operations, stock adjustments, movement logging  
**SupervisorController**: Inventory analysis, stock limit management, reporting
**ProductionController**: Production workflow management, material consumption tracking
**InventoryDashboardController**: Main dashboard with KPIs and stock analytics
**ReportsController**: Analytics dashboard, exports, ABC analysis, stock reports

### Route Organization

Routes are organized by role with middleware protection:
- `/admin/*`: Administrative functions
- `/worker/*`: Worker-specific inventory operations  
- `/supervisor/*`: Supervisory management and analysis
- `/inventario/*`: Main inventory dashboard and alerts
- `/api/inventory/*`: AJAX endpoints for Vue.js components

### Frontend Architecture

- **Laravel Inertia**: Used for seamless SPA-like experience with Vue.js 3
- **Vue.js 3**: Frontend framework for reactive components
- **Tailwind CSS**: Utility-first CSS framework for styling
- **Laravel Mix & Vite**: Asset compilation with both legacy (webpack.mix.js) and modern (vite.config.js) setups

### Database Structure

Key relationships:
- Items have hierarchical self-relationships through ItemItems (kit ↔ components)
- Items belong to Categories and connect to Suppliers via pivot tables
- InventoryMovements track all stock changes with audit trails
- StockAlerts and InventoryAlerts provide automated notifications
- SystemLogs maintain comprehensive audit trails

### Key Features

- **Hierarchical Inventory**: Supports kits composed of multiple components/elements
- **Stock Management**: Min/max stock levels with automated alert generation
- **Role-Based Access**: Different interfaces and permissions per user role
- **Real-time Analytics**: Dashboard KPIs, stock charts, ABC analysis
- **Audit Trail**: Complete movement and system log tracking
- **Production Workflow**: Material consumption and production order management
- **Reporting**: Comprehensive exports and analytics capabilities

## Development Notes

- This is a Spanish-language application (comments and UI in Spanish)
- Uses decimal precision for stock quantities (allows fractional inventory)
- Implements soft deletes and active/inactive states rather than hard deletion
- Stock alerts are automatically generated when items fall below minimum thresholds
- The system supports both Laravel Mix (legacy) and Vite for asset compilation