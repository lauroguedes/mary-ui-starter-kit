# Contributing to Mary UI Starter Kit

Thank you for considering contributing to the Mary UI Starter Kit! We welcome contributions from everyone.

## Code of Conduct

By participating in this project, you agree to abide by our Code of Conduct. We expect all contributors to be respectful and considerate.

## How to Contribute

### Reporting Bugs

Before submitting a bug report:
- Check if the issue has already been reported
- Ensure you're using the latest version
- Test with a clean installation

When submitting a bug report, include:
- A clear description of the issue
- Steps to reproduce the problem
- Expected vs actual behavior
- Environment details (PHP version, Laravel version, etc.)
- Screenshots if applicable

### Suggesting Features

We love feature suggestions! Before submitting:
- Check if the feature has already been requested
- Consider if it fits the project's scope and goals
- Think about how it would benefit other users

### Contributing Code

1. **Fork the Repository**
   ```bash
   git clone https://github.com/lauroguedes/mary-ui-starter-kit.git
   cd mary-ui-starter-kit
   ```

2. **Create a Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Set Up Development Environment**
   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   ```

4. **Make Your Changes**
   - Write clean, readable code
   - Follow existing code style and conventions
   - Add tests for new functionality
   - Update documentation if needed

5. **Test Your Changes**
   ```bash
   # Run all tests
   ./vendor/bin/pest
   
   # Check code style
   ./vendor/bin/pint --test
   
   # Run static analysis (if applicable)
   ./vendor/bin/rector --dry-run
   ```

6. **Commit Your Changes**
   ```bash
   git add .
   git commit -m "Add: brief description of your changes"
   ```

   Use conventional commit messages:
   - `feat:` for new features
   - `fix:` for bug fixes
   - `docs:` for documentation changes
   - `style:` for formatting changes
   - `refactor:` for code refactoring
   - `test:` for adding tests
   - `chore:` for maintenance tasks

7. **Push and Create Pull Request**
   ```bash
   git push origin feature/your-feature-name
   ```

## Development Guidelines

### Code Style

- Follow PSR-12 coding standards
- Use Laravel conventions and best practices
- Run `./vendor/bin/pint` to format code
- Use meaningful variable and method names

### Testing

- Write tests for all new functionality
- Use Pest testing framework
- Aim for high test coverage
- Test both happy path and edge cases
- Use descriptive test names

Example test structure:
```php
test('user can update their profile with valid data', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    // Test implementation
    
    expect($user->fresh()->name)->toBe('Updated Name');
});
```

### Database Changes

- Create migrations for schema changes
- Update model factories if needed
- Update seeders if applicable
- Test migrations both up and down

### Frontend Changes

- Follow Tailwind CSS conventions
- Use Mary UI components when possible
- Ensure responsive design
- Test in multiple browsers

### Livewire Components

- Use Volt single-file components
- Follow naming conventions
- Add proper validation
- Include comprehensive tests

## Pull Request Guidelines

### Before Submitting

- [ ] Tests pass locally
- [ ] Code follows style guidelines
- [ ] Documentation is updated
- [ ] Commit messages are clear
- [ ] No merge conflicts

### Pull Request Template

When creating a pull request, include:

1. **Description**: What does this PR do?
2. **Motivation**: Why is this change needed?
3. **Testing**: How was this tested?
4. **Screenshots**: For UI changes
5. **Breaking Changes**: Any backwards incompatible changes

### Review Process

1. Automated tests will run on your PR
2. Maintainers will review your code
3. Address any feedback or requested changes
4. Once approved, your PR will be merged

## Development Environment

### Requirements

- PHP 8.2+
- Node.js 18+
- Composer
- Git

### Useful Commands

```bash
# Development server with hot reload
composer run dev

# Run tests
./vendor/bin/pest

# Format code
./vendor/bin/pint

# Refactor code
./vendor/bin/rector

# Clear caches
php artisan optimize:clear
```

### Debugging

- Use LaraDumps for debugging: `ds($variable)`
- Check logs with Laravel Pail: `php artisan pail`
- Use browser dev tools for frontend issues

## Questions?

If you have questions about contributing:

- Check existing issues and discussions
- Create a new discussion for general questions
- Create an issue for specific problems

Thank you for contributing to Mary UI Starter Kit! 🚀
