# Mary UI Starter Kit

A modern Laravel starter kit featuring Livewire, Volt, Mary UI, and Tailwind CSS. This project provides a beautiful, ready-to-use authentication system, dashboard, and developer experience for building web applications rapidly.

## Features

- Laravel 12.x with PHP 8.2+
- Livewire 3.x, Volt, and Mary UI for reactive UIs
- Tailwind CSS 4.x and DaisyUI for styling
- Vite for asset bundling
- Authentication (login, registration, password reset, email verification)
- Dashboard and settings pages
- SQLite database by default (easy local setup)
- Pest for testing

## Getting Started

### Prerequisites
- PHP >= 8.2
- Node.js >= 18
- Composer

### Installation

```bash
# Clone the repository
$ git clone <your-repo-url> mary-ui-starter-kit
$ cd mary-ui-starter-kit

# Install PHP dependencies
$ composer install

# Install Node dependencies
$ npm install

# Copy environment file and generate app key
$ cp .env.example .env
$ php artisan key:generate

# Build frontend assets
$ npm run build

# Run database migrations
$ php artisan migrate

# Start the development server
$ php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000) to view the app.

## Running Tests

```bash
$ ./vendor/bin/pest
```

## License

This project is open-sourced under the MIT license.
