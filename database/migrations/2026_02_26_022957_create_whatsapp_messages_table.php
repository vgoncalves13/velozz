<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('whatsapp_instance_id')->nullable()->constrained('whatsapp_instances')->cascadeOnDelete();
            $table->string('type')->default('text');
            $table->string('direction')->default('outgoing');
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->string('remote_message_id')->nullable(); // Z-API message ID
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('lead_id');
            $table->index('whatsapp_instance_id');
            $table->index('direction');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
