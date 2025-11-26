<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(Role $role): bool
    {
        /** @var Role $userRole */
        $userRole = $this->role;

        return $userRole === $role;
    }

    /**
     * Check if user is an Owner (admin).
     */
    public function isOwner(): bool
    {
        return $this->hasRole(Role::Owner);
    }

    /**
     * Check if user is a Tax Accountant.
     */
    public function isTaxAccountant(): bool
    {
        return $this->hasRole(Role::TaxAccountant);
    }

    /**
     * Check if user can access a specific route.
     */
    public function canAccessRoute(string $routeName): bool
    {
        /** @var Role $role */
        $role = $this->role;

        return $role->canAccessRoute($routeName);
    }
}
