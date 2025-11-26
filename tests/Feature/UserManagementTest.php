<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_user_list(): void
    {
        $owner = User::factory()->owner()->create();
        User::factory()->taxAccountant()->count(3)->create();

        $response = $this->actingAs($owner)->get('/users');

        $response->assertStatus(200);
        $response->assertSee('Benutzer');
    }

    public function test_owner_can_create_new_user(): void
    {
        $owner = User::factory()->owner()->create();

        Livewire::actingAs($owner)
            ->test(\App\Livewire\Users\Index::class)
            ->call('openCreateModal')
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', Role::TaxAccountant->value)
            ->call('save');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => Role::TaxAccountant->value,
        ]);
    }

    public function test_owner_can_edit_user(): void
    {
        $owner = User::factory()->owner()->create();
        $user = User::factory()->taxAccountant()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\Users\Index::class)
            ->call('openEditModal', $user->id)
            ->set('name', 'Updated Name')
            ->call('save');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_owner_can_delete_other_user(): void
    {
        $owner = User::factory()->owner()->create();
        $userToDelete = User::factory()->taxAccountant()->create();

        Livewire::actingAs($owner)
            ->test(\App\Livewire\Users\Index::class)
            ->call('confirmDelete', $userToDelete->id)
            ->call('delete');

        $this->assertDatabaseMissing('users', [
            'id' => $userToDelete->id,
        ]);
    }

    public function test_owner_cannot_delete_self(): void
    {
        $owner = User::factory()->owner()->create();

        Livewire::actingAs($owner)
            ->test(\App\Livewire\Users\Index::class)
            ->call('confirmDelete', $owner->id)
            ->call('delete')
            ->assertSee('Sie können sich nicht selbst löschen');

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
        ]);
    }

    public function test_owner_cannot_demote_self_from_owner_role(): void
    {
        $owner = User::factory()->owner()->create();

        Livewire::actingAs($owner)
            ->test(\App\Livewire\Users\Index::class)
            ->call('openEditModal', $owner->id)
            ->set('role', Role::TaxAccountant->value)
            ->call('save')
            ->assertSee('Sie können sich nicht selbst die Inhaber-Rolle entziehen');

        $owner->refresh();
        $this->assertEquals(Role::Owner, $owner->role);
    }

    public function test_user_creation_validates_required_fields(): void
    {
        $owner = User::factory()->owner()->create();

        Livewire::actingAs($owner)
            ->test(\App\Livewire\Users\Index::class)
            ->call('openCreateModal')
            ->set('name', '')
            ->set('email', '')
            ->set('password', '')
            ->call('save')
            ->assertHasErrors(['name', 'email', 'password']);
    }

    public function test_user_creation_validates_unique_email(): void
    {
        $owner = User::factory()->owner()->create();
        User::factory()->create(['email' => 'existing@example.com']);

        Livewire::actingAs($owner)
            ->test(\App\Livewire\Users\Index::class)
            ->call('openCreateModal')
            ->set('name', 'Test User')
            ->set('email', 'existing@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_user_creation_validates_password_confirmation(): void
    {
        $owner = User::factory()->owner()->create();

        Livewire::actingAs($owner)
            ->test(\App\Livewire\Users\Index::class)
            ->call('openCreateModal')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'different')
            ->call('save')
            ->assertHasErrors(['password']);
    }

    public function test_user_update_allows_empty_password(): void
    {
        $owner = User::factory()->owner()->create();
        $user = User::factory()->taxAccountant()->create([
            'password' => bcrypt('original-password'),
        ]);
        $originalPasswordHash = $user->password;

        Livewire::actingAs($owner)
            ->test(\App\Livewire\Users\Index::class)
            ->call('openEditModal', $user->id)
            ->set('name', 'Updated Name')
            ->set('password', '')
            ->call('save');

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals($originalPasswordHash, $user->password);
    }

    public function test_tax_accountant_cannot_access_user_management(): void
    {
        $taxAccountant = User::factory()->taxAccountant()->create();

        $response = $this->actingAs($taxAccountant)->get('/users');

        $response->assertStatus(403);
    }
}
