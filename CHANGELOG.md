# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of Mary UI Starter Kit
- Complete authentication system with Laravel Breeze-style flows
- User management dashboard with CRUD operations
- Avatar upload and management system
- Comprehensive test suite with 77+ tests
- Development workflow with Concurrently
- Code quality tools (Pint, Rector, LaraDumps)

### Features

#### Authentication & Security
- Login, registration, and password reset flows
- Email verification with resend functionality
- Password confirmation for sensitive operations
- Secure logout functionality

#### User Management
- Full CRUD operations for user management
- Advanced search and filtering capabilities
- User status management (Active, Inactive, Suspended)
- Bulk operations with proper authorization
- Avatar upload with image cropping (Cropper.js)
- Automatic file cleanup on user deletion

#### Developer Experience
- Laravel 12.x with PHP 8.2+ support
- Livewire 3.x with Volt single-file components
- Mary UI 2.x for beautiful, accessible components
- Tailwind CSS 4.x + DaisyUI for modern styling
- Vite for fast asset bundling
- SQLite database for easy local development

#### Testing & Quality
- 77 comprehensive tests with 218 assertions
- Pest testing framework with modern syntax
- Complete test coverage for all features
- File upload testing with Storage fakes
- Form validation and error handling tests
- Database integrity and cleanup tests

#### Code Quality Tools
- Laravel Pint for code formatting
- Rector for automated refactoring
- LaraDumps for enhanced debugging
- Laravel Pail for log monitoring
- Concurrently for multi-process development

### Technical Details

#### Dependencies
- `laravel/framework: ^12.0`
- `livewire/livewire: ^3.6`
- `livewire/volt: ^1.7.0`
- `robsontenorio/mary: ^2.0`
- `pestphp/pest: ^3.8`

#### Browser Support
- Chrome/Edge 88+
- Firefox 85+
- Safari 14+

## [1.0.0] - 2024-06-15

### Added
- Initial stable release
- Production-ready codebase
- Comprehensive documentation
- Open-source license (MIT)
- Packagist distribution ready

---

## Release Notes

### v1.0.0 - Initial Release

This is the first stable release of Mary UI Starter Kit, providing a solid foundation for building modern Laravel applications with Livewire and Mary UI.

**Key Highlights:**
- 🚀 **Production Ready**: Fully tested and documented
- 🎨 **Modern UI**: Beautiful components with Mary UI and Tailwind CSS
- 🔐 **Complete Auth**: All authentication flows included
- 👥 **User Management**: Full CRUD with advanced features
- 🧪 **Well Tested**: 77+ tests ensuring reliability
- 🛠️ **Developer Friendly**: Excellent DX with modern tooling

**What's Included:**
- Authentication system (login, register, password reset, email verification)
- User management dashboard with search, filtering, and status management
- Avatar upload system with cropping and automatic cleanup
- Comprehensive test suite covering all functionality
- Development workflow with hot reloading and debugging tools
- Code quality tools for maintaining clean code

**Getting Started:**
```bash
composer create-project lauroguedes/mary-ui-starter-kit my-app
```

**Requirements:**
- PHP 8.2+
- Node.js 18+
- Composer

For detailed installation and usage instructions, see the [README](README.md).

---

### Migration Guide

Since this is the initial release, no migration is required.

### Breaking Changes

No breaking changes in this release.

### Deprecations

No deprecations in this release.

### Security

This release includes:
- Secure authentication flows
- Password confirmation for sensitive operations
- Proper authorization checks
- Input validation and sanitization
- CSRF protection
- File upload validation

### Performance

- Optimized for Laravel 12.x
- Efficient Livewire components
- Minimal asset bundle size
- Fast development workflow

---

For more information about releases, see our [GitHub Releases](https://github.com/lauroguedes/mary-ui-starter-kit/releases) page.