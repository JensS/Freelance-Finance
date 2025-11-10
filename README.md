# Freelance Finance Hub

A self-hosted Laravel-based accounting and invoicing system for freelancers.

## Features

- **Invoice & Quote Management**: Create professional invoices and quotes with PDF generation
- **Monthly Accounting**: Automated bank statement parsing and expense tracking  
- **Financial Reporting**: Monthly/yearly reports with visualizations
- **AI-Powered Insights**: Spending analysis and recommendations via Ollama
- **Paperless Integration**: Automatic document archiving

## Quick Start

```bash
# Clone and setup
git clone <repository-url>
cd freelance-finance-hub

# Install dependencies
./vendor/bin/sail composer install
./vendor/bin/sail npm install

# Start development environment
./vendor/bin/sail up -d

# Run migrations and seed
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed

# Build frontend
./vendor/bin/sail npm run dev
```

Visit `http://localhost:8080` to access the application.

## Requirements

- Docker & Docker Compose
- Laravel Sail
- PostgreSQL 18+
- PHP 8.2+

## Configuration

Copy `.env.example` to `.env` and configure:
- Database credentials
- Paperless integration URL
- Ollama API endpoint (optional)

## Usage

1. **Settings**: Configure company details in Settings → Company Information
2. **Customers**: Add customer information for invoicing
3. **Invoices/Quotes**: Create and manage invoices/quotes with PDF export
4. **Monthly Accounting**: Upload bank statements for automated expense tracking
5. **Reports**: Generate financial reports and tax summaries

## Development

```bash
# Run tests
./vendor/bin/sail artisan test

# Code style fix
./vendor/bin/sail pint

# Static analysis
./vendor/bin/sail composer phpstan
```

## License

**Personal Use License**

Copyright (c) 2025 [Your Name]

This project is licensed for **personal use only**. 

**Permissions** (for original author only):
- ✅ Commercial use
- ✅ Modification
- ✅ Distribution
- ✅ Private use

**Restrictions** (for all others):
- ❌ Commercial use
- ❌ Modification
- ❌ Distribution
- ❌ Public use

This software is provided "as is" without warranty. Only the original author retains commercial usage rights.

---

**Note**: This is a personal project. For commercial licensing inquiries, please contact the original author.
