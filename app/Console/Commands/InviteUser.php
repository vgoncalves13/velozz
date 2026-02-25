<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InviteUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:invite
                            {email : The email address of the user to invite}
                            {name : The name of the user}
                            {role : The role of the user (admin_client, supervisor, operator, financial)}
                            {tenant_id : The tenant ID this user belongs to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invite a new user to join a tenant';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $validRoles = ['admin_client', 'supervisor', 'operator', 'financial'];

        if (! in_array($this->argument('role'), $validRoles)) {
            $this->error('Invalid role. Must be one of: '.implode(', ', $validRoles));

            return self::FAILURE;
        }

        // Check if tenant exists
        $tenant = \App\Models\Tenant::find($this->argument('tenant_id'));
        if (! $tenant) {
            $this->error('Tenant not found');

            return self::FAILURE;
        }

        // Check if user already exists
        if (\App\Models\User::where('email', $this->argument('email'))->exists()) {
            $this->error('A user with this email already exists');

            return self::FAILURE;
        }

        // Create user
        $user = \App\Models\User::create([
            'tenant_id' => $this->argument('tenant_id'),
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)), // temporary password
            'role' => $this->argument('role'),
            'status' => 'invited',
        ]);

        // Assign role using Spatie
        $user->assignRole($this->argument('role'));

        // Dispatch invitation email job
        \App\Jobs\SendInviteEmail::dispatch($user);

        $this->info("Invitation sent to {$user->email}");
        $this->info('User will be able to set their password via the invitation link');

        return self::SUCCESS;
    }
}
