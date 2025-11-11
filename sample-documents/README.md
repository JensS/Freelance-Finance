# Sample Documents for Testing

This directory contains sample PDF documents and their expected parsing results for testing the importers.

## Structure

```
sample-documents/
├── README.md                          # This file
├── invoices/                          # Sample invoices
│   ├── *.pdf                         # Invoice PDF files
│   └── *.json                        # Expected parsing results
├── quotes/                           # Sample quotes
│   ├── *.pdf                         # Quote PDF files
│   └── *.json                        # Expected parsing results
└── bank-statements/                  # Sample bank statements
    ├── *.pdf                         # Bank statement PDF files
    └── *.json                        # Expected parsing results
```

## Usage

1. Place your sample PDF files in the appropriate directory
2. Create a corresponding `.json` file with the expected parsing results
3. The test suite will automatically discover and test all PDF/JSON pairs

## Naming Convention

Each PDF file must have a corresponding JSON file with the same base name:

- `invoice-example-1.pdf` → `invoice-example-1.json`
- `quote-example-1.pdf` → `quote-example-1.json`
- `kontist-2025-09.pdf` → `kontist-2025-09.json`

## Documentation

For detailed information on creating test data, see `/TESTING_IMPORTERS.md`

## Running Tests

```bash
# Run all importer tests
./vendor/bin/sail artisan test --filter=ImporterTest

# Run specific tests
./vendor/bin/sail artisan test --filter=InvoiceImporterTest
./vendor/bin/sail artisan test --filter=QuoteImporterTest
./vendor/bin/sail artisan test --filter=BankStatementImporterTest
```
