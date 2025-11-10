<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;

class InvoicePreviewController extends Controller
{
    /**
     * Create a dummy invoice for preview purposes
     */
    private function createDummyInvoice(): Invoice
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

        // Create dummy invoice
        $invoice = new Invoice([
            'invoice_number' => 'RE-2025-001',
            'type' => 'general',
            'issue_date' => Carbon::parse('2025-11-10'),
            'due_date' => Carbon::parse('2025-11-24'),
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
            'notes' => 'Vielen Dank für Ihren Auftrag.',
        ]);

        // Set the customer relationship without saving
        $invoice->setRelation('customer', $customer);
        $invoice->customer_id = $customer->id;
        $invoice->id = 999999;

        // Add subject for display
        $invoice->subject = 'Webdesign-Leistungen November 2025';

        return $invoice;
    }

    /**
     * Preview invoice as HTML (for iframe embedding)
     */
    public function previewHtml()
    {
        $invoice = $this->createDummyInvoice();

        return view('pdfs.invoice', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Preview invoice as PDF
     */
    public function previewPdf()
    {
        $invoice = $this->createDummyInvoice();

        $pdf = $invoice->generatePdf();

        return $pdf->stream('vorschau.pdf');
    }

    /**
     * Preview specific invoice as HTML
     */
    public function showHtml(Invoice $invoice)
    {
        $invoice->load('customer');

        return view('pdfs.invoice', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Preview specific invoice as PDF (stream, not download)
     */
    public function showPdf(Invoice $invoice)
    {
        $invoice->load('customer');

        $pdf = $invoice->generatePdf();

        return $pdf->stream($invoice->getPdfFilename());
    }
}
