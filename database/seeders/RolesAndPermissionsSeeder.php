<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Lead permissions
            'view_lead',
            'create_lead',
            'edit_lead',
            'delete_lead',
            'export_leads',
            'import_leads',

            // WhatsApp permissions
            'send_message',
            'view_messages',
            'configure_whatsapp',
            'manage_templates',

            // Dashboard & Reports
            'view_dashboard',
            'view_reports',

            // Team Management
            'manage_operators',
            'view_operators',

            // Pipeline & Kanban
            'manage_pipeline',
            'view_pipeline',
            'move_lead_stage',

            // Products & Opportunities
            'manage_products',
            'view_products',
            'manage_opportunities',
            'view_opportunities',

            // Settings & Configuration
            'manage_tenant_settings',
            'view_audit_logs',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission]);
        }

        // Reset cache again after creating permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles and assign permissions

        // 1. Admin Master - Full system access (manages all tenants)
        $adminMaster = \Spatie\Permission\Models\Role::create(['name' => 'admin_master']);
        // Admin Master doesn't need permissions - has full access at application level

        // 2. Admin Client - Full tenant access
        $adminClient = \Spatie\Permission\Models\Role::create(['name' => 'admin_client']);
        $adminClient->givePermissionTo([
            'view_lead', 'create_lead', 'edit_lead', 'delete_lead', 'export_leads', 'import_leads',
            'send_message', 'view_messages', 'configure_whatsapp', 'manage_templates',
            'view_dashboard', 'view_reports',
            'manage_operators', 'view_operators',
            'manage_pipeline', 'view_pipeline', 'move_lead_stage',
            'manage_products', 'view_products', 'manage_opportunities', 'view_opportunities',
            'manage_tenant_settings', 'view_audit_logs',
        ]);

        // 3. Supervisor - Can manage team and leads
        $supervisor = \Spatie\Permission\Models\Role::create(['name' => 'supervisor']);
        $supervisor->givePermissionTo([
            'view_lead', 'create_lead', 'edit_lead', 'export_leads', 'import_leads',
            'send_message', 'view_messages', 'manage_templates',
            'view_dashboard', 'view_reports',
            'view_operators',
            'view_pipeline', 'move_lead_stage',
            'view_products', 'manage_opportunities', 'view_opportunities',
        ]);

        // 4. Operator - Basic operator permissions (only assigned leads)
        $operator = \Spatie\Permission\Models\Role::create(['name' => 'operator']);
        $operator->givePermissionTo([
            'view_lead', 'create_lead', 'edit_lead',
            'send_message', 'view_messages',
            'view_dashboard',
            'view_pipeline', 'move_lead_stage',
            'view_products', 'view_opportunities',
        ]);

        // 5. Financial - Financial access (products, opportunities, reports)
        $financial = \Spatie\Permission\Models\Role::create(['name' => 'financial']);
        $financial->givePermissionTo([
            'view_lead', 'export_leads',
            'view_dashboard', 'view_reports',
            'manage_products', 'view_products',
            'manage_opportunities', 'view_opportunities',
        ]);
    }
}
