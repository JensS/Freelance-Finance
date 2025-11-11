<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Kunden')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $deleteConfirm = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete(int $customerId)
    {
        $customer = Customer::find($customerId);

        if ($customer) {
            // Check if customer has invoices or quotes
            if ($customer->invoices()->exists() || $customer->quotes()->exists()) {
                session()->flash('error', 'Kunde kann nicht gelöscht werden, da bereits Rechnungen oder Angebote vorhanden sind.');

                return;
            }

            $customer->delete();
            session()->flash('success', 'Kunde erfolgreich gelöscht.');
        }
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.customers.index', [
            'customers' => $customers,
        ]);
    }
}
