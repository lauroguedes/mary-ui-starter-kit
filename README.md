# Mary UI Starter Kit 🚀

<div align="center">

A **modern, production-ready Laravel starter kit** featuring **Livewire Volt** and **Mary UI**. Build beautiful web applications with a complete authentication system, user management, and developer-friendly tooling.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat&logo=laravel)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-purple?style=flat)](https://livewire.laravel.com)
[![Mary UI](https://img.shields.io/badge/Mary_UI-2.x-blue?style=flat)](https://mary-ui.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat)](LICENSE)

</div>

## ✨ Features

### 🎨 **Frontend Stack**
- **Livewire 3.x** with **Volt** for reactive single-file components
- **Mary UI 2.x** - Beautiful, accessible UI components
- **Tailwind CSS 4.x** + **DaisyUI** for styling
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

### 🏗️ **Architecture & Developer Experience**
- **Laravel 12.x** with PHP 8.2+ support
- **SQLite** database by default (easy local setup)
- **Pest testing framework** with 77+ comprehensive tests
- **Code quality tools**: Pint (formatting), Rector (refactoring)
- **Debugging tools**: LaraDumps, Laravel Pail
- **Development workflow** with Concurrently for multi-process dev server

### 🧪 **Testing Coverage**
- **77 tests** with **218 assertions**
- Complete test coverage for authentication flows
- User management CRUD operations testing
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
```

### Development Workflow

For an enhanced development experience with hot reloading:

```bash
# Start all development services (server, queue, logs, vite)
composer run dev
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

Key environment variables for customization. Change the `APP_LAYOUT` to `sidebar` or `header` and `LOGIN_LAYOUT` to `card`, `simple`, or `split`:

```env
# Appearance settings
APP_LAYOUT=
LOGIN_LAYOUT=
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

- [ ] **Google Auth Provider**
- [ ] **Role-based permissions system**
- [ ] **Advanced Log and Audit**
- [ ] **Multi-tenant support**
- [ ] **Advanced notification system**
- [ ] **Dashboard analytics**
- [ ] **API integration with Laravel Sanctum**

## 🆘 Support

- **Documentation**: [Mary UI Docs](https://mary-ui.com)
- **Issues**: [GitHub Issues](https://github.com/lauroguedes/mary-ui-starter-kit/issues)
- **Discussions**: [GitHub Discussions](https://github.com/lauroguedes/mary-ui-starter-kit/discussions)

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

<div align="center">

**Built with ❤️ by [Lauro Guedes](https://lauroguedes.dev)**

**⭐ Star this repository if it helped you!**

</div>
