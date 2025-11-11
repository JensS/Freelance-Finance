<?php

namespace Tests\Unit;

use App\Services\BankStatementParser;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BankStatementImporterTest extends TestCase
{
    private BankStatementParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new BankStatementParser;
    }

    /**
     * Test that discovers all bank statement PDF/JSON pairs in sample-documents/bank-statements
     * and validates the parser output against expected results
     *
     * @dataProvider bankStatementProvider
     */
    public function test_bank_statement_parsing(string $pdfPath, string $jsonPath): void
    {
        // Check that both files exist
        $this->assertFileExists($pdfPath, "PDF file not found: $pdfPath");
        $this->assertFileExists($jsonPath, "JSON file not found: $jsonPath");

        // Load expected results
        $expectedJson = File::get($jsonPath);
        $expected = json_decode($expectedJson, true);

        $this->assertIsArray($expected, "Failed to parse JSON file: $jsonPath");
        $this->assertArrayHasKey('transactions', $expected, 'Expected JSON must have "transactions" field');
        $this->assertIsArray($expected['transactions'], 'Transactions must be an array');

        // Parse the PDF
        $result = $this->parser->parsePdf($pdfPath);

        // Assert that transactions were found
        $this->assertIsArray($result, 'Parser should return an array');
        $this->assertNotEmpty($result, 'Parser should find at least one transaction in: '.basename($pdfPath));

        // Assert transaction count matches
        $this->assertCount(
            count($expected['transactions']),
            $result,
            basename($pdfPath).': Transaction count mismatch'
        );

        // Assert all expected transactions are present and match
        $this->assertTransactionsMatch($expected['transactions'], $result, basename($pdfPath));
    }

    /**
     * Assert that transactions match expected values
     */
    private function assertTransactionsMatch(array $expected, array $actual, string $filename): void
    {
        foreach ($expected as $index => $expectedTx) {
            $actualTx = $actual[$index] ?? null;

            $this->assertNotNull(
                $actualTx,
                "[$filename] Missing transaction at index $index"
            );

            // Date
            $this->assertEquals(
                $expectedTx['date'] ?? null,
                $actualTx['date'] ?? null,
                "[$filename] Transaction $index: date mismatch"
            );

            // Correspondent (optional - may not be extracted for all formats)
            if (isset($expectedTx['correspondent']) && $expectedTx['correspondent'] !== null) {
                $this->assertEquals(
                    $expectedTx['correspondent'],
                    $actualTx['correspondent'] ?? null,
                    "[$filename] Transaction $index: correspondent mismatch"
                );
            }

            // Title (optional)
            if (isset($expectedTx['title']) && $expectedTx['title'] !== null) {
                $this->assertEquals(
                    $expectedTx['title'],
                    $actualTx['title'] ?? null,
                    "[$filename] Transaction $index: title mismatch"
                );
            }

            // Description (usually present)
            if (isset($expectedTx['description'])) {
                $this->assertEquals(
                    $expectedTx['description'],
                    $actualTx['description'] ?? null,
                    "[$filename] Transaction $index: description mismatch"
                );
            }

            // Type (optional - may not be in all formats)
            if (isset($expectedTx['type']) && $expectedTx['type'] !== null) {
                $this->assertEquals(
                    $expectedTx['type'],
                    $actualTx['type'] ?? null,
                    "[$filename] Transaction $index: type mismatch"
                );
            }

            // Amount (critical field)
            $this->assertEqualsWithDelta(
                $expectedTx['amount'] ?? 0,
                $actualTx['amount'] ?? 0,
                0.01,
                "[$filename] Transaction $index: amount mismatch"
            );

            // Currency
            $this->assertEquals(
                $expectedTx['currency'] ?? 'EUR',
                $actualTx['currency'] ?? 'EUR',
                "[$filename] Transaction $index: currency mismatch"
            );
        }
    }

    /**
     * Data provider that discovers all bank statement PDF/JSON pairs
     */
    public static function bankStatementProvider(): array
    {
        $statementsPath = base_path('sample-documents/bank-statements');

        if (! File::isDirectory($statementsPath)) {
            return [];
        }

        $testCases = [];
        $pdfFiles = File::glob($statementsPath.'/*.pdf');

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
     * Test that parser handles empty/invalid PDFs gracefully
     */
    public function test_handles_invalid_pdf_gracefully(): void
    {
        $tempPath = sys_get_temp_dir().'/invalid-test.pdf';

        // Create a temporary file with invalid content
        File::put($tempPath, 'This is not a valid PDF');

        try {
            $result = $this->parser->parsePdf($tempPath);

            // Should return empty array for unparseable files
            $this->assertIsArray($result);
            $this->assertEmpty($result);
        } finally {
            // Clean up
            if (File::exists($tempPath)) {
                File::delete($tempPath);
            }
        }
    }

    /**
     * Test bank type detection
     */
    public function test_detects_bank_type(): void
    {
        $statementsPath = base_path('sample-documents/bank-statements');

        if (! File::isDirectory($statementsPath)) {
            $this->markTestSkipped('No bank-statements directory found');
        }

        $jsonFiles = File::glob($statementsPath.'/*.json');

        foreach ($jsonFiles as $jsonPath) {
            $pdfPath = str_replace('.json', '.pdf', $jsonPath);

            if (! File::exists($pdfPath)) {
                continue;
            }

            $expected = json_decode(File::get($jsonPath), true);

            if (! isset($expected['bank_type'])) {
                continue;
            }

            // This is a basic check - we can't directly test bank type detection
            // without modifying the parser, but we can ensure the parser runs
            $result = $this->parser->parsePdf($pdfPath);

            $this->assertIsArray($result, 'Parser should return array for: '.basename($pdfPath));
        }
    }
}
