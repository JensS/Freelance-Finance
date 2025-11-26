<?php

namespace Tests\Unit;

use App\Enums\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function test_owner_role_has_correct_label(): void
    {
        $this->assertEquals('Inhaber', Role::Owner->label());
    }

    public function test_tax_accountant_role_has_correct_label(): void
    {
        $this->assertEquals('Steuerberater', Role::TaxAccountant->label());
    }

    public function test_owner_role_is_admin(): void
    {
        $this->assertTrue(Role::Owner->isAdmin());
    }

    public function test_tax_accountant_role_is_not_admin(): void
    {
        $this->assertFalse(Role::TaxAccountant->isAdmin());
    }

    public function test_owner_can_access_all_routes(): void
    {
        $this->assertTrue(Role::Owner->canAccessRoute('invoices.index'));
        $this->assertTrue(Role::Owner->canAccessRoute('quotes.index'));
        $this->assertTrue(Role::Owner->canAccessRoute('customers.index'));
        $this->assertTrue(Role::Owner->canAccessRoute('settings.index'));
        $this->assertTrue(Role::Owner->canAccessRoute('users.index'));
        $this->assertTrue(Role::Owner->canAccessRoute('accounting.index'));
        $this->assertTrue(Role::Owner->canAccessRoute('reports.index'));
    }

    public function test_tax_accountant_can_access_allowed_routes(): void
    {
        $this->assertTrue(Role::TaxAccountant->canAccessRoute('dashboard'));
        $this->assertTrue(Role::TaxAccountant->canAccessRoute('accounting.index'));
        $this->assertTrue(Role::TaxAccountant->canAccessRoute('reports.index'));
        $this->assertTrue(Role::TaxAccountant->canAccessRoute('transactions.verify-imports'));
        $this->assertTrue(Role::TaxAccountant->canAccessRoute('paperless.thumbnail'));
    }

    public function test_tax_accountant_cannot_access_restricted_routes(): void
    {
        $this->assertFalse(Role::TaxAccountant->canAccessRoute('invoices.index'));
        $this->assertFalse(Role::TaxAccountant->canAccessRoute('quotes.index'));
        $this->assertFalse(Role::TaxAccountant->canAccessRoute('customers.index'));
        $this->assertFalse(Role::TaxAccountant->canAccessRoute('settings.index'));
        $this->assertFalse(Role::TaxAccountant->canAccessRoute('users.index'));
    }
}
