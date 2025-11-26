<?php

namespace App\Enums;

enum Role: string
{
    case Owner = 'owner';
    case TaxAccountant = 'tax_accountant';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Inhaber',
            self::TaxAccountant => 'Steuerberater',
        };
    }

    /**
     * Get allowed routes for this role.
     */
    public function allowedRoutes(): array
    {
        return match ($this) {
            self::Owner => ['*'], // Owner has access to everything
            self::TaxAccountant => [
                'dashboard',
                'accounting.*',
                'transactions.*',
                'reports.*',
                'paperless.*',
                'logout',
            ],
        };
    }

    /**
     * Check if this role can access a given route.
     */
    public function canAccessRoute(string $routeName): bool
    {
        $allowedRoutes = $this->allowedRoutes();

        // Owner has access to everything
        if (in_array('*', $allowedRoutes)) {
            return true;
        }

        foreach ($allowedRoutes as $pattern) {
            // Exact match
            if ($pattern === $routeName) {
                return true;
            }

            // Wildcard match (e.g., 'accounting.*' matches 'accounting.index')
            if (str_ends_with($pattern, '.*')) {
                $prefix = substr($pattern, 0, -2);
                if (str_starts_with($routeName, $prefix . '.') || $routeName === $prefix) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if this role is an admin (Owner).
     */
    public function isAdmin(): bool
    {
        return $this === self::Owner;
    }
}
