# Importer Testing Documentation

This document explains how to create test data for the PDF importers (invoices, quotes, and bank statements).

## Overview

The Freelance Finance Hub uses three main importers:

1. **DocumentParser** - Parses invoices and quotes from PDF files
2. **BankStatementParser** - Parses bank statements (Kontoauszüge) from PDF files

Each importer has corresponding tests that validate the parsing logic against expected results stored in JSON files.

## Directory Structure

```
sample-documents/
├── invoices/
│   ├── invoice-example-1.pdf          # Sample invoice PDF
│   ├── invoice-example-1.json         # Expected parsing result
│   ├── invoice-example-2.pdf
│   └── invoice-example-2.json
├── quotes/
│   ├── quote-example-1.pdf            # Sample quote PDF
│   ├── quote-example-1.json           # Expected parsing result
│   └── ...
└── bank-statements/
    ├── kontist-statement-2025-09.pdf  # Sample bank statement PDF
    ├── kontist-statement-2025-09.json # Expected parsing result
    └── ...
```

## JSON Format Specifications

### 1. Invoice Expected Results (`invoice-*.json`)

Each invoice PDF should have a corresponding JSON file with the expected parsing results.

**Structure:**
```json
{
  "type": "invoice",
  "invoice_number": "492",
  "customer_data": {
    "name": "Ageras GmbH",
    "email": "customer@example.com",
    "street": "Friedrichstraße 123",
    "zip": "10117",
    "city": "Berlin",
    "tax_number": "DE123456789"
  },
  "issue_date": "2025-06-04",
  "due_date": "2025-06-18",
  "service_period_start": "2025-05-19",
  "service_period_end": "2025-05-26",
  "service_location": "Berlin, Deutschland",
  "project_name": "Sage Video",
  "items": [
    {
      "description": "Videoproduktion",
      "quantity": 1,
      "unit_price": 11607.33,
      "total": 11607.33
    }
  ],
  "subtotal": 11607.33,
  "vat_rate": 19.0,
  "vat_amount": 2205.39,
  "total": 13812.72,
  "notes": null,
  "is_project_invoice": true
}
```

**Field Descriptions:**
- `type`: Always "invoice" for invoices
- `invoice_number`: The invoice number extracted from the PDF
- `customer_data`: Object containing customer information
  - `name`: Company or person name
  - `email`: Customer email (optional)
  - `street`: Street address (optional)
  - `zip`: Postal code (optional)
  - `city`: City name (optional)
  - `tax_number`: Tax ID or VAT number (optional)
- `issue_date`: Invoice date in YYYY-MM-DD format
- `due_date`: Payment due date in YYYY-MM-DD format (optional)
- `service_period_start`: Start of service period in YYYY-MM-DD format (for project invoices)
- `service_period_end`: End of service period in YYYY-MM-DD format (for project invoices)
- `service_location`: Location where service was performed (for project invoices)
- `project_name`: Name of the project (for project invoices)
- `items`: Array of line items
  - `description`: Item description
  - `quantity`: Quantity (float)
  - `unit_price`: Price per unit (float)
  - `total`: Total for this line (float)
- `subtotal`: Net amount before VAT (float)
- `vat_rate`: VAT rate percentage (float)
- `vat_amount`: VAT amount in currency (float)
- `total`: Gross total including VAT (float)
- `notes`: Additional notes (optional, can be null)
- `is_project_invoice`: Boolean indicating if this is a project-type invoice

**Important Notes:**
- All amounts should be in float format with decimal points (e.g., 1234.56)
- Dates must be in YYYY-MM-DD format
- Optional fields can be `null` if not present in the PDF
- If a field cannot be extracted, use `null` rather than empty string

---

### 2. Quote Expected Results (`quote-*.json`)

Each quote PDF should have a corresponding JSON file with the expected parsing results.

**Structure:**
```json
{
  "type": "quote",
  "quote_number": "2025-PC-Sust-Vid-v1",
  "customer_data": {
    "name": "Example Company GmbH",
    "email": "contact@example.com",
    "street": "Hauptstraße 1",
    "zip": "10115",
    "city": "Berlin",
    "tax_number": null
  },
  "issue_date": "2025-03-15",
  "valid_until": "2025-04-15",
  "project_name": "Sustainability Video Production",
  "items": [
    {
      "description": "Concept Development",
      "quantity": 1,
      "unit_price": 2500.00,
      "total": 2500.00
    },
    {
      "description": "Video Production (2 days)",
      "quantity": 2,
      "unit_price": 1800.00,
      "total": 3600.00
    },
    {
      "description": "Post-Production",
      "quantity": 1,
      "unit_price": 3000.00,
      "total": 3000.00
    }
  ],
  "subtotal": 9100.00,
  "vat_rate": 19.0,
  "vat_amount": 1729.00,
  "total": 10829.00,
  "notes": "Price valid for 30 days"
}
```

**Field Descriptions:**
- `type`: Always "quote" for quotes
- `quote_number`: The quote number extracted from the PDF
- `customer_data`: Same structure as invoices
- `issue_date`: Quote date in YYYY-MM-DD format
- `valid_until`: Validity end date in YYYY-MM-DD format
- `project_name`: Name of the project (optional)
- `items`: Array of line items (same structure as invoices)
- `subtotal`: Net amount before VAT (float)
- `vat_rate`: VAT rate percentage (float)
- `vat_amount`: VAT amount in currency (float)
- `total`: Gross total including VAT (float)
- `notes`: Additional notes (optional)

---

### 3. Bank Statement Expected Results (`bank-statement-*.json`)

Each bank statement PDF should have a corresponding JSON file with the expected parsing results.

**Structure:**
```json
{
  "bank_type": "solaris",
  "statement_month": "2025-09",
  "transactions": [
    {
      "date": "2025-09-02",
      "correspondent": "Ageras GmbH",
      "title": "Payment for Invoice 492",
      "description": "SEPA-Überweisung Ageras GmbH",
      "type": "Einkommen 19%",
      "amount": 13812.72,
      "currency": "EUR"
    },
    {
      "date": "2025-09-05",
      "correspondent": "Amazon Web Services",
      "title": "AWS Cloud Services",
      "description": "SEPA-Lastschrift AWS EMEA",
      "type": "Geschäftsausgabe 19%",
      "amount": -85.43,
      "currency": "EUR"
    },
    {
      "date": "2025-09-10",
      "correspondent": "Google Ireland Ltd",
      "title": "Google Workspace",
      "description": "SEPA-Lastschrift Google",
      "type": "Geschäftsausgabe 0%",
      "amount": -12.99,
      "currency": "EUR"
    },
    {
      "date": "2025-09-15",
      "correspondent": "Finanzamt Berlin",
      "title": "Umsatzsteuer Q3 2025",
      "description": "Überweisung Finanzamt",
      "type": "Steuerzahlung",
      "amount": -2500.00,
      "currency": "EUR"
    }
  ]
}
```

**Field Descriptions:**
- `bank_type`: Type of bank detected (e.g., "solaris", "generic")
- `statement_month`: Month of the statement in YYYY-MM format (optional)
- `transactions`: Array of transaction objects
  - `date`: Transaction date in YYYY-MM-DD format
  - `correspondent`: Merchant/company name (first line of transaction description)
  - `title`: Additional transaction details/reference (optional)
  - `description`: Full transaction description as it appears in the statement
  - `type`: Transaction category (see types below)
  - `amount`: Transaction amount (negative for expenses, positive for income)
  - `currency`: Currency code (usually "EUR")

**Transaction Types (Kontist Format):**
- `Geschäftsausgabe 0%` - Business expense, 0% VAT (international services)
- `Geschäftsausgabe 7%` - Business expense, 7% VAT (reduced rate)
- `Geschäftsausgabe 19%` - Business expense, 19% VAT (standard rate)
- `Einkommen 19%` - Income with 19% VAT
- `Reverse Charge` - Reverse charge mechanism for EU services
- `Privat` - Private transaction (to be excluded from business accounting)
- `Umsatzsteuerstattung` - VAT refund
- `Steuerzahlung` - Tax payment
- `Nicht kategorisiert` - Not categorized

**Important Notes:**
- Amounts are negative for expenses, positive for income
- The `correspondent` field should contain the merchant name
- The `description` field contains the full raw text from the PDF
- Not all fields may be extractable from all PDFs - use `null` for missing fields

---

## Creating Test Data

### Step 1: Place Your PDF Files

1. Create the directory structure:
   ```bash
   mkdir -p sample-documents/invoices
   mkdir -p sample-documents/quotes
   mkdir -p sample-documents/bank-statements
   ```

2. Copy your sample PDF files into the appropriate directory:
   - Invoices go in `sample-documents/invoices/`
   - Quotes go in `sample-documents/quotes/`
   - Bank statements go in `sample-documents/bank-statements/`

### Step 2: Create Expected Results JSON

For each PDF file, create a corresponding `.json` file with the same base name.

**Example:**
- PDF: `sample-documents/invoices/invoice-ageras-492.pdf`
- JSON: `sample-documents/invoices/invoice-ageras-492.json`

### Step 3: Fill in the Expected Data

1. Open the PDF and manually extract the data
2. Create a JSON file using the format specifications above
3. Ensure all data types are correct:
   - Strings for text fields
   - Floats for amounts (use decimals, not commas)
   - Dates in YYYY-MM-DD format
   - Use `null` for missing optional fields (not empty strings)

### Step 4: Validate Your JSON

Make sure your JSON is valid:
```bash
# Using jq (if available)
jq . sample-documents/invoices/your-file.json

# Or use any online JSON validator
```

### Step 5: Run the Tests

Run the importer tests to verify everything works:
```bash
./vendor/bin/sail artisan test --filter=ImporterTest
```

---

## Testing Strategy

### Comprehensive Coverage

Create test data that covers:

1. **Common Cases**
   - Standard invoices with all fields populated
   - Simple quotes with basic items
   - Regular bank statements with typical transactions

2. **Edge Cases**
   - Invoices with missing optional fields
   - Multiple line items
   - Different VAT rates (0%, 7%, 19%)
   - Compact date formats (e.g., "19.-26.5.25")
   - Long customer names with legal suffixes

3. **Format Variations**
   - Different date formats in PDFs
   - Various number formats (German vs. international)
   - Different project information layouts
   - Multiple bank types (Solaris, Kontist, etc.)

### Naming Conventions

Use descriptive names for your test files:

**Good:**
- `invoice-ageras-492-project-type.pdf`
- `invoice-simple-no-project-fields.pdf`
- `quote-multi-items-with-notes.pdf`
- `bank-statement-kontist-2025-09.pdf`

**Avoid:**
- `test1.pdf`
- `invoice.pdf`
- `example.pdf`

---

## Running Tests

### Run All Importer Tests
```bash
./vendor/bin/sail artisan test --filter=ImporterTest
```

### Run Specific Importer Tests
```bash
# Invoice importer tests
./vendor/bin/sail artisan test --filter=InvoiceImporterTest

# Quote importer tests
./vendor/bin/sail artisan test --filter=QuoteImporterTest

# Bank statement importer tests
./vendor/bin/sail artisan test --filter=BankStatementImporterTest
```

### Debugging Failed Tests

If a test fails:

1. Check the test output for which field(s) don't match
2. Verify your JSON file has the correct data types and format
3. Manually inspect the PDF to confirm the expected data
4. Check for typos in field names or values
5. Ensure date formats are YYYY-MM-DD
6. Verify amounts use decimal points (not commas)

---

## Example Test Data Files

See the `sample-documents/` directory for complete examples of:
- `invoices/invoice-example-project-type.json` - Project invoice with all fields
- `invoices/invoice-example-general.json` - General invoice without project fields
- `quotes/quote-example-standard.json` - Standard quote
- `bank-statements/kontist-example-2025-09.json` - Kontist bank statement

---

## Tips for Creating Quality Test Data

1. **Be Precise**: Extract data exactly as it appears in the PDF
2. **Test Edge Cases**: Include PDFs with unusual formatting or missing fields
3. **Use Real Examples**: Base test data on actual invoices/statements when possible
4. **Document Quirks**: Add comments in your JSON if the PDF has unusual formatting
5. **Keep It Organized**: Use consistent naming and directory structure
6. **Version Control**: Commit both PDFs and JSON files to git

---

## Troubleshooting

### Common Issues

**Problem:** Parser extracts wrong date
- **Solution:** Check if the PDF has multiple dates; ensure the JSON specifies which date should be extracted

**Problem:** Customer name not matching
- **Solution:** Verify the customer name appears after the date line in the PDF; check for legal suffixes (GmbH, AG, etc.)

**Problem:** Amounts don't match
- **Solution:** Ensure JSON uses decimal points (not commas); verify German number format conversion

**Problem:** Items array is empty
- **Solution:** Check if the PDF has a clear table structure; the parser may struggle with complex layouts

---

## Contributing

When adding new test cases:

1. Create both PDF and JSON files
2. Run tests to verify they pass
3. Document any special considerations in comments
4. Commit both files with a descriptive commit message
5. Update this documentation if you discover new edge cases or patterns

---

## Questions?

For questions about creating test data:
- Check the parser code in `app/Services/DocumentParser.php` and `app/Services/BankStatementParser.php`
- Review existing test files in `sample-documents/`
- Run the tests and examine the differences
- Consult the CLAUDE.md file for project-specific patterns
