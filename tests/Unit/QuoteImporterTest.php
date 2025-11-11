<?php

namespace Tests\Unit;

use App\Services\DocumentParser;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class QuoteImporterTest extends TestCase
{
    private DocumentParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new DocumentParser;
    }

    /**
     * Test that discovers all quote PDF/JSON pairs in sample-documents/quotes
     * and validates the parser output against expected results
     *
     * @dataProvider quoteProvider
     */
    public function test_quote_parsing(string $pdfPath, string $jsonPath): void
    {
        // Check that both files exist
        $this->assertFileExists($pdfPath, "PDF file not found: $pdfPath");
        $this->assertFileExists($jsonPath, "JSON file not found: $jsonPath");

        // Load expected results
        $expectedJson = File::get($jsonPath);
        $expected = json_decode($expectedJson, true);

        $this->assertIsArray($expected, "Failed to parse JSON file: $jsonPath");
        $this->assertArrayHasKey('type', $expected, 'Expected JSON must have "type" field');
        $this->assertEquals('quote', $expected['type'], 'Type must be "quote"');

        // Parse the PDF
        $result = $this->parser->parseDocument($pdfPath);

        // Assert no errors
        $this->assertArrayNotHasKey('error', $result, "Parser returned error: ".($result['error'] ?? ''));

        // Assert document type is correct
        $this->assertEquals('quote', $result['type'], 'Document should be detected as quote');

        // Assert all expected fields are present and match
        $this->assertQuoteFieldsMatch($expected, $result, basename($pdfPath));
    }

    /**
     * Assert that quote fields match expected values
     */
    private function assertQuoteFieldsMatch(array $expected, array $actual, string $filename): void
    {
        // Basic fields
        $this->assertEquals(
            $expected['quote_number'] ?? null,
            $actual['quote_number'] ?? null,
            "[$filename] Quote number mismatch"
        );

        $this->assertEquals(
            $expected['issue_date'] ?? null,
            $actual['issue_date'] ?? null,
            "[$filename] Issue date mismatch"
        );

        $this->assertEquals(
            $expected['valid_until'] ?? null,
            $actual['valid_until'] ?? null,
            "[$filename] Valid until date mismatch"
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

        // Project name
        $this->assertEquals(
            $expected['project_name'] ?? null,
            $actual['project_name'] ?? null,
            "[$filename] Project name mismatch"
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
    }

    /**
     * Data provider that discovers all quote PDF/JSON pairs
     */
    public static function quoteProvider(): array
    {
        $quotesPath = base_path('sample-documents/quotes');

        if (! File::isDirectory($quotesPath)) {
            return [];
        }

        $testCases = [];
        $pdfFiles = File::glob($quotesPath.'/*.pdf');

        foreach ($pdfFiles as $pdfPath) {
            $jsonPath = str_replace('.pdf', '.json', $pdfPath);

            // Only include if JSON file exists
            if (File::exists($jsonPath)) {
                $testName = basename($pdfPath, '.pdf');
                $testCases[$testName] = [$pdfPath, $jsonPath];
            }
        }

        return $testCases;
    }

    /**
     * Test that the parser correctly identifies non-quote documents
     */
    public function test_rejects_non_quote_documents(): void
    {
        $invoicesPath = base_path('sample-documents/invoices');

        if (! File::isDirectory($invoicesPath)) {
            $this->markTestSkipped('No invoices directory found');
        }

        $pdfFiles = File::glob($invoicesPath.'/*.pdf');

        if (empty($pdfFiles)) {
            $this->markTestSkipped('No invoice PDFs found for testing');
        }

        foreach ($pdfFiles as $pdfPath) {
            $result = $this->parser->parseDocument($pdfPath);

            // Should not be detected as quote
            $this->assertNotEquals(
                'quote',
                $result['type'] ?? '',
                'Invoice should not be detected as quote: '.basename($pdfPath)
            );
        }
    }
}
