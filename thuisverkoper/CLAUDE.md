# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

DIY Home-Selling Platform for the Netherlands - A Laravel-based web application allowing private sellers to list properties, purchase services, and manage the complete home-selling process.

**Target Environment**: Hostinger shared hosting/VPS
**Framework**: Laravel (PHP 8.x)
**Frontend**: Blade templates + Alpine.js + Tailwind CSS

## Key Features to Implement

- Property listing management (CRUD)
- Service marketplace with cart/checkout
- Document generation and e-signatures 
- Virtual property tours (360° viewer)
- Booking system for viewings
- Payment processing (Stripe/Mollie)
- User dashboard and notifications

## Development Commands

**Setup Commands** (already completed in this repository):
```bash
# Install PHP dependencies (if needed)
composer install

# Install Node.js dependencies
npm install

# Copy environment file and configure
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

**Daily Development Commands**:
```bash
# Start development server
php artisan serve

# Watch and compile frontend assets
npm run dev

# Build assets for production
npm run build

# Run all tests
php artisan test

# Run specific test class
php artisan test tests/Feature/PropertyControllerTest.php

# Run specific test method
php artisan test --filter=test_user_can_create_property

# Clear various caches during development
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Generate IDE helper files (if installed)
php artisan ide-helper:generate
php artisan ide-helper:models
```

**Production Optimization Commands**:
```bash
# Cache optimization for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer install --no-dev --optimize-autoloader
```

## Core Architecture

**MVC Structure**:
- **Models**: User, Property, Service, Order, OrderItem, Booking, Document
- **Controllers**: Authentication (Breeze), PropertyController, ServiceController, OrderController, PaymentController, BookingController, DocumentController, DashboardController
- **Views**: Blade templates in `resources/views/` with layouts in `resources/views/layouts/`
- **Middleware**: Standard Laravel auth middleware plus custom validation

**Key Services**:
- `PaymentService`: Unified payment handling for Stripe/Mollie with webhook validation, refunds, and cancellations
- `DocumentService`: PDF generation, signature capture, and document management

**Authentication & Authorization**:
- Laravel Breeze for session-based authentication
- Policy classes for authorization (BookingPolicy, OrderPolicy, DocumentPolicy)
- Route-level middleware protection for authenticated features

**Database Design**:
- MySQL with full-text search on properties table
- JSON columns for flexible data (features, images arrays)
- Proper foreign key relationships between all entities
- Migration-based schema management

**Asset Pipeline**:
- Vite for modern asset compilation (replaces Laravel Mix)
- Alpine.js for reactive frontend components
- Tailwind CSS with forms and typography plugins

## Database Schema Highlights

- `properties`: Property listings with JSON fields for features/images, full-text search capability, status management
- `services`: À-la-carte services (notary, EPC certificates, etc.) with category, pricing, and availability status
- `orders`: Master order records with payment provider tracking, total amounts, and status workflow
- `order_items`: Individual service items within orders with quantity and pricing details
- `bookings`: Property viewing appointments with scheduling, confirmation, and completion tracking
- `documents`: Generated PDFs with signature capture, verification hashes, and version control

**Key Relationships**:
- Properties belong to Users (sellers), have many Bookings and Documents
- Orders contain multiple OrderItems (services), belong to Users
- Bookings link Properties with Users (viewers) and include scheduling data
- Documents can be associated with Properties and Orders for contract generation

## Frontend Architecture

**Template Engine**: Blade templates with shared layouts in `resources/views/layouts/app.blade.php`

**JavaScript Framework**: Alpine.js for reactive components without build complexity
- Property listing interactions and filtering
- Shopping cart management (add/remove services)  
- Booking calendar and appointment scheduling
- Digital signature capture for contracts
- Modal dialogs and form interactions

**Styling**: Tailwind CSS v4 with additional plugins:
- `@tailwindcss/forms` for styled form elements
- `@tailwindcss/typography` for rich text content

**Specialized Libraries**:
- `pannellum` for 360° property virtual tours
- `signature_pad` for digital signature capture in documents
- `fullcalendar` for booking appointment management

**Asset Management**: Vite handles compilation and HMR during development

## Routes & API Structure

**Public Routes** (no authentication required):
- `GET /` - Welcome page
- `GET /properties` - Browse all property listings  
- `GET /properties/{property}` - Individual property details
- `GET /services` - Browse available services
- `GET /services/{service}` - Service details

**Authenticated Routes** (require login):
- `GET /dashboard` - User dashboard with overview
- Resource routes for: `properties`, `services`, `orders`, `bookings`, `documents`
- Cart management: add/remove services, view cart, checkout
- Payment processing with success/cancel handling
- Document signing workflows

**API Endpoints** (AJAX):
- `/api/orders/stats` - Order statistics for dashboard
- `/api/bookings/calendar-events` - Calendar integration data
- `/api/services/search` - Service search functionality

**Webhooks** (no CSRF protection):
- `POST /webhooks/stripe` - Stripe payment confirmations
- `POST /webhooks/mollie` - Mollie payment confirmations

## Payment Integration Architecture

**PaymentService Class** (`app/Services/PaymentService.php`):
- Unified interface for both Stripe and Mollie
- Payment session creation with Dutch payment methods (iDEAL, Bancontact)
- Webhook signature validation for security
- Refund processing and payment cancellation
- Status checking and payment method discovery

**Supported Payment Methods**:
- **Stripe**: Credit/debit cards, iDEAL, Bancontact, SEPA
- **Mollie**: iDEAL, credit cards, Bancontact, Sofort, KBC, Belfius, and more Dutch-specific methods

## Hostinger-Specific Deployment

- **Caching**: File-based caching (not Redis) - configured for shared hosting constraints
- **Queue Processing**: Database queue driver for background jobs
- **File Storage**: Local disk storage for uploads and documents
- **URL Rewriting**: `.htaccess` configuration for clean URLs and performance
- **Cron Jobs**: Set up Laravel scheduler for automated tasks and queue processing
- **PHP Version**: Requires PHP 8.1+ with required extensions
- **Database**: MySQL with proper charset and collation settings

## Key Dependencies

**Backend (Composer)**:
- `laravel/framework ^10.10` - Core Laravel framework
- `laravel/breeze` - Authentication scaffolding
- `laravel/sanctum` - API authentication
- `spatie/laravel-permission` - Role and permission management
- `barryvdh/laravel-dompdf` - PDF generation
- `intervention/image` - Image processing and optimization
- `stripe/stripe-php` - Stripe payment integration
- `mollie/laravel-mollie` - Mollie payment integration

**Frontend (NPM)**:
- `alpinejs` - Reactive JavaScript framework
- `tailwindcss ^4.1.11` - CSS framework
- `@tailwindcss/forms` & `@tailwindcss/typography` - Additional styling
- `pannellum` - 360° virtual tour viewer
- `signature_pad` - Digital signature capture
- `fullcalendar` - Appointment booking calendar

## Testing Strategy

- **PHPUnit Configuration**: `phpunit.xml` configured for Unit and Feature tests
- **Test Environment**: Isolated test database and session drivers
- **Test Structure**: Separate Unit and Feature test suites in `tests/` directory
- **Coverage**: Focus on critical business logic, payment flows, and authorization

Refer to `diy_home_platform_guide.md` for detailed implementation examples and code snippets.
