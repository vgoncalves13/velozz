<?php

namespace App\Console\Commands;

use App\Helpers\AuditHelper;
use App\Models\User;
use Illuminate\Console\Command;

class TestAuditLogs extends Command
{
    protected $signature = 'audit:test';

    protected $description = 'Create sample audit logs for testing';

    public function handle(): int
    {
        $this->info('Creating sample audit logs...');

        // Login logs
        $this->line('Creating login logs...');
        $users = User::limit(3)->get();
        foreach ($users as $user) {
            AuditHelper::log('login', 'user', $user->id);
        }

        // Logout logs
        $this->line('Creating logout logs...');
        AuditHelper::log('logout', 'user', $users->first()->id ?? 1);

        // Import logs
        $this->line('Creating import logs...');
        for ($i = 1; $i <= 5; $i++) {
            AuditHelper::log('import', 'import', $i, null, [
                'filename' => "leads_{$i}.xlsx",
                'total_rows' => rand(50, 500),
                'imported' => rand(40, 450),
            ]);
        }

        // Lead transfer logs (with previous/new data)
        $this->line('Creating lead transfer logs...');
        for ($i = 1; $i <= 3; $i++) {
            AuditHelper::log(
                'lead_transfer',
                'lead',
                $i,
                ['assigned_user_id' => 1, 'user_name' => 'Operador A'],
                ['assigned_user_id' => 2, 'user_name' => 'Operador B']
            );
        }

        // Send message logs
        $this->line('Creating send_message logs...');
        for ($i = 1; $i <= 10; $i++) {
            AuditHelper::log('send_message', 'whatsapp_message', $i);
        }

        // QR Code access logs
        $this->line('Creating qr_code_access logs...');
        AuditHelper::log('qr_code_access', 'whatsapp_instance', 1);
        AuditHelper::log('qr_code_access', 'whatsapp_instance', 1);

        // GDPR anonymization logs
        $this->line('Creating GDPR logs...');
        AuditHelper::log('gdpr_anonymization', 'lead', 999, [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
        ], [
            'full_name' => 'ANONYMOUS',
            'email' => null,
        ]);

        // Update logs
        $this->line('Creating update logs...');
        AuditHelper::log('update_lead', 'lead', 5, [
            'priority' => 'low',
        ], [
            'priority' => 'high',
        ]);

        // System actions (no user_id)
        $this->line('Creating system action logs...');
        auth()->logout();
        AuditHelper::log('system_cleanup', 'system', null, null, [
            'action' => 'scheduled_cleanup',
            'items_deleted' => 42,
        ]);

        $total = \App\Models\AuditLog::count();
        $this->info("✅ Created sample logs! Total audit logs in database: {$total}");
        $this->line('');
        $this->line('View them at: http://velozz.test/app/audit-logs');

        return Command::SUCCESS;
    }
}
