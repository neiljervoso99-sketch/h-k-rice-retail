<?php

namespace App\Support;

use App\Models\User;

class AdminModuleAccess
{
    public static function allows(User $user, ?string $routeName): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isStaff()) {
            return false;
        }

        if ($routeName === 'admin.dashboard' || str_starts_with($routeName ?? '', 'admin.profile.')) {
            return true;
        }

        $permission = match (true) {
            str_starts_with($routeName ?? '', 'admin.sales.') => 'sales',
            str_starts_with($routeName ?? '', 'admin.manual-transactions.') => 'manual_transactions',
            str_starts_with($routeName ?? '', 'admin.debts.') => 'debts',
            str_starts_with($routeName ?? '', 'admin.inventory.') => 'inventory',
            str_starts_with($routeName ?? '', 'admin.purchase-orders.') => 'purchase_orders',
            str_starts_with($routeName ?? '', 'admin.suppliers.') => 'suppliers',
            str_starts_with($routeName ?? '', 'admin.orders.') => 'customer_orders',
            str_starts_with($routeName ?? '', 'admin.products.'),
            str_starts_with($routeName ?? '', 'admin.bundles.'),
            str_starts_with($routeName ?? '', 'admin.reviews.') => 'products',
            str_starts_with($routeName ?? '', 'admin.profit-pricing.') => 'profit_pricing',
            str_starts_with($routeName ?? '', 'admin.expenses.') => 'expenses',
            str_starts_with($routeName ?? '', 'admin.reports.') => 'reports',
            default => null,
        };

        return $permission !== null && $user->hasStaffPermission($permission);
    }
}