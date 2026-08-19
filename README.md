# Mary UI Starter Kit 🚀

<div align="center">

A **modern, production-ready Laravel starter kit** featuring **Livewire 4** and **Mary UI**. Build beautiful web applications with a complete authentication system, user management, and developer-friendly tooling.

[![Laravel](https://img.shields.io/badge/Laravel-13.x-red?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777bb4?style=flat&logo=php&logoColor=white)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-4.x-purple?style=flat)](https://livewire.laravel.com)
[![Mary UI](https://img.shields.io/badge/Mary_UI-2.x-blue?style=flat)](https://mary-ui.com)
[![Pest](https://img.shields.io/badge/Pest-5.x-8b5cf6?style=flat)](https://pestphp.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat)](LICENSE)
---
[![Packagist Version](https://img.shields.io/packagist/v/lauroguedes/mary-ui-starter-kit?style=flat)](https://packagist.org/packages/lauroguedes/mary-ui-starter-kit)
[![Packagist Downloads](https://img.shields.io/packagist/dt/lauroguedes/mary-ui-starter-kit?style=flat)](https://packagist.org/packages/lauroguedes/mary-ui-starter-kit)
[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2F9657bd09-8c7c-4219-ab01-ba07f6679c8a&style=flat)](https://forge.laravel.com/lauro-guedes-q58/graceful-silence-fzg/3022898)
</br>

![demo_screenshot](https://github.com/user-attachments/assets/6005b127-2d3f-4c7e-a4fc-2dfc257faafb)

</div>

## ✨ Features

### 🎨 **Frontend Stack**
- **Livewire 4.x** for reactive components with improved performance
- **Mary UI 2.x** - Beautiful, accessible UI components
- **Tailwind CSS 4.x** + **DaisyUI v5** for styling
- **Blade Heroicons and Font Awesome 7** icons integration
- **Vite 8** for lightning-fast asset bundling
- **Live version badges** on the welcome page, read from the running app and `composer.lock`

### 🔐 **Authentication & User Management**
- Complete authentication system (login, registration, password reset)
- Email verification with resend functionality
- Password confirmation for sensitive operations
- User profile management with avatar uploads
- **User management dashboard** with full CRUD operations
- **User status management** (Active, Inactive, Suspended)
- **Advanced filtering and search** capabilities
- **Avatar management** with automatic cleanup
- **Google OAuth integration** for social login
- **Roles & Permissions system** powered by Spatie Laravel Permission
- **Role-based access control** with granular permission management
- **User role assignment** and permission checking middleware

### 🔗 **OAuth Socialite Integration**
- **Laravel Socialite** integration with extensible provider system
- **Google OAuth** authentication out of the box
- **Social account linking** to existing user accounts
- **Automatic user creation** for new social logins
- **Extensible architecture** for adding new OAuth providers
- **Secure token handling** and user data synchronization

### 🎭 **Demo Mode**
- **Built-in demo mode** for showcasing your application
- **Scheduled data reset** to maintain clean demo environment
- **Configurable demo password** or auto-generated random password on each reset
- **Login protection** prevents password changes in demo mode
- **Visual indicator** alerts users when demo mode is active
- **Configurable reset schedule** (hourly, daily, etc.)

### 🏗️ **Architecture & Developer Experience**
- **Laravel 13.x** with PHP 8.4+ support
- **SQLite** database by default (easy local setup)
- **Pest 5 testing framework** with 230+ comprehensive tests, running in parallel
- **Code quality tools**: Pint (formatting), Rector (refactoring)
- **Debugging tools**: LaraDumps, Laravel Pail
- **Development workflow** with Concurrently for multi-process dev server
- **OAuth Socialite Integration** with extensible architecture for new oauth providers

### 🧪 **Testing Coverage**
- Complete test coverage for authentication flows
- User management CRUD operations testing
- Roles and Permissions management CRUD operations testing
- Demo mode functionality testing
- File upload and avatar management testing
- Form validation and error handling
- Database cleanup and file storage testing

### 📁 **File Management**
- Avatar upload with cropping support (Cropper.js)
- Automatic file cleanup on user deletion
- File validation (type, size)
- Storage testing with fake disks

## 🚀 Quick Start

### Prerequisites
- **PHP 8.4+** (Laravel 13 needs 8.3+, Pest 5 raises the floor to 8.4)
- **Node.js 22+**
- **Composer**
- **SQLite** (included with PHP)

### Installation

```bash
# Install via Laravel Installer
laravel new my-app --using=lauroguedes/mary-ui-starter-kit

# or Composer
composer create-project lauroguedes/mary-ui-starter-kit my-app

# (Optional) Generate fake data for testing
php artisan db:seed

# Default user
user: test@user.com
pw: secret
```

Clone the repository manually:
```bash
# Clone the repository
git clone https://github.com/lauroguedes/mary-ui-starter-kit
cd mary-ui-starter-kit

# Install PHP dependencies
composer install

# Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# Set up the database
php artisan migrate --seed

# Install frontend dependencies
npm install
# or if you use Yarn
yarn

# Run the development server
php artisan serve
# In a separate terminal
npm run dev
# or
yarn dev
```

### Development Workflow

For an enhanced development experience with hot reloading:

```bash
# Start all development services (server, queue, logs, vite)
composer dev
```

Visit [http://localhost:8000](http://localhost:8000) to view your application.

This runs:
- Laravel development server
- Queue worker
- Log monitoring (Pail)
- Vite dev server with hot reload

## 🧪 Testing

The suite runs on **Pest 5** and is parallel-safe, which takes a full run from ~40s down to ~9s.

```bash
# Run the whole suite in parallel (the default)
composer test

# Rerun only what recent changes touched (Test Impact Analysis)
composer test:tia

# Enforce the 80% coverage gate
composer test:coverage

# Refresh the time-balanced shard timings after adding or removing tests
composer test:shards
```

Or call Pest directly:

```bash
./vendor/bin/pest --parallel
./vendor/bin/pest --shard=1/2 --parallel
```

### Notes on the Pest 5 features

- **Parallel** is the default. Anything that writes to shared state outside the
  database must stay process-local — that is why `phpunit.xml` sets
  `APP_MAINTENANCE_DRIVER=array`, so `demo:reset` calling `artisan down` cannot
  put *other* test processes into maintenance mode.
- **Tia** (`--tia`) needs a coverage driver (`pcov` or Xdebug). Without one Pest
  prints a notice and runs the full suite instead.
- **Time-balanced sharding** reads `tests/.pest/shards.json`, which is committed
  so CI shards stay stable. CI runs two shards per PHP version.

## 🔧 Customization

Key environment variables for customization:

```env
# Appearance settings
APP_LAYOUT=sidebar      # Options: sidebar, header
LOGIN_LAYOUT=card       # Options: card, simple, split

# Demo mode settings
DEMO_MODE=false         # Enable demo mode for showcasing the app
DEMO_PASSWORD=          # Fixed password for all demo users (random if not set)
DEMO_RESET_SCHEDULE=hourly  # Options: hourly, daily, weekly
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes and add tests
4. Run the test suite: `composer test`
5. Commit your changes: `git commit -m 'Add amazing feature'`
6. Push to the branch: `git push origin feature/amazing-feature`
7. Open a Pull Request

### Code Quality

We maintain high code quality standards:

```bash
# Format code
./vendor/bin/pint

# Refactor code
./vendor/bin/rector

# Run tests
composer test
```

## 📋 Roadmap

- [x] **Role-based permissions system** ✅
- [x] **Demo mode for showcasing** ✅
- [ ] **Advanced Log and Audit**
- [ ] **Multi-tenant support**
- [ ] **Advanced notification system**
- [ ] **Dashboard analytics**
- [ ] **API integration with Laravel Sanctum**

## 🆘 Support

- **Documentation**: [Mary UI Docs](https://mary-ui.com)
- **Issues**: [GitHub Issues](https://github.com/lauroguedes/mary-ui-starter-kit/issues)

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

<div align="center">

**Built with ❤️ by [Lauro Guedes](https://lauroguedes.dev)**

**⭐ Star this repository if it helped you!**

</div>
