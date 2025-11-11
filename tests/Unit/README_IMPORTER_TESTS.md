# Importer Tests

This directory contains unit tests for the PDF importers.

## Test Files

- **InvoiceImporterTest.php** - Tests for invoice PDF parsing
- **QuoteImporterTest.php** - Tests for quote PDF parsing
- **BankStatementImporterTest.php** - Tests for bank statement PDF parsing

## How It Works

Each test file automatically discovers PDF/JSON pairs in the `sample-documents/` directory and validates that the parser correctly extracts data from the PDFs.

### Test Discovery

The tests use data providers to automatically find all test cases:

```php
// InvoiceImporterTest discovers:
sample-documents/invoices/*.pdf + *.json pairs

// QuoteImporterTest discovers:
sample-documents/quotes/*.pdf + *.json pairs

// BankStatementImporterTest discovers:
sample-documents/bank-statements/*.pdf + *.json pairs
```

### Adding New Test Cases

1. Place a PDF file in the appropriate `sample-documents/` subdirectory
2. Create a `.json` file with the same name containing expected results
3. Run the tests - your new test case will be automatically included

Example:
```bash
# Add new test files
sample-documents/invoices/my-test-invoice.pdf
sample-documents/invoices/my-test-invoice.json

# Tests will automatically discover and run this test
./vendor/bin/sail artisan test --filter=InvoiceImporterTest
```

## Running Tests

### Run All Importer Tests
```bash
./vendor/bin/sail artisan test --filter=ImporterTest
```

### Run Specific Importer Tests
```bash
# Invoice tests only
./vendor/bin/sail artisan test --filter=InvoiceImporterTest

# Quote tests only
./vendor/bin/sail artisan test --filter=QuoteImporterTest

# Bank statement tests only
./vendor/bin/sail artisan test --filter=BankStatementImporterTest
```

### Run a Specific Test Case
```bash
# Run only one specific invoice test
./vendor/bin/sail artisan test --filter=InvoiceImporterTest::test_invoice_parsing#invoice-example-1
```

## Test Coverage

The tests validate:

### Invoice Tests
- Invoice number extraction
- Customer data (name, email, address, tax number)
- Dates (issue date, due date)
- Project information (name, service period, location)
- Line items with quantities and prices
- Financial calculations (subtotal, VAT, total)
- Project vs. general invoice detection

### Quote Tests
- Quote number extraction
- Customer data
- Dates (issue date, valid until)
- Project name
- Line items
- Financial calculations

### Bank Statement Tests
- Transaction date extraction
- Correspondent (merchant) names
- Transaction descriptions
- Transaction types/categories
- Amounts (positive for income, negative for expenses)
- Currency
- Bank type detection

## Expected JSON Format

See `/TESTING_IMPORTERS.md` for detailed documentation on the expected JSON structure for each document type.

## Debugging Failed Tests

When a test fails, you'll see output like:

```
1) InvoiceImporterTest::test_invoice_parsing with data set "invoice-example-1"
[invoice-example-1.pdf] Invoice number mismatch
Failed asserting that '493' is identical to '492'.
```

This means:
- The test file is `invoice-example-1.pdf`
- The field that failed is "Invoice number"
- The parser extracted '493' but expected '492'

To fix:
1. Open the PDF and verify the correct value
2. Either fix the parser logic or update the expected JSON
3. Re-run the test

## Skipped Tests

Tests are skipped when:
- No sample documents are available
- No matching PDF/JSON pairs are found
- The sample-documents directory doesn't exist

This is normal for fresh installations. Add PDF/JSON pairs to enable the tests.

## CI/CD Integration

These tests can be integrated into your CI/CD pipeline:

```yaml
# GitHub Actions example
- name: Run Importer Tests
  run: ./vendor/bin/sail artisan test --filter=ImporterTest
```

## Notes

- PDFs are not committed to git (see `sample-documents/.gitignore`)
- JSON files ARE committed to git (they're the test expectations)
- Example JSON files are provided with `EXAMPLE-` prefix
- Tests use a 0.01 tolerance for floating-point comparisons
- Missing optional fields should be `null`, not empty strings
