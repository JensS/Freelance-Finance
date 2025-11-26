<?php

namespace Tests\Unit;

use App\Services\DocumentParser;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InvoiceImporterTest extends TestCase
{
    private DocumentParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new DocumentParser;
    }

    /**
     * Test that discovers all invoice PDF/JSON pairs in sample-documents/invoices
     * and validates the parser output against expected results
     *
     * @dataProvider invoiceProvider
     */
    public function test_invoice_parsing(string $pdfPath, string $jsonPath): void
    {
        // Check that both files exist
        $this->assertFileExists($pdfPath, "PDF file not found: $pdfPath");
        $this->assertFileExists($jsonPath, "JSON file not found: $jsonPath");

        // Load expected results
        $expectedJson = File::get($jsonPath);
        $expected = json_decode($expectedJson, true);

        $this->assertIsArray($expected, "Failed to parse JSON file: $jsonPath");
        $this->assertArrayHasKey('type', $expected, 'Expected JSON must have "type" field');
        $this->assertEquals('invoice', $expected['type'], 'Type must be "invoice"');

        // Parse the PDF
        $result = $this->parser->parseDocument($pdfPath);

        // Assert no errors
        $this->assertArrayNotHasKey('error', $result, 'Parser returned error: '.($result['error'] ?? ''));

        // Assert document type is correct
        $this->assertEquals('invoice', $result['type'], 'Document should be detected as invoice');

        // Assert all expected fields are present and match
        $this->assertInvoiceFieldsMatch($expected, $result, basename($pdfPath));
    }

    /**
     * Assert that invoice fields match expected values
     */
    private function assertInvoiceFieldsMatch(array $expected, array $actual, string $filename): void
    {
        // Basic fields
        $this->assertEquals(
            $expected['invoice_number'] ?? null,
            $actual['invoice_number'] ?? null,
            "[$filename] Invoice number mismatch"
        );

        $this->assertEquals(
            $expected['issue_date'] ?? null,
            $actual['issue_date'] ?? null,
            "[$filename] Issue date mismatch"
        );

        $this->assertEquals(
            $expected['due_date'] ?? null,
            $actual['due_date'] ?? null,
            "[$filename] Due date mismatch"
        );

        // Customer data
        if (isset($expected['customer_data'])) {
            $this->assertArrayHasKey('customer_data', $actual, "[$filename] Missing customer_data");

            foreach ($expected['customer_data'] as $key => $value) {
                $this->assertEquals(
                    $value,
                    $actual['customer_data'][$key] ?? null,
                    "[$filename] Customer data field '$key' mismatch"
                );
            }
        }

        // Project fields
        $this->assertEquals(
            $expected['project_name'] ?? null,
            $actual['project_name'] ?? null,
            "[$filename] Project name mismatch"
        );

        $this->assertEquals(
            $expected['service_period_start'] ?? null,
            $actual['service_period_start'] ?? null,
            "[$filename] Service period start mismatch"
        );

        $this->assertEquals(
            $expected['service_period_end'] ?? null,
            $actual['service_period_end'] ?? null,
            "[$filename] Service period end mismatch"
        );

        $this->assertEquals(
            $expected['service_location'] ?? null,
            $actual['service_location'] ?? null,
            "[$filename] Service location mismatch"
        );

        // Financial fields (with tolerance for floating point comparison)
        $this->assertEqualsWithDelta(
            $expected['subtotal'] ?? 0,
            $actual['subtotal'] ?? 0,
            0.01,
            "[$filename] Subtotal mismatch"
        );

        $this->assertEqualsWithDelta(
            $expected['vat_rate'] ?? 19.0,
            $actual['vat_rate'] ?? 19.0,
            0.01,
            "[$filename] VAT rate mismatch"
        );

        $this->assertEqualsWithDelta(
            $expected['vat_amount'] ?? 0,
            $actual['vat_amount'] ?? 0,
            0.01,
            "[$filename] VAT amount mismatch"
        );

        $this->assertEqualsWithDelta(
            $expected['total'] ?? 0,
            $actual['total'] ?? 0,
            0.01,
            "[$filename] Total mismatch"
        );

        // Items array
        if (isset($expected['items']) && is_array($expected['items'])) {
            $this->assertArrayHasKey('items', $actual, "[$filename] Missing items array");
            $this->assertCount(
                count($expected['items']),
                $actual['items'] ?? [],
                "[$filename] Items count mismatch"
            );

            foreach ($expected['items'] as $index => $expectedItem) {
                $actualItem = $actual['items'][$index] ?? null;
                $this->assertNotNull($actualItem, "[$filename] Missing item at index $index");

                $this->assertEquals(
                    $expectedItem['description'] ?? '',
                    $actualItem['description'] ?? '',
                    "[$filename] Item $index: description mismatch"
                );

                $this->assertEqualsWithDelta(
                    $expectedItem['quantity'] ?? 1,
                    $actualItem['quantity'] ?? 1,
                    0.01,
                    "[$filename] Item $index: quantity mismatch"
                );

                $this->assertEqualsWithDelta(
                    $expectedItem['unit_price'] ?? 0,
                    $actualItem['unit_price'] ?? 0,
                    0.01,
                    "[$filename] Item $index: unit_price mismatch"
                );

                $this->assertEqualsWithDelta(
                    $expectedItem['total'] ?? 0,
                    $actualItem['total'] ?? 0,
                    0.01,
                    "[$filename] Item $index: total mismatch"
                );
            }
        }

        // Notes (optional field)
        if (isset($expected['notes'])) {
            $this->assertEquals(
                $expected['notes'],
                $actual['notes'] ?? null,
                "[$filename] Notes mismatch"
            );
        }

        // Is project invoice flag
        if (isset($expected['is_project_invoice'])) {
            $this->assertEquals(
                $expected['is_project_invoice'],
                $actual['is_project_invoice'] ?? false,
                "[$filename] is_project_invoice flag mismatch"
            );
        }
    }

    /**
     * Data provider that discovers all invoice PDF/JSON pairs
     */
    public static function invoiceProvider(): array
    {
        $invoicesPath = dirname(__DIR__, 2).'/sample-documents/invoices';

        if (! is_dir($invoicesPath)) {
            return [];
        }

        $testCases = [];
        $pdfFiles = glob($invoicesPath.'/*.pdf') ?: [];

        foreach ($pdfFiles as $pdfPath) {
            $jsonPath = str_replace('.pdf', '.json', $pdfPath);

            // Only include if JSON file exists
            if (file_exists($jsonPath)) {
                $testName = basename($pdfPath, '.pdf');
                $testCases[$testName] = [$pdfPath, $jsonPath];
            }
        }

        return $testCases;
    }

    /**
     * Test that the parser correctly identifies non-invoice documents
     */
    public function test_rejects_non_invoice_documents(): void
    {
        $quotesPath = base_path('sample-documents/quotes');

        if (! File::isDirectory($quotesPath)) {
            $this->markTestSkipped('No quotes directory found');
        }

        $pdfFiles = File::glob($quotesPath.'/*.pdf');

        if (empty($pdfFiles)) {
            $this->markTestSkipped('No quote PDFs found for testing');
        }

        foreach ($pdfFiles as $pdfPath) {
            $result = $this->parser->parseDocument($pdfPath);

            // Should not be detected as invoice
            $this->assertNotEquals(
                'invoice',
                $result['type'] ?? '',
                'Quote should not be detected as invoice: '.basename($pdfPath)
            );
        }
    }
}
