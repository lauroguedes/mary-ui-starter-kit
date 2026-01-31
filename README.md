# Mary UI Starter Kit 🚀

<div align="center">

A **modern, production-ready Laravel starter kit** featuring **Livewire 4** and **Mary UI**. Build beautiful web applications with a complete authentication system, user management, and developer-friendly tooling.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat&logo=laravel)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4.x-purple?style=flat)](https://livewire.laravel.com)
[![Mary UI](https://img.shields.io/badge/Mary_UI-2.x-blue?style=flat)](https://mary-ui.com)
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
- **Blade Hero and Fontawesome** icons integration
- **Vite** for lightning-fast asset bundling

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
- **Laravel 12.x** with PHP 8.2+ support
- **SQLite** database by default (easy local setup)
- **Pest testing framework** with 80+ comprehensive tests
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
- **PHP 8.2+**
- **Node.js 18+**
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

Run the comprehensive test suite:

```bash
# Run all tests
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage
```

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
4. Run the test suite: `./vendor/bin/pest`
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
./vendor/bin/pest
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
