<?php

namespace App\Livewire\Invoices;

use App\Models\Customer;
use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Rechnung bearbeiten')]
class Edit extends Component
{
    public Invoice $invoice;

    // Customer search
    public string $customerSearch = '';

    public array $searchResults = [];

    public ?int $customer_id = null;

    public ?Customer $selectedCustomer = null;

    // Invoice details
    public string $type = 'general';

    public string $project_name = '';

    public string $service_period_start = '';

    public string $service_period_end = '';

    public string $service_location = '';

    public string $issue_date = '';

    public string $due_date = '';

    public float $vat_rate = 19.0;

    public string $notes = '';

    // Line items
    public array $items = [];

    // Calculated values
    public float $subtotal = 0;

    public float $vat_amount = 0;

    public float $total = 0;

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice->load('customer');

        // Load invoice data
        $this->customer_id = $invoice->customer_id;
        /** @var Customer|null $customer */
        $customer = $invoice->customer;
        $this->selectedCustomer = $customer;
        $this->customerSearch = $customer !== null ? $customer->name : '';

        $this->type = $invoice->type;
        $this->project_name = $invoice->project_name ?? '';
        $this->service_period_start = $invoice->service_period_start?->format('Y-m-d') ?? '';
        $this->service_period_end = $invoice->service_period_end?->format('Y-m-d') ?? '';
        $this->service_location = $invoice->service_location ?? '';
        $this->issue_date = $invoice->issue_date->format('Y-m-d');
        $this->due_date = $invoice->due_date->format('Y-m-d');
        $this->vat_rate = (float) $invoice->vat_rate;
        $this->notes = $invoice->notes ?? '';

        // Load line items
        $this->items = $invoice->items;

        // Calculate totals
        $this->calculateTotals();
    }

    public function updatedCustomerSearch()
    {
        if (strlen($this->customerSearch) < 2) {
            $this->searchResults = [];

            return;
        }

        $this->searchResults = Customer::query()
            ->where('name', 'like', '%'.$this->customerSearch.'%')
            ->orWhere('email', 'like', '%'.$this->customerSearch.'%')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function selectCustomer(int $customerId)
    {
        $this->customer_id = $customerId;
        /** @var Customer|null $customer */
        $customer = Customer::find($customerId);
        $this->selectedCustomer = $customer;
        $this->customerSearch = $customer !== null ? $customer->name : '';
        $this->searchResults = [];
    }

    public function clearCustomer()
    {
        $this->customer_id = null;
        $this->selectedCustomer = null;
        $this->customerSearch = '';
        $this->searchResults = [];
    }

    public function addItem()
    {
        $this->items[] = [
            'description' => '',
            'quantity' => 1,
            'unit' => 'Std',
            'unit_price' => 0,
            'total' => 0,
        ];
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function updatedItems()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;

        foreach ($this->items as &$item) {
            $item['total'] = $item['quantity'] * $item['unit_price'];
            $this->subtotal += $item['total'];
        }

        $this->vat_amount = $this->subtotal * ($this->vat_rate / 100);
        $this->total = $this->subtotal + $this->vat_amount;
    }

    public function save()
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:project,general',
            'project_name' => 'required_if:type,project|nullable|string|max:255',
            'service_period_start' => 'required_if:type,project|nullable|date',
            'service_period_end' => 'required_if:type,project|nullable|date|after_or_equal:service_period_start',
            'service_location' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Calculate totals one more time to be sure
        $this->calculateTotals();

        // Update the invoice
        $this->invoice->update([
            'customer_id' => $this->customer_id,
            'type' => $this->type,
            'project_name' => $this->project_name ?: null,
            'service_period_start' => $this->service_period_start ?: null,
            'service_period_end' => $this->service_period_end ?: null,
            'service_location' => $this->service_location ?: null,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'vat_rate' => $this->vat_rate,
            'vat_amount' => $this->vat_amount,
            'total' => $this->total,
            'notes' => $this->notes ?: null,
        ]);

        session()->flash('success', 'Rechnung erfolgreich aktualisiert.');

        return redirect()->route('invoices.index');
    }

    public function render()
    {
        return view('livewire.invoices.edit');
    }
}
