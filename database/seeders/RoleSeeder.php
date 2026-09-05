<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Market management
            'manage markets',

            // Shop management
            'view shops',
            'create shops',
            'edit shops',
            'delete shops',

            // Staff management
            'view staff',
            'create staff',
            'edit staff',
            'delete staff',

            // Invoice management
            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',

            // Payment management
            'view payments',
            'collect payments',
            'delete payments',

            // Reports
            'view reports',
            'export reports',

            // Complaints
            'view complaints',
            'create complaints',
            'manage complaints',

            // Notices
            'view notices',
            'create notices',
            'edit notices',
            'delete notices',

            // Settings
            'manage settings',

            // SMS
            'send sms',
            'view sms logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $marketOwner = Role::create(['name' => 'market_owner']);
        $marketOwner->givePermissionTo([
            'view shops', 'create shops', 'edit shops', 'delete shops',
            'view staff', 'create staff', 'edit staff', 'delete staff',
            'view invoices', 'create invoices', 'edit invoices', 'delete invoices',
            'view payments', 'collect payments', 'delete payments',
            'view reports', 'export reports',
            'view complaints', 'manage complaints',
            'view notices', 'create notices', 'edit notices', 'delete notices',
            'manage settings',
            'send sms', 'view sms logs',
        ]);

        $collector = Role::create(['name' => 'collector']);
        $collector->givePermissionTo([
            'view shops', 'create shops',
            'view invoices',
            'view payments', 'collect payments',
            'view complaints', 'manage complaints',
            'view notices',
        ]);

        $shopOwner = Role::create(['name' => 'shop_owner']);
        $shopOwner->givePermissionTo([
            'view invoices',
            'view payments',
            'view complaints', 'create complaints',
            'view notices',
        ]);
    }
}
