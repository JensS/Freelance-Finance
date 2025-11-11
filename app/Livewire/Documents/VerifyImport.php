<?php

namespace App\Livewire\Documents;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quote;
use App\Services\DocumentParser;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class VerifyImport extends Component
{
    public $parsedData = [];

    public $documentType = 'invoice'; // invoice, quote, expense, cash_receipt

    public $redirectRoute = 'invoices.index';

    // Customer fields
    public $customerId = null;

    public $customerName = '';

    public $customerEmail = '';

    public $customerStreet = '';

    public $customerCity = '';

    public $customerZip = '';

    public $customerTaxNumber = '';

    // Invoice/Quote fields
    public $issueDate = '';

    public $dueDate = '';

    public $validUntil = '';

    public $projectName = '';

    public $servicePeriodStart = '';

    public $servicePeriodEnd = '';

    public $serviceLocation = '';

    public $isProjectInvoice = false;

    public $items = [];

    public $subtotal = 0;

    public $vatRate = 19.0;

    public $vatAmount = 0;

    public $total = 0;

    public $notes = '';

    // Available customers for dropdown
    public $availableCustomers = [];

    protected $rules = [
        'customerName' => 'required|string|max:255',
        'customerEmail' => 'nullable|email',
        'issueDate' => 'required|date',
        'dueDate' => 'nullable|date|after_or_equal:issueDate',
        'validUntil' => 'nullable|date|after_or_equal:issueDate',
        'total' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        // Load parsed data from session
        $this->parsedData = session('import_data', []);

        // Determine document type from request or session
        $this->documentType = request('type', 'invoice');

        // Set redirect route based on document type
        $this->redirectRoute = match ($this->documentType) {
            'invoice' => 'invoices.index',
            'quote' => 'quotes.index',
            default => 'dashboard',
        };

        // Check if we have data to verify
        if (empty($this->parsedData)) {
            session()->flash('error', 'Keine Daten zum Verifizieren gefunden.');
            redirect()->route($this->redirectRoute);

            return;
        }

        // Load available customers
        $this->availableCustomers = Customer::orderBy('name')->get();

        // Populate fields from parsed data
        $this->populateFields();
    }

    private function populateFields()
    {
        // Customer data
        if (isset($this->parsedData['existing_customer']) && $this->parsedData['existing_customer']) {
            $this->customerId = $this->parsedData['existing_customer']->id;
            $this->customerName = $this->parsedData['existing_customer']->name;
            $this->customerEmail = $this->parsedData['existing_customer']->email ?? '';
            $this->customerStreet = $this->parsedData['existing_customer']->street ?? '';
            $this->customerCity = $this->parsedData['existing_customer']->city ?? '';
            $this->customerZip = $this->parsedData['existing_customer']->zip ?? '';
            $this->customerTaxNumber = $this->parsedData['existing_customer']->tax_number ?? '';
        } else {
            $customerData = $this->parsedData['customer_data'] ?? [];
            $this->customerName = $customerData['name'] ?? '';
            $this->customerEmail = $customerData['email'] ?? '';
            $this->customerStreet = $customerData['street'] ?? '';
            $this->customerCity = $customerData['city'] ?? '';
            $this->customerZip = $customerData['zip'] ?? '';
            $this->customerTaxNumber = $customerData['tax_number'] ?? '';
        }

        // Document fields
        $this->issueDate = $this->parsedData['issue_date'] ?? now()->format('Y-m-d');
        $this->dueDate = $this->parsedData['due_date'] ?? now()->addDays(14)->format('Y-m-d');
        $this->validUntil = $this->parsedData['valid_until'] ?? now()->addDays(30)->format('Y-m-d');

        // Project fields
        $this->projectName = $this->parsedData['project_name'] ?? '';
        $this->servicePeriodStart = $this->parsedData['service_period_start'] ?? '';
        $this->servicePeriodEnd = $this->parsedData['service_period_end'] ?? '';
        $this->serviceLocation = $this->parsedData['service_location'] ?? '';
        $this->isProjectInvoice = $this->parsedData['is_project_invoice'] ?? false;

        // Financial data
        $this->items = $this->parsedData['items'] ?? [];
        $this->subtotal = $this->parsedData['subtotal'] ?? 0;
        $this->vatRate = $this->parsedData['vat_rate'] ?? 19.0;
        $this->vatAmount = $this->parsedData['vat_amount'] ?? 0;
        $this->total = $this->parsedData['total'] ?? 0;
        $this->notes = $this->parsedData['notes'] ?? '';
    }

    public function updatedCustomerId($value)
    {
        if ($value) {
            $customer = Customer::find($value);
            if ($customer) {
                $this->customerName = $customer->name;
                $this->customerEmail = $customer->email ?? '';
                $this->customerStreet = $customer->street ?? '';
                $this->customerCity = $customer->city ?? '';
                $this->customerZip = $customer->zip ?? '';
                $this->customerTaxNumber = $customer->tax_number ?? '';
            }
        }
    }

    public function confirmImport()
    {
        $this->validate();

        try {
            $parser = new DocumentParser;

            // Prepare data for import
            $importData = [
                'customer_data' => [
                    'name' => $this->customerName,
                    'email' => $this->customerEmail,
                    'street' => $this->customerStreet,
                    'city' => $this->customerCity,
                    'zip' => $this->customerZip,
                    'tax_number' => $this->customerTaxNumber,
                ],
                'issue_date' => $this->issueDate,
                'due_date' => $this->dueDate,
                'valid_until' => $this->validUntil,
                'project_name' => $this->projectName,
                'service_period_start' => $this->servicePeriodStart,
                'service_period_end' => $this->servicePeriodEnd,
                'service_location' => $this->serviceLocation,
                'is_project_invoice' => $this->isProjectInvoice,
                'items' => $this->items,
                'subtotal' => $this->subtotal,
                'vat_rate' => $this->vatRate,
                'vat_amount' => $this->vatAmount,
                'total' => $this->total,
                'notes' => $this->notes,
            ];

            // Use existing customer if selected
            if ($this->customerId) {
                $importData['existing_customer'] = Customer::find($this->customerId);
            }

            // Import based on document type
            if ($this->documentType === 'invoice') {
                $result = $parser->importDocument($importData, 'invoice');
                session()->flash('success', 'Rechnung erfolgreich importiert!');
            } elseif ($this->documentType === 'quote') {
                $result = $parser->importDocument($importData, 'quote');
                session()->flash('success', 'Angebot erfolgreich importiert!');
            }

            if (isset($result['error'])) {
                session()->flash('error', 'Fehler beim Import: '.$result['error']);

                return;
            }

            // Clear session data after successful import
            session()->forget('import_data');
            session()->forget('import_filename');

            return redirect()->route($this->redirectRoute);

        } catch (\Exception $e) {
            Log::error('Failed to import document after verification', [
                'document_type' => $this->documentType,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Fehler beim Import: '.$e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route($this->redirectRoute);
    }

    public function render()
    {
        return view('livewire.documents.verify-import');
    }
}
