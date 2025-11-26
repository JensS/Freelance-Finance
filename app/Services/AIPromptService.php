<?php

namespace App\Services;

use App\Models\KnowledgeBaseEntry;

/**
 * Centralized AI prompt generation service
 *
 * This service provides prompts for AI-powered document extraction.
 * Prompts are provider-agnostic and can be used with any vision AI service
 * (Ollama, OpenAI, Anthropic, OpenRouter, etc.)
 */
class AIPromptService
{
    /**
     * Build prompt for receipt/expense extraction
     *
     * @param  array  $knownCorrespondents  List of known correspondent names from Paperless
     * @return string The prompt
     */
    public function buildReceiptExtractionPrompt(array $knownCorrespondents = []): string
    {
        $correspondentHint = '';
        if (! empty($knownCorrespondents)) {
            $correspondentList = implode(', ', array_slice($knownCorrespondents, 0, 50)); // Limit to 50
            $correspondentHint = "\n\n**Known Correspondents/Merchants:**\nWhen identifying the correspondent/merchant, try to match against these known businesses from our system:\n{$correspondentList}\n\nIf the merchant on the receipt closely matches one of these names, use the exact name from the list. Otherwise, extract the exact merchant name from the receipt.";
        }

        // Add knowledge base context
        $receiptSourcesContext = KnowledgeBaseEntry::getReceiptSourcesForAI();
        $noteTemplatesContext = KnowledgeBaseEntry::getNoteTemplatesForAI();

        $knowledgeBaseHint = '';
        if (! empty($receiptSourcesContext) || ! empty($noteTemplatesContext)) {
            $knowledgeBaseHint = "\n\n**Additional Context:**";
            if (! empty($receiptSourcesContext)) {
                $knowledgeBaseHint .= "\n\n{$receiptSourcesContext}";
            }
            if (! empty($noteTemplatesContext)) {
                $knowledgeBaseHint .= "\n\n{$noteTemplatesContext}";
            }
        }

        return <<<PROMPT
You are analyzing a receipt or invoice image for tax documents in Germany. Extract the following information and return it in TOON format (Token-Oriented Object Notation) - a compact, structured format that's more efficient than JSON.{$correspondentHint}{$knowledgeBaseHint}

**CRITICAL - You must extract ALL of the following fields:**

- **date**: Transaction date in DD.MM.YYYY format (e.g., "15.03.2024")
- **correspondent**: Merchant or company name (the business that issued the receipt)
- **amount_gross**: TOTAL AMOUNT shown on the receipt (Bruttobetrag/Gesamtbetrag/Summe) as POSITIVE decimal number (e.g., 119.00)
- **vat_rate**: VAT rate as percentage number only (e.g., 19, 7, or 0)
- **description**: Brief description of what was purchased or the service provided
- **transaction_type**: Classify as one of:
  - "Geschäftsausgabe 19%" (business expense with 19% VAT - standard rate in Germany)
  - "Geschäftsausgabe 7%" (business expense with 7% VAT - reduced rate, includes restaurants/meals)
  - "Geschäftsausgabe 0%" (business expense with 0% VAT - international services)
  - "Bewirtung" (entertainment/meal expense - restaurants, cafes, business meals)
  - "Einkommen 19%" (income with 19% VAT)
  - "Privat" (private/personal expense)
- **is_bewirtung**: Boolean true/false - Set to true ONLY if this is a Bewirtungsbeleg (receipt from restaurant, cafe, bar showing food/drinks consumed)

**Bewirtung Fields (ONLY fill if is_bewirtung is true):**
- **bewirtete_person**: Name(s) of person(s) being entertained/hosted (extract from receipt if visible, otherwise null)
- **anlass**: Business occasion/reason for the meal - BE SPECIFIC (e.g., "Projektbesprechung für Website-Redesign", "Vertragsverhandlung mit Kunde XYZ"). Generic terms like "Arbeitsgespräch" are NOT sufficient per German tax law.
- **ort**: Location/venue of the meal (restaurant name and city, e.g., "Restaurant Maximilians, Berlin")

**CRITICAL Instructions for Amount Extraction:**
1. **Extract amount_gross and vat_rate** - the system will calculate net and VAT amounts from these
2. Look for these German labels on receipts:
   - "Bruttobetrag", "Gesamtbetrag", "Summe", "Total", "Gesamt", "Zu zahlen" = amount_gross
   - "inkl. MwSt.", "inkl. 19% MwSt.", "zzgl. 19% USt." = indicates gross amount includes VAT
   - "MwSt.-Satz", "Steuersatz", "USt.-Satz" followed by "19%", "7%", "0%" = vat_rate
   - "19% MwSt", "7% USt" = vat_rate
3. **amount_gross is the TOTAL PAID/TO PAY** - the final amount on the receipt
4. **amount_gross must ALWAYS be POSITIVE** - receipts show positive values, never negative
5. German receipts use comma as decimal separator (119,00) - CONVERT to period (119.00)
6. Remove currency symbols - output numbers only (e.g., 119.00 not "119,00 EUR")
7. **VAT rate detection:**
   - Look for "19%", "7%", "0%" near "MwSt", "USt", "Mehrwertsteuer", "Umsatzsteuer"
   - If not explicitly shown, assume 19% for most receipts, 7% for restaurants/meals
   - International receipts often have 0% VAT
8. Return amount_gross as a number with 2 decimal places (e.g., 119.00, not 119)

**Other Instructions:**
9. For international services or EU B2B, use 0% VAT
10. For Bewirtung (restaurants/meals/cafes), set transaction_type to "Bewirtung" and is_bewirtung to true
11. Bewirtung requires SPECIFIC occasion details - not generic terms. Examples of GOOD occasions: "Projektbesprechung Website-Relaunch mit Kunde ABC", "Vertragsverhandlung für Videoproduktion Q2 2025"
12. Only set is_bewirtung to true for actual restaurant/cafe receipts showing food or beverages consumed
13. If bewirtete_person is not visible on receipt, set to null (user will fill manually)
14. Extract ort (location) from the receipt - typically the restaurant name and city
15. If a field cannot be determined after calculation, set it to null
16. Return ONLY valid TOON format, no additional text or explanation

**TOON Format Rules:**
- Use `key: value` for simple fields
- Use indentation for nested objects
- No quotes needed for simple strings
- Numbers without quotes
- Booleans as true/false
- null for empty values

**Example Response (Regular Expense):**
date: 15.03.2024
correspondent: Amazon EU S.à.r.l.
amount_gross: 119.00
vat_rate: 19
description: Office supplies and computer equipment
transaction_type: Geschäftsausgabe 19%
is_bewirtung: false
bewirtete_person: null
anlass: null
ort: null

**Example for Bewirtung (Restaurant/Business Meal):**
date: 20.03.2024
correspondent: Restaurant Maximilians
amount_gross: 85.60
vat_rate: 7
description: Business lunch - 2 main courses, beverages
transaction_type: Bewirtung
is_bewirtung: true
bewirtete_person: null
anlass: null
ort: Restaurant Maximilians, Berlin

Now analyze the receipt image and extract the information:
PROMPT;
    }

    /**
     * Build prompt for outgoing invoice extraction
     *
     * @param  array  $knownCorrespondents  List of known correspondent names from Paperless
     * @return string The prompt
     */
    public function buildInvoiceExtractionPrompt(array $knownCorrespondents = []): string
    {
        $correspondentHint = '';
        if (! empty($knownCorrespondents)) {
            $correspondentList = implode(', ', array_slice($knownCorrespondents, 0, 50)); // Limit to 50
            $correspondentHint = "\n\n**Known Customers/Clients:**\nWhen identifying the customer/client name, try to match against these known businesses from our system:\n{$correspondentList}\n\nIf the customer on the invoice closely matches one of these names, use the exact name from the list. Otherwise, extract the exact customer name from the invoice.";
        }

        return <<<PROMPT
You are analyzing an invoice (Rechnung) image for tax documents in Germany. Extract the following information and return it in TOON format (Token-Oriented Object Notation) - a compact, structured format that's more efficient than JSON.{$correspondentHint}

**Required Fields:**
- **invoice_number**: The invoice/receipt number (e.g., "503", "RE-2025-001")
- **customer_name**: The customer/client company name
- **customer_address**: Full customer address including street, postal code, and city
- **issue_date**: Invoice issue date in DD.MM.YYYY or DD.MM.YY format (e.g., "12.08.25")
- **due_date**: Payment due date in DD.MM.YYYY or DD.MM.YY format (if mentioned, otherwise null)
- **project_name**: Project name or description if mentioned
- **service_period_start**: Service period start date in DD.MM.YYYY format (e.g., "4.8.25", if mentioned)
- **service_period_end**: Service period end date in DD.MM.YYYY format (e.g., "9.8.25", if mentioned)
- **service_location**: Service location/place (e.g., "Frankfurt", "Berlin", if mentioned)
- **items**: Array of line items, each with:
  - **description**: Item description (keep German umlauts and special characters)
  - **quantity**: Quantity as number (e.g., 3, 1, 2.5)
  - **unit_price**: Unit price as decimal number (e.g., 2000.00)
  - **total**: Total price for this line as decimal number (e.g., 6000.00)
- **subtotal**: Net total amount (Nettobetrag) as decimal number
- **vat_rate**: VAT percentage (e.g., 19, 7, 0)
- **vat_amount**: VAT amount (MwSt) as decimal number
- **total**: Gross total amount (Gesamtbetrag) as decimal number
- **notes**: Any additional notes, payment terms, or special instructions

**Important Instructions:**
1. Amounts must be decimal numbers without currency symbols (e.g., 10251.85 not "10.251,85 €")
2. German invoices use comma as decimal separator (10.251,85) - convert to period (10251.85)
3. German invoices use period as thousands separator (10.251,85) - remove it (10251.85)
4. Dates should be in DD.MM.YYYY or DD.MM.YY format as shown in the document
5. Keep all German text, umlauts (ä, ö, ü, ß) and special characters intact in descriptions
6. Extract ALL line items from the invoice table
7. If a field cannot be determined, set it to null
8. Return ONLY valid TOON format, no additional text

**TOON Format Rules:**
- Use `key: value` for simple fields
- Use tabular format for arrays of objects: `items[n]{field1,field2,...}:`
- Numbers without quotes
- null for empty values

**Example Response:**
invoice_number: 503
customer_name: Sahler Werbung GmbH & Co. KG
customer_address: Berliner Allee 2, 40212 Düsseldorf
issue_date: 12.08.25
due_date: 14.08.25
project_name: Outerwear / RUSH Kampagne
service_period_start: 4.8.25
service_period_end: 9.8.25
service_location: Frankfurt
items[2]{description,quantity,unit_price,total}:
  Director creative fee / Gage,3,2000.00,6000.00
  Kameratechnik: A Kamera (Alexa Mini, Objektivsatz, etc.),1,1500.00,1500.00
subtotal: 8615.00
vat_rate: 19
vat_amount: 1636.85
total: 10251.85
notes: Vielen Dank für die angenehme Zusammenarbeit! Die Rechnungssumme ist fällig mit Zugang dieser Rechnung.

Now analyze the invoice image and extract the information:
PROMPT;
    }

    /**
     * Build prompt for quote/proposal extraction
     *
     * @param  array  $knownCorrespondents  List of known correspondent names from Paperless
     * @return string The prompt
     */
    public function buildQuoteExtractionPrompt(array $knownCorrespondents = []): string
    {
        $correspondentHint = '';
        if (! empty($knownCorrespondents)) {
            $correspondentList = implode(', ', array_slice($knownCorrespondents, 0, 50)); // Limit to 50
            $correspondentHint = "\n\n**Known Customers/Clients:**\nWhen identifying the customer/client name, try to match against these known businesses from our system:\n{$correspondentList}\n\nIf the customer on the quote closely matches one of these names, use the exact name from the list. Otherwise, extract the exact customer name from the quote.";
        }

        return <<<PROMPT
You are analyzing a quote/proposal (Angebot) image for tax documents in Germany. Extract the following information and return it in TOON format (Token-Oriented Object Notation) - a compact, structured format that's more efficient than JSON.{$correspondentHint}

**Required Fields:**
- **quote_number**: The quote/proposal number (e.g., "2025-P&C-Sustainability-Video-v1", "Q-2025-001")
- **customer_name**: The customer/client company name
- **customer_address**: Full customer address including street, postal code, and city
- **issue_date**: Quote issue date in DD.MM.YYYY or DD.MM.YY format (e.g., "21.01.25")
- **valid_until**: Quote validity/expiration date in DD.MM.YYYY format (if mentioned, otherwise null)
- **project_name**: Project name or description
- **brief**: Project brief or description (if mentioned as separate section)
- **service_period**: Service period description (e.g., "Q1 2025", if mentioned)
- **service_location**: Service location/place (e.g., "Berlin, DE", if mentioned)
- **items**: Array of line items, each with:
  - **description**: Item description (keep German umlauts and special characters)
  - **quantity**: Quantity as number (e.g., 3, 1, 2.5)
  - **unit_price**: Unit price as decimal number (e.g., 2000.00)
  - **total**: Total price for this line as decimal number (e.g., 6000.00)
- **subtotal**: Net total amount (Nettobetrag) as decimal number
- **vat_rate**: VAT percentage (e.g., 19, 7, 0)
- **vat_amount**: VAT amount (MwSt) as decimal number
- **total**: Gross total amount (Gesamtbetrag) as decimal number
- **notes**: Any additional notes or closing remarks

**Important Instructions:**
1. Amounts must be decimal numbers without currency symbols (e.g., 21646.10 not "21.646,10 €")
2. German quotes use comma as decimal separator (21.646,10) - convert to period (21646.10)
3. German quotes use period as thousands separator (21.646,10) - remove it (21646.10)
4. Dates should be in DD.MM.YYYY or DD.MM.YY format as shown in the document
5. Keep all German text, umlauts (ä, ö, ü, ß) and special characters intact in descriptions
6. Extract ALL line items from the quote table
7. If a field cannot be determined, set it to null
8. Return ONLY valid TOON format, no additional text

**TOON Format Rules:**
- Use `key: value` for simple fields
- Use tabular format for arrays of objects: `items[n]{field1,field2,...}:`
- Numbers without quotes
- null for empty values

**Example Response:**
quote_number: 2025-P&C-Sustainability-Video-v1
customer_name: vbc Agency GmbH
customer_address: Meinekestr. 12, 10719 Berlin
issue_date: 21.01.25
valid_until: null
project_name: P&C Sustainability Corporate Video
brief: 90-120 sekündiges Video zum Thema Sustainability...
service_period: Q1 2025
service_location: Berlin, DE
items[2]{description,quantity,unit_price,total}:
  Produktion: DP & Director creative fee,1,2000.00,2000.00
  Produktion: Crew: Assistent (1 Drehtag + An- und Abreise),2,450.00,900.00
subtotal: 18190.00
vat_rate: 19
vat_amount: 3456.10
total: 21646.10
notes: Vielen Dank für das Interesse an einer Zusammenarbeit!

Now analyze the quote image and extract the information:
PROMPT;
    }
}
