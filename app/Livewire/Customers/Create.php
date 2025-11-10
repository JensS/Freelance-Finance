<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Neuer Kunde')]
class Create extends Component
{
    public string $name = '';

    public string $email = '';

    public string $street = '';

    public string $city = '';

    public string $zip = '';

    public string $tax_id = '';

    public string $eu_vat_id = '';

    public string $contact_person = '';

    public string $notes = '';

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip' => 'required|string|max:10',
            'tax_id' => 'nullable|string|max:50',
            'eu_vat_id' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Customer::create([
            'name' => $this->name,
            'email' => $this->email,
            'address' => [
                'street' => $this->street,
                'city' => $this->city,
                'zip' => $this->zip,
            ],
            'tax_id' => $this->tax_id ?: null,
            'eu_vat_id' => $this->eu_vat_id ?: null,
            'contact_person' => $this->contact_person ?: null,
            'notes' => $this->notes ?: null,
        ]);

        session()->flash('success', 'Kunde erfolgreich erstellt.');

        return redirect()->route('customers.index');
    }

    public function render()
    {
        return view('livewire.customers.create');
    }
}
