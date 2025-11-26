<?php

namespace App\Livewire\Quotes;

use App\Models\Customer;
use App\Models\Quote;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Angebot bearbeiten')]
class Edit extends Component
{
    public Quote $quote;

    // Customer search
    public string $customerSearch = '';

    public array $searchResults = [];

    public ?int $customer_id = null;

    public ?Customer $selectedCustomer = null;

    // Quote details
    public string $type = 'general';

    public string $project_name = '';

    public string $issue_date = '';

    public string $valid_until = '';

    public float $vat_rate = 19.0;

    public string $notes = '';

    // Line items
    public array $items = [];

    // Calculated values
    public float $subtotal = 0;

    public float $vat_amount = 0;

    public float $total = 0;

    public function mount(Quote $quote)
    {
        $this->quote = $quote->load('customer');

        // Load quote data
        $this->customer_id = $quote->customer_id;
        /** @var Customer|null $customer */
        $customer = $quote->customer;
        $this->selectedCustomer = $customer;
        $this->customerSearch = $customer !== null ? $customer->name : '';

        $this->type = $quote->type;
        $this->project_name = $quote->project_name ?? '';
        $this->issue_date = $quote->issue_date->format('Y-m-d');
        $this->valid_until = $quote->valid_until->format('Y-m-d');
        $this->vat_rate = (float) $quote->vat_rate;
        $this->notes = $quote->notes ?? '';

        // Load line items
        $this->items = $quote->items;

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
            'issue_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:issue_date',
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

        // Update the quote
        $this->quote->update([
            'customer_id' => $this->customer_id,
            'type' => $this->type,
            'project_name' => $this->project_name ?: null,
            'issue_date' => $this->issue_date,
            'valid_until' => $this->valid_until,
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'vat_rate' => $this->vat_rate,
            'vat_amount' => $this->vat_amount,
            'total' => $this->total,
            'notes' => $this->notes ?: null,
        ]);

        session()->flash('success', 'Angebot erfolgreich aktualisiert.');

        return redirect()->route('quotes.index');
    }

    public function render()
    {
        return view('livewire.quotes.edit');
    }
}
