<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Quote;
use Carbon\Carbon;

class QuotePreviewController extends Controller
{
    /**
     * Create a dummy quote for preview purposes
     */
    private function createDummyQuote(): Quote
    {
        // Create dummy customer
        $customer = new Customer([
            'name' => 'Max Mustermann',
            'email' => 'max@beispiel.de',
            'address' => [
                'street' => 'Beispielweg 45',
                'zip' => '10789',
                'city' => 'Berlin',
            ],
        ]);

        // Don't save to database, just create in memory
        $customer->id = 999999;

        // Create dummy quote
        $quote = new Quote([
            'quote_number' => 'ANG-2025-001',
            'type' => 'general',
            'issue_date' => Carbon::parse('2025-11-10'),
            'valid_until' => Carbon::parse('2025-12-10'),
            'items' => [
                [
                    'description' => 'Webentwicklung',
                    'quantity' => 40.00,
                    'unit' => 'Std.',
                    'unit_price' => 95.00,
                    'total' => 3800.00,
                ],
                [
                    'description' => 'Grafikdesign',
                    'quantity' => 10.00,
                    'unit' => 'Std.',
                    'unit_price' => 85.00,
                    'total' => 850.00,
                ],
            ],
            'subtotal' => 4650.00,
            'vat_rate' => 19,
            'vat_amount' => 883.50,
            'total' => 5533.50,
            'notes' => 'Vielen Dank für Ihr Interesse.',
        ]);

        // Set the customer relationship without saving
        $quote->setRelation('customer', $customer);
        $quote->customer_id = $customer->id;
        $quote->id = 999999;

        // Add subject for display (using setAttribute to avoid PHPStan error)
        $quote->setAttribute('subject', 'Webdesign-Leistungen');

        return $quote;
    }

    /**
     * Preview quote as HTML (for iframe embedding)
     */
    public function previewHtml()
    {
        $quote = $this->createDummyQuote();

        return view('pdfs.quote', [
            'quote' => $quote,
        ]);
    }

    /**
     * Preview quote as PDF
     */
    public function previewPdf()
    {
        $quote = $this->createDummyQuote();

        $pdf = $quote->generatePdf();

        return $pdf->stream('vorschau.pdf');
    }

    /**
     * Preview specific quote as HTML
     */
    public function showHtml(Quote $quote)
    {
        $quote->load('customer');

        return view('pdfs.quote', [
            'quote' => $quote,
        ]);
    }

    /**
     * Preview specific quote as PDF (stream, not download)
     */
    public function showPdf(Quote $quote)
    {
        $quote->load('customer');

        $pdf = $quote->generatePdf();

        return $pdf->stream($quote->getPdfFilename());
    }
}
