<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['admin_master', 'admin_cliente', 'supervisor', 'operador', 'financeiro'])->default('operador')->after('phone');
            $table->enum('status', ['active', 'invited', 'suspended', 'temporary'])->default('active')->after('role');
            $table->string('photo')->nullable()->after('status');
            $table->string('invite_token')->nullable()->after('remember_token');
            $table->timestamp('invite_expires_at')->nullable()->after('invite_token');
            $table->timestamp('last_login_at')->nullable()->after('invite_expires_at');

            $table->index('tenant_id');
            $table->index('role');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn([
                'tenant_id',
                'phone',
                'role',
                'status',
                'photo',
                'invite_token',
                'invite_expires_at',
                'last_login_at',
            ]);
        });
    }
};
