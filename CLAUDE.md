# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Freelance Finance Hub** is a self-hosted Laravel-based accounting and invoicing system for creative freelancers. It handles invoice/quote creation, monthly tax document preparation, and financial analysis with AI-powered spending recommendations.

## Technology Stack

- **Backend**: Laravel 12 (PHP)
- **Database**: PostgreSQL 18 with JSON support
- **Docker**: Laravel Sail for development
- **Frontend**: Livewire 3 + Alpine.js (reactive UI without SPA complexity)
- **Styling**: Tailwind CSS 4.0
- **PDF Generation**: barryvdh/laravel-dompdf (with custom font support)
- **PDF Parsing**: smalot/pdfparser (for bank statement text extraction)
- **AI Integration**: Unified via Prism PHP (supports multiple providers)
  - **Library**: prism-php/prism (https://prismphp.com)
  - **Supported Providers**: Ollama, OpenAI, Anthropic, OpenRouter, Mistral, Groq, xAI, Gemini, DeepSeek
  - **Recommended Text Model (Ollama)**: gpt-oss:20b (supports thinking mode)
  - **Recommended Vision Model (Ollama)**: qwen2.5vl:3b (optimized for document extraction)
  - **Other Text Models**: llama3.2, mistral, codellama, qwen3, deepseek-r1
  - **Other Vision Models**: llama3.2-vision, llava, qwen2-vl, granite-3.2-vision
  - **Configuration**: Settings UI or .env via Prism config
- **Queue**: Database queue (Redis optional)

## Development Commands

### Setup & Installation
```bash
# Install dependencies
./vendor/bin/sail composer install
./vendor/bin/sail npm install

# Start Docker environment
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Seed default settings
./vendor/bin/sail artisan db:seed
```

### Development
```bash
# Build frontend assets
./vendor/bin/sail npm run dev

# Build for production
./vendor/bin/sail npm run build

# Run tests
./vendor/bin/sail artisan test

# Run specific test
./vendor/bin/sail artisan test --filter=InvoiceCreationTest

# Clear caches
./vendor/bin/sail artisan optimize:clear
```

### Code Quality
```bash
# Run Laravel Pint (code style fixer)
./vendor/bin/sail pint

# Run PHPStan (static analysis)
./vendor/bin/sail composer phpstan
```

## Core Architecture

### 1. Invoice & Quote Management
- **Purpose**: Create invoices and quotes with autocomplete for customers and line items
- **Key Features**:
  - Two invoice types: Project Invoice (with project details, service period, location) and General Invoice
  - Autocomplete learns new customers and line items automatically
  - Export to Paperless server as PDF with appropriate tags
- **Models**: `Invoice`, `Quote`, `Customer`, `InvoiceLineItem`
- **Livewire Components**: `CreateInvoice`, `CreateQuote`

### 2. Monthly Accounting Preparation
- **Purpose**: Prepare monthly tax documents for the tax advisor (due by 10th of each month)
- **Workflow**:
  1. Upload bank statement PDF (Kontist format)
  2. Parse transactions automatically
  3. Search Paperless for matching receipts using date (±7 day buffer) and merchant name
  4. Show validation page where user corrects parsed data and adds notes
  5. Allow adding cash receipts (not in bank statement)
  6. Generate monthly report PDF for tax advisor
- **Models**: `BankTransaction`, `Expense`, `CashReceipt`
- **Livewire Components**: `BankStatementUpload`, `TransactionValidation`, `MonthlyReportGenerator`

### 3. Financial Reporting & Analysis
- **Purpose**: Generate monthly and yearly financial reports with visualizations
- **Data Sources**:
  - Invoices from database (income)
  - Expenses from monthly accounting preparation
  - Annual income tax (user-entered)
- **Features**: Charts and graphs showing income/expense trends
- **Models**: `FinancialReport`, `TaxPayment`
- **Livewire Components**: `FinancialDashboard`, `ReportViewer`

### 4. AI-Powered Recommendations
- **Purpose**: Provide spending analysis and improvement suggestions
- **Process**:
  1. Send all transactions with user notes to local Ollama API
  2. AI analyzes spending patterns
  3. Display recommendations in UI
  4. Archive recommendations per month
- **Integration**: `App\Services\OllamaService`
- **Models**: `AiRecommendation`
- **Livewire Components**: `AiRecommendationsPanel`

## Bank Statement Parsing

### Kontist Bank Statement Format
The system parses Kontist bank statements (see `/sample-documents/`). Key fields:

- **Date**: Format DD.MM.YY
- **Correspondent**: Merchant/company name (first line of BUCHUNGSTEXT)
- **Type**: Pre-categorized by Kontist as:
  - `Geschäftsausgabe 0%` (business expense, 0% VAT - international services)
  - `Geschäftsausgabe 7%` (business expense, 7% VAT - reduced rate, includes restaurants/meals)
  - `Geschäftsausgabe 19%` (business expense, 19% VAT - standard rate)
  - `Einkommen 19%` (income with 19% VAT)
  - `Reverse Charge` (reverse charge mechanism for EU services)
  - `Privat` (private transaction - to be ignored)
  - `Umsatzsteuerstattung` (VAT refund)
  - `Steuerzahlung` (tax payment)
  - `Nicht kategorisiert` (not categorized)

**Note**: Restaurant and meal expenses (Bewirtung) are classified as `Geschäftsausgabe 7%` with a `[Bewirtung]` note added to the description for tax advisor reference.
- **Title**: Additional transaction details/reference
- **Amount**: Negative for expenses, positive for income (EUR)

### Validation Interface Requirements
After parsing, users must be able to:
- Review all extracted fields (Date, Correspondent, Title, Type)
- Correct any field EXCEPT Amount (read-only)
- Add a NOTE to each transaction
- Mark transactions as Private (to be excluded)

## Paperless Integration

### Server Details
- **URL**: http://128.140.41.24:8000/
- **API Docs**: https://docs.paperless-ngx.com/api/
- **Storage Path**: Configurable in Settings → Paperless Integration
  - Default: "Selbstständigkeit"
  - Automatically filters ALL document operations (search, upload, import) to this path
  - Can be changed to any storage path defined in your Paperless instance

### Document Classification
**Tags**:
- `Eingangsrechnung` (incoming invoice/expense receipt)
- `Ausgangsrechnung` (outgoing invoice - created by us)
- `Angebot` (quote)
- `Bank Statement` (monthly bank statement)
- `Bewirtung` (entertainment/meal receipt)
- `Barbeleg` (cash receipt)

**Document Types**:
- `Eingangsrechnungen` (incoming invoices - includes Barbeleg)
- `Ausgangsrechnungen` (outgoing invoices)
- `Kontoauszug` (bank statements)

### Invoice Matching Logic
When matching bank transactions to Paperless documents:
- **Date range**: Transaction date ±7 days (configurable buffer)
- **Merchant matching**: Fuzzy match on correspondent name
- **Critical**: Invoice dates and bank posting dates often differ due to processing delays
- **Best practice**: For monthly accounting, search invoices from ~5 days before month start to ~5 days after month end

Refer to `/Invoice_Collection_Knowledge_Base.md` for merchant name variations and common payment patterns.

## PDF Generation

### Invoice and Quote PDFs
Professional PDF templates are available matching the reference designs in `sample-documents/`.

**Features**:
- Clean, professional layout with company branding
- German formatting (dates, currency with comma decimals)
- Automatic company info from Settings
- Project information display (for project-type invoices/quotes)
- Line items table with calculations
- Legal text and payment terms
- Bank details footer

**Usage**:
```php
// Generate PDF
$invoice = Invoice::with('customer')->find($id);
$pdf = $invoice->generatePdf();

// Download
return $pdf->download($invoice->getPdfFilename());

// Stream to browser
return $pdf->stream();

// Save to storage
$pdf->save(storage_path("invoices/{$invoice->getPdfFilename()}"));
```

**Templates**:
- `resources/views/pdfs/invoice.blade.php` - Invoice PDF template
- `resources/views/pdfs/quote.blade.php` - Quote PDF template

Both use company settings from the database for dynamic content (address, bank details, tax info).

## Settings & Configuration

### Company Defaults (Configurable in UI)
Store in `settings` table as JSON:
```json
{
  "company_name": "Jens Sage",
  "company_address": {
    "street": "Your Street 1",
    "city": "Berlin",
    "zip": "10115"
  },
  "bank_details": {
    "iban": "DE1234567890",
    "bic": "BELADEBEXXX"
  },
  "tax_number": "12/345/67890",
  "eu_vat_id": "DE123456789",
  "vat_rate": 19,
  "paperless_storage_path": 123
}
```

### Paperless Storage Path Setting
- **Location**: Settings → Paperless Integration
- **Function**: Automatically filters all Paperless interactions to documents within the specified storage path
- **Applies to**:
  - Document uploads (invoices, quotes)
  - Document searches (expense matching, invoice lookup)
  - Expense document retrieval
- **How it works**: The system fetches available storage paths from your Paperless instance and displays them in a dropdown
- **Default behavior**: If no storage path is set, all documents across all paths will be included

### Environment Variables

**Note**: Paperless and Ollama settings are now configurable via Settings UI and stored in database. Environment variables below serve as defaults only.

```bash
# Ollama AI (configurable via Settings → Integrationen)
OLLAMA_API_URL=http://jens.pc.local:11434

# Paperless (configurable via Settings → Integrationen)
PAPERLESS_URL=http://128.140.41.24:8000/
PAPERLESS_API_TOKEN=your_token_here

# Database
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=freelance_finance
DB_USERNAME=sail
DB_PASSWORD=password
```

## Monthly Report Format

The monthly report PDF for the tax advisor should include:

1. **Income Section**: List all invoices received (Einkommen)
2. **Expense Section**:
   - Bank transactions (Geschäftsausgaben)
   - Cash receipts (Barbelege)
3. **For Each Transaction**:
   - Date
   - Correspondent
   - Amount
   - VAT rate (0%, 7%, 19%)
   - User note (if any)
   - Paperless storage path: `Selbstständigkeit/[filename]`
   - Paperless document ID for easy reference

**Purpose**: Make the tax advisor's life easier by providing organized, annotated financial data.

## AI Recommendation Prompts

When sending data to Ollama API:
- Include all transactions with user notes
- Request spending pattern analysis
- Ask for specific improvement suggestions
- Archive responses by month for historical tracking

Example prompt structure:
```
Analyze the following monthly expenses:
[Transaction list with notes]

Provide:
1. Spending pattern analysis
2. Areas of concern or overspending
3. Specific actionable recommendations for improvement
4. Budget suggestions for next month
```

## AI Vision Models for Receipt Extraction

The system uses Ollama vision models to automatically extract data from PDF receipts during transaction verification.

### Supported Vision Models

**Recommended Model:**
1. **Qwen 2.5 VL 3B** (`qwen2.5vl:3b`) ⭐ **RECOMMENDED**
   - Latest Qwen vision-language model, optimized for document understanding
   - Excellent OCR and structured data extraction
   - Fast inference with 3B parameter size
   - Best for receipt, invoice, and quote extraction
   - Install: `ollama pull qwen2.5vl:3b`

**Alternative Models:**
2. **LLaVA** (`llava`)
   - Popular, high-performance vision model
   - Good for general-purpose visual and language understanding
   - Install: `ollama pull llava`

3. **Llama 3.2-Vision** (`llama3.2-vision`)
   - Meta's latest vision capabilities
   - Optimized for image reasoning and captioning
   - Install: `ollama pull llama3.2-vision`

4. **Qwen-VL** (`qwen2-vl`)
   - Powerful vision-language model from Qwen family
   - Strong visual reasoning capabilities
   - Install: `ollama pull qwen2-vl`

5. **Granite-3.2-Vision** (`granite-3.2-vision`)
   - Excellent for OCR and document interpretation
   - Lower RAM requirements
   - Install: `ollama pull granite-3.2-vision`

6. **MiniCPM-V** (`minicpm-v`)
   - Compact model with good performance
   - Runs efficiently on single GPU
   - Install: `ollama pull minicpm-v`

### Configuration

Models are selected via **Settings → Integrationen → Ollama AI Integration**:
- Text Model: For financial analysis and recommendations
- Vision Model: For automatic receipt data extraction

The system automatically categorizes installed models into text and vision types based on model name patterns.

### Extraction Process

1. User clicks "AI-Extraktion" button on verification page
2. System downloads PDF from Paperless
3. Converts first page to high-quality image (200 DPI)
4. Sends to Ollama with structured JSON prompt
5. Extracts: date, merchant, amounts (gross/net/VAT), description, transaction type
6. Populates form fields for user review

## AI Text Models & Thinking Mode

### Recommended Text Model

**GPT-OSS 20B** (`gpt-oss:20b`) ⭐ **RECOMMENDED**
- Open-source reasoning model optimized for financial analysis
- Supports "thinking mode" with three levels: low, medium (default), high
- Excellent for complex financial analysis and tax optimization suggestions
- ~11 GB download
- Install: `ollama pull gpt-oss:20b`

### Thinking Mode

The system automatically enables "thinking mode" for text model prompts when supported:

**How it works:**
- Most models use `"think": true` for enhanced reasoning
- GPT-OSS requires `"think": "low"/"medium"/"high"` (default: "medium")
- Thinking mode is enabled by default in `OllamaService::generate()`
- Can be disabled by passing `enableThinking: false`

**Supported Models:**
- **gpt-oss** (requires "low"/"medium"/"high")
- **qwen**, **qwen2**, **qwen3**, **qwq** (boolean)
- **llama3.x** (boolean)
- **mistral** variants (boolean)
- **deepseek-r1** (boolean)
- **gemma** variants (boolean)

**Example API Request:**
```json
{
  "model": "gpt-oss:20b",
  "prompt": "Analyze this month's expenses...",
  "think": "medium",
  "stream": false
}
```

**Benefits:**
- Improved reasoning quality for financial analysis
- Better tax optimization suggestions
- More accurate transaction categorization
- Enhanced anomaly detection

### Model Installation UI

The Settings page (Integrationen → Ollama) automatically detects missing recommended models and shows:
- Warning card with model name
- One-click install button
- Progress indicator during download
- Automatic model selection after installation

Users can click "Installieren" to pull the recommended models without terminal access.

## Authentication & Authorization

Multi-user authentication with role-based access control:

### User Roles
Two roles are available:
- **Owner (Inhaber)**: Full access to all features including invoices, quotes, customers, settings, and user management
- **Tax Accountant (Steuerberater)**: Limited access to accounting, transaction verification, reports, and Paperless documents

### Authentication
- Email + password authentication using Laravel's built-in Auth system
- Session-based with remember token support
- Login page: Livewire component `App\Livewire\Auth\Login`
- Middleware: `App\Http\Middleware\EnsureUserHasRole`

### Role Enum
Located at `App\Enums\Role`:
- `Role::Owner` - Admin role with full access
- `Role::TaxAccountant` - Limited access for tax advisors

### User Management
- Only Owners can manage users
- Available at `/users` route
- Owners cannot delete themselves or demote themselves from Owner role

### Initial Setup
Run the seeder to create the initial admin account:
```bash
./vendor/bin/sail artisan db:seed --class=UserSeeder
```
Default credentials (change after first login!):
- Email: admin@example.com
- Password: password

### Access Control by Route
**Owner only:**
- `/invoices/*` - Invoice management
- `/quotes/*` - Quote management
- `/customers/*` - Customer management
- `/settings/*` - System settings
- `/users/*` - User management

**Owner + Tax Accountant:**
- `/dashboard` - Dashboard
- `/accounting/*` - Monthly accounting
- `/transactions/*` - Transaction verification
- `/reports/*` - Financial reports
- `/paperless/*` - Paperless document access

## Important Notes

1. **Invoice Numbering**: Ensure sequential invoice numbers are maintained (for German tax compliance)
2. **Date Buffers**: Always use ±7 day buffer when matching transactions to invoices
3. **Private Transactions**: Must be excluded from tax reports but logged for reconciliation
4. **VAT Handling**: Correctly differentiate between 0% (international), 7% (reduced), and 19% (standard) rates
5. **Reverse Charge**: EU B2B services follow reverse charge mechanism (no VAT charged)
6. **PDF Templates**: Invoice PDFs must include all legally required information (company details, tax numbers, payment terms)
7. **Paperless Tags**: Always use correct tag combinations (e.g., Eingangsrechnung + Geschäftsausgabe)

## Sample Documents

Reference documents in `/sample-documents/`:
- `Ageras_GmbH_Kontist_Kontoauszug_9_2025.pdf` - Bank statement format
- `2025-08-12 Jens Sage Rechnung Sage 503.pdf` - Outgoing invoice example
- `251021-Jens Sage-0001077.pdf` - Another invoice example
- `Angebot 2025-PC-Sust-Vid-v1.pdf` - Quote example
