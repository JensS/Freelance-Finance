# Continuous Integration & Testing

This document describes the CI/CD pipeline and testing strategy for Freelance Finance Hub.

## Overview

The project uses **GitHub Actions** for continuous integration, automatically running tests and code quality checks on every push and pull request to the `master` branch.

## CI Pipeline

The CI pipeline consists of three parallel jobs:

### 1. Tests Job (`tests`)

Runs the full test suite against multiple PHP versions to ensure compatibility.

**Matrix Strategy:**
- PHP 8.3
- PHP 8.4

**Services:**
- PostgreSQL 18 (matches production)

**Steps:**
1. Checkout code
2. Setup PHP with required extensions
3. Setup Node.js 20
4. Install Composer dependencies
5. Install NPM dependencies
6. Configure test database environment
7. Generate application key
8. Run database migrations
9. Build frontend assets
10. Execute PHPUnit tests in parallel mode

**Environment:**
- Database: `freelance_finance_test`
- PostgreSQL 18 on port 5432
- Optimized for CI with parallel test execution

### 2. Code Quality Job (`code-quality`)

Ensures code meets quality standards.

**Checks:**
- **Laravel Pint**: Code style validation (PSR-12 compliance)
  - Runs in test mode (`--test` flag) to verify without modifying files
  - Ensures consistent code formatting across the project

- **PHPStan**: Static analysis
  - Catches type errors and potential bugs before runtime
  - Enforces type safety and best practices

### 3. Security Audit Job (`security`)

Scans dependencies for known vulnerabilities.

**Audits:**
- **Composer**: PHP dependency security scan
- **NPM**: JavaScript dependency security scan

**Note:** Security audits run with `|| true` to warn but not fail the build, allowing teams to review and prioritize fixes.

## Local Testing

### Run All Tests

```bash
# Using Laravel Sail
./vendor/bin/sail artisan test

# With parallel execution (faster)
./vendor/bin/sail artisan test --parallel

# Run specific test file
./vendor/bin/sail artisan test --filter=InvoiceManagementTest
```

### Run Code Quality Checks

```bash
# Laravel Pint (code style)
./vendor/bin/sail pint

# Laravel Pint in test mode (check without fixing)
./vendor/bin/sail pint --test

# PHPStan (static analysis)
./vendor/bin/sail composer phpstan
```

### Run Security Audits

```bash
# Composer security audit
./vendor/bin/sail composer audit

# NPM security audit
./vendor/bin/sail npm audit
```

## Test Organization

### Test Suites

The project has two test suites defined in `phpunit.xml`:

1. **Unit Tests** (`tests/Unit/`)
   - Test individual classes and methods in isolation
   - No database or external dependencies
   - Fast execution
   - Examples:
     - `BankStatementImporterTest.php` (214 lines)
     - `InvoiceImporterTest.php` (259 lines)
     - `QuoteImporterTest.php` (232 lines)

2. **Feature Tests** (`tests/Feature/`)
   - Test complete application flows
   - Use database and full application stack
   - Test real-world scenarios
   - Examples:
     - `AuthenticationTest.php` - Login/logout flows
     - `SettingsTest.php` - Settings management
     - `InvoiceManagementTest.php` - Invoice CRUD operations
     - `DashboardTest.php` - Dashboard display and data

### Test Database Configuration

Tests use a separate PostgreSQL database (`freelance_finance_test`) configured in `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="pgsql"/>
<env name="DB_DATABASE" value="freelance_finance_test"/>
```

**Key Settings:**
- `BCRYPT_ROUNDS=4`: Faster password hashing in tests
- `CACHE_STORE=array`: In-memory cache (no Redis needed)
- `QUEUE_CONNECTION=sync`: Synchronous queue execution
- `SESSION_DRIVER=array`: In-memory sessions
- `MAIL_MAILER=array`: Capture emails without sending

### RefreshDatabase Trait

Feature tests use `RefreshDatabase` to:
- Migrate the database before each test
- Rollback transactions after each test
- Ensure test isolation and repeatability

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    // Tests run in isolated database state
}
```

## Writing Tests

### Feature Test Example

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Simulate authenticated session
        $this->withSession(['authenticated' => true]);
    }

    public function test_feature_works_as_expected(): void
    {
        $response = $this->get('/my-route');

        $response->assertStatus(200);
        $response->assertSeeLivewire('my-component');
    }
}
```

### Unit Test Example

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;

class MyUnitTest extends TestCase
{
    public function test_calculation_is_correct(): void
    {
        $result = calculateVat(100, 19);

        $this->assertEquals(19, $result);
    }
}
```

## Test Coverage

### Current Coverage

The project has strong test coverage for:
- ✅ Bank statement parsing and import
- ✅ Invoice creation and management
- ✅ Quote creation and management
- ✅ Authentication flows
- ✅ Settings management
- ✅ Dashboard functionality

### Areas for Expansion

Consider adding tests for:
- Paperless integration (mocked API calls)
- PDF generation (assert content and structure)
- AI vision service (mocked Ollama responses)
- Monthly report generation
- Financial calculations and VAT handling
- Cash receipt management
- Transaction verification workflow

## CI Status Badge

Add this badge to your README.md to show CI status:

```markdown
![CI](https://github.com/YOUR_USERNAME/Freelance-Finance/workflows/CI/badge.svg)
```

## Troubleshooting

### Tests Failing Locally But Pass in CI

1. **Check database state**: Run `./vendor/bin/sail artisan migrate:fresh`
2. **Clear caches**: Run `./vendor/bin/sail artisan optimize:clear`
3. **Check environment**: Ensure `.env.testing` doesn't override `phpunit.xml`

### Tests Pass Locally But Fail in CI

1. **Database differences**: CI uses PostgreSQL 18 - ensure compatibility
2. **Timing issues**: Add `sleep()` or use `waitFor()` for async operations
3. **File permissions**: CI has different file system permissions

### Pint Failures

If Pint reports style violations:

```bash
# Fix automatically
./vendor/bin/sail pint

# Review changes
git diff

# Commit fixes
git add .
git commit -m "Fix code style violations"
```

### PHPStan Errors

PHPStan enforces type safety. Common fixes:

```php
// Add type hints
public function calculate(float $amount): float

// Add PHPDoc for complex types
/** @var array<string, mixed> */
private array $settings;

// Use null coalescing
$value = $array['key'] ?? null;
```

## Performance Optimization

### Parallel Testing

The CI uses `--parallel` flag for faster test execution:
- Tests run in multiple processes
- Reduces total test time by ~60%
- Each process gets its own database

### Caching

GitHub Actions caches:
- Composer dependencies (`vendor/`)
- NPM packages (`node_modules/`)
- Reduces install time from ~3min to ~30sec

## Best Practices

1. **Write tests first** (TDD) when adding new features
2. **Keep tests isolated** - no shared state between tests
3. **Use factories** for creating test data consistently
4. **Mock external services** (Paperless, Ollama) to avoid network calls
5. **Test edge cases** - empty inputs, large datasets, invalid data
6. **Maintain fast tests** - unit tests should run in milliseconds
7. **Meaningful assertions** - test behavior, not implementation details
8. **Clear test names** - describe what is being tested

## Continuous Deployment

After CI passes, consider adding deployment steps:
- Deploy to staging environment
- Run smoke tests
- Deploy to production (manual approval)
- Post-deployment health checks

## Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Pint Documentation](https://laravel.com/docs/pint)
- [PHPStan Documentation](https://phpstan.org/)
