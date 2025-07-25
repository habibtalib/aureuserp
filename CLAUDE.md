# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

AureusERP is a comprehensive open-source ERP solution built on Laravel 11.x and FilamentPHP 3.x. It uses a modular plugin architecture where business functionality is organized into separate packages under `plugins/webkul/`.

## Key Development Commands

### Installation & Setup
```bash
# Install the entire ERP system
php artisan erp:install

# Install individual plugins
php artisan <plugin-name>:install
# Example: php artisan inventories:install

# Uninstall plugins
php artisan <plugin-name>:uninstall
```

### Development Workflow
```bash
# Start development environment (runs server, queue, logs, and vite)
composer dev

# Individual commands
php artisan serve              # Start Laravel server
php artisan queue:listen       # Process queues
php artisan pail              # View logs
npm run dev                   # Start Vite development

# Build assets
npm run build
vite build

# Code quality
php artisan pint              # PHP CS Fixer (Laravel Pint)

# Testing
php artisan test              # Run PHPUnit tests
```

### Database Operations
```bash
# Run migrations
php artisan migrate

# Generate roles/permissions (after installing Filament Shield)
php artisan shield:generate

# Seed database
php artisan db:seed
```

## Architecture Overview

### Core Structure
- **Laravel 11.45.x** foundation with **FilamentPHP 3.x** admin panels
- **Multi-panel architecture**: Admin panel (`/admin`) and Customer panel
- **Plugin-based system** using Composer's merge-plugin for modular functionality
- **Service Provider pattern** for plugin registration and bootstrapping

### Plugin Architecture
All business logic is organized into plugins located in `plugins/webkul/`:

#### Core Plugins (Always Installed)
- **Analytics** - Business intelligence and reporting
- **Chatter** - Internal communication system
- **Fields** - Custom field management
- **Security** - Role-based access control
- **Support** - Help desk functionality
- **Table Views** - Customizable data presentation

#### Installable Plugins
- **Accounts** - Financial accounting and reporting
- **Blogs** - Blog management system
- **Contacts** - Customer/vendor contact management
- **Employees** - HR and employee management
- **Inventories** - Warehouse and inventory control
- **Partners** - Partner relationship management
- **Products** - Product catalog management
- **Projects** - Project planning and tracking
- **Purchases** - Procurement management
- **Sales** - Sales pipeline management
- **Website** - Customer-facing website

### Plugin Structure
Each plugin follows a consistent structure:
```
plugins/webkul/<plugin-name>/
├── composer.json              # Plugin dependencies
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/             # Data seeders
├── resources/
│   ├── lang/                # Translations
│   └── views/               # Blade templates
└── src/
    ├── <Plugin>ServiceProvider.php
    ├── <Plugin>Plugin.php
    ├── Filament/            # FilamentPHP resources
    ├── Models/              # Eloquent models
    ├── Enums/               # PHP enums
    └── Policies/            # Authorization policies
```

### Key Architectural Patterns

#### Plugin Registration
- Plugins register via `PluginManager::make()` in `AdminPanelProvider`
- Each plugin has a ServiceProvider for dependency injection
- Plugin composer.json files are merged using `wikimedia/composer-merge-plugin`

#### FilamentPHP Integration
- Admin interface built with FilamentPHP panels
- Uses `FilamentShieldPlugin` for role-based permissions
- Navigation organized into groups (Dashboard, Settings, etc.)
- Resources, forms, and tables defined in `Filament/` directories

#### Database Design
- Uses Laravel migrations with plugin-specific prefixes
- Follows naming convention: `<plugin>_<table_name>`
- Factory classes for testing and seeding
- Policy classes for authorization

## Development Guidelines

### Plugin Development
- Follow the existing plugin structure when creating new modules
- Use the `php artisan <plugin>:install` pattern for setup commands
- Include database factories and seeders for testing
- Implement FilamentPHP resources for admin interface

### Code Standards
- Follow Laravel/PHP best practices
- Use Laravel Pint for code formatting
- Implement proper authorization with Policies
- Use PHP enums for constants and states

### Testing
- Write tests using PHPUnit
- Use factories for test data generation
- Test plugin installation/uninstallion workflows

### Dependencies
- Plugin dependencies are managed through individual composer.json files
- Core Laravel dependencies defined in root composer.json
- Frontend dependencies managed through npm/package.json