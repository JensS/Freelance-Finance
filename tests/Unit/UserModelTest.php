<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_role_attribute(): void
    {
        $user = User::factory()->owner()->create();

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals(Role::Owner, $user->role);
    }

    public function test_user_is_owner_method(): void
    {
        $owner = User::factory()->owner()->create();
        $taxAccountant = User::factory()->taxAccountant()->create();

        $this->assertTrue($owner->isOwner());
        $this->assertFalse($taxAccountant->isOwner());
    }

    public function test_user_is_tax_accountant_method(): void
    {
        $owner = User::factory()->owner()->create();
        $taxAccountant = User::factory()->taxAccountant()->create();

        $this->assertFalse($owner->isTaxAccountant());
        $this->assertTrue($taxAccountant->isTaxAccountant());
    }

    public function test_user_has_role_method(): void
    {
        $owner = User::factory()->owner()->create();

        $this->assertTrue($owner->hasRole(Role::Owner));
        $this->assertFalse($owner->hasRole(Role::TaxAccountant));
    }

    public function test_user_can_access_route_method(): void
    {
        $owner = User::factory()->owner()->create();
        $taxAccountant = User::factory()->taxAccountant()->create();

        // Owner can access everything
        $this->assertTrue($owner->canAccessRoute('invoices.index'));
        $this->assertTrue($owner->canAccessRoute('settings.index'));

        // Tax accountant has limited access
        $this->assertTrue($taxAccountant->canAccessRoute('accounting.index'));
        $this->assertFalse($taxAccountant->canAccessRoute('invoices.index'));
    }

    public function test_user_role_is_cast_to_enum(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals(Role::Owner, $user->role);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'plaintext']);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(password_verify('plaintext', $user->password));
    }
}
