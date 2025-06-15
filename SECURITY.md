# Security Policy

## Supported Versions

We actively support the following versions of Mary UI Starter Kit:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

We take security seriously. If you discover a security vulnerability within Mary UI Starter Kit, please send an email to **laurowguedes@gmail.com** instead of creating a public issue.

### What to Include in Your Report

Please include the following information in your security report:

1. **Description**: A clear description of the vulnerability
2. **Steps to Reproduce**: Detailed steps to reproduce the issue
3. **Impact**: Description of the potential impact
4. **Affected Versions**: Which versions are affected
5. **Proof of Concept**: If possible, include a proof of concept
6. **Suggested Fix**: If you have suggestions for fixing the issue

### Response Timeline

- **Acknowledgment**: We will acknowledge receipt of your report within 24 hours
- **Initial Assessment**: We will provide an initial assessment within 48 hours
- **Updates**: We will provide regular updates on our progress
- **Resolution**: We aim to resolve critical security issues within 7 days

### Responsible Disclosure

We follow responsible disclosure practices:

1. We will work with you to understand and validate the vulnerability
2. We will develop and test a fix
3. We will release a security update
4. We will publicly acknowledge your contribution (if desired)

### Security Best Practices

When using Mary UI Starter Kit, please follow these security best practices:

#### Environment Configuration
- Never commit `.env` files to version control
- Use strong, unique application keys
- Configure proper database credentials
- Set up proper file permissions

#### Authentication & Authorization
- Use strong passwords
- Enable email verification
- Implement proper authorization checks
- Use HTTPS in production

#### File Uploads
- Validate file types and sizes
- Store uploaded files outside the web root
- Scan uploaded files for malware
- Implement proper access controls

#### Database Security
- Use parameterized queries (already implemented)
- Regular database backups
- Limit database user permissions
- Keep database software updated

#### General Security
- Keep Laravel and all dependencies updated
- Enable CSRF protection (enabled by default)
- Use secure headers
- Implement rate limiting
- Regular security audits

### Security Features

Mary UI Starter Kit includes several built-in security features:

- **CSRF Protection**: All forms include CSRF tokens
- **Password Hashing**: Passwords are hashed using Laravel's bcrypt
- **Input Validation**: Comprehensive input validation on all forms
- **File Upload Security**: Proper file type and size validation
- **SQL Injection Prevention**: Using Eloquent ORM and parameterized queries
- **XSS Prevention**: Blade templating auto-escapes output
- **Authentication**: Secure authentication system with email verification
- **Authorization**: Proper authorization checks throughout the application

### Known Security Considerations

- Avatar uploads are validated but should be scanned for malware in production
- Rate limiting should be implemented for authentication endpoints
- Consider implementing 2FA for enhanced security
- Review and configure CORS settings for API endpoints

### Security Updates

Security updates will be released as patch versions (e.g., 1.0.1, 1.0.2) and will be clearly marked in the [CHANGELOG](CHANGELOG.md).

To stay informed about security updates:
- Watch this repository for releases
- Subscribe to our security mailing list (if available)
- Follow our security advisories

### Hall of Fame

We recognize and thank security researchers who help us improve the security of Mary UI Starter Kit:

### Contact

For security-related questions or concerns:
- **Email**: laurowguedes@gmail.com
- **Security Issues**: Create a private vulnerability report
- **General Questions**: Use GitHub Discussions

Thank you for helping keep Mary UI Starter Kit secure! 🔒
