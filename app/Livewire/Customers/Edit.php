<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Kunde bearbeiten')]
class Edit extends Component
{
    public Customer $customer;

    public string $name = '';

    public string $street = '';

    public string $city = '';

    public string $zip = '';

    public string $tax_id = '';

    public string $eu_vat_id = '';

    public string $notes = '';

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
        $this->name = $customer->name;
        $this->street = $customer->address['street'] ?? '';
        $this->city = $customer->address['city'] ?? '';
        $this->zip = $customer->address['zip'] ?? '';
        $this->tax_id = $customer->tax_id ?? '';
        $this->eu_vat_id = $customer->eu_vat_id ?? '';
        $this->notes = $customer->notes ?? '';
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip' => 'required|string|max:10',
            'tax_id' => 'nullable|string|max:50',
            'eu_vat_id' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $this->customer->update([
            'name' => $this->name,
            'address' => [
                'street' => $this->street,
                'city' => $this->city,
                'zip' => $this->zip,
            ],
            'tax_id' => $this->tax_id ?: null,
            'eu_vat_id' => $this->eu_vat_id ?: null,
            'notes' => $this->notes ?: null,
        ]);

        session()->flash('success', 'Kunde erfolgreich aktualisiert.');

        return redirect()->route('customers.index');
    }

    public function render()
    {
        return view('livewire.customers.edit');
    }
}
