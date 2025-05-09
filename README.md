# Library Management System

A comprehensive Library Management System built with vanilla PHP 7+ using OOP principles and MVC architecture.

## Features

### For Librarians (Admins)
- Complete book management (add, edit, delete)
- Member management
- Loan processing and tracking
- Administrative dashboard with statistics
- Audit logs and reporting
- User management

### For Members
- Book searching and browsing
- Personal loan history
- Profile management
- Book reservation

## Technical Specifications

- **Architecture**: MVC pattern with PSR-4 autoloading
- **Database**: MySQL with PDO
- **Security**: Session-based authentication, CSRF protection, input validation
- **Frontend**: Responsive CSS, Mobile-first approach, ARIA-compliant
- **JavaScript**: Modular ES6 JavaScript (no external frameworks)

## Installation

1. Clone the repository
2. Run `composer install` to set up autoloading
3. Configure your database in `config/config.php`
4. Import the database schema from `database/schema.sql`
5. Optionally import seed data using `database/seeds.php`
6. Set up a virtual host or run `composer serve` to start a development server

## Directory Structure

```
library-management/
├─ app/ - Application specific code
│ ├─ Controllers/ - Controller classes
│ ├─ Models/ - Model classes
│ ├─ Views/ - View templates
│ └─ routes.php - Route definitions
├─ core/ - Core framework classes
├─ config/ - Configuration files
├─ database/ - Database schema and seeds
├─ logs/ - Log files
├─ public/ - Publicly accessible files
└─ vendor/ - Composer dependencies
```

## Security

This application implements several security features:
- Session-based authentication
- CSRF protection
- Input validation and sanitization
- Audit logging

## License

[MIT License](LICENSE)

## Credits

Created by [Your Name]