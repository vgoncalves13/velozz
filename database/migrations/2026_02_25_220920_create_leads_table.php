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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            // Basic information
            $table->string('full_name');
            $table->string('email')->nullable();

            // Phone numbers (up to 10)
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('phone_3')->nullable();
            $table->string('phone_4')->nullable();
            $table->string('phone_5')->nullable();
            $table->string('phone_6')->nullable();
            $table->string('phone_7')->nullable();
            $table->string('phone_8')->nullable();
            $table->string('phone_9')->nullable();
            $table->string('phone_10')->nullable();

            // WhatsApp numbers (up to 10)
            $table->string('whatsapp_1')->nullable();
            $table->string('whatsapp_2')->nullable();
            $table->string('whatsapp_3')->nullable();
            $table->string('whatsapp_4')->nullable();
            $table->string('whatsapp_5')->nullable();
            $table->string('whatsapp_6')->nullable();
            $table->string('whatsapp_7')->nullable();
            $table->string('whatsapp_8')->nullable();
            $table->string('whatsapp_9')->nullable();
            $table->string('whatsapp_10')->nullable();
            $table->tinyInteger('primary_whatsapp_index')->nullable()->default(1); // 1-10

            // Address
            $table->string('street_type')->nullable(); // Rua, Avenida, Travessa, etc
            $table->string('street_name')->nullable();
            $table->string('number')->nullable();
            $table->string('complement')->nullable();
            $table->string('district')->nullable(); // bairro
            $table->string('neighborhood')->nullable(); // freguesia
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Portugal');

            // Lead management
            $table->enum('source', ['import', 'manual', 'api', 'form'])->default('manual');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pipeline_stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();

            // Metadata
            $table->json('tags')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // Consent & contact preferences
            $table->enum('consent_status', ['pending', 'granted', 'refused'])->default('pending');
            $table->date('consent_date')->nullable();
            $table->boolean('opt_out')->default(false);
            $table->string('opt_out_reason')->nullable();
            $table->date('opt_out_date')->nullable();
            $table->boolean('do_not_contact')->default(false);

            // Additional info
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tenant_id');
            $table->index('assigned_user_id');
            $table->index('pipeline_stage_id');
            $table->index('source');
            $table->index('priority');
            $table->index('whatsapp_1');
            $table->index('email');
            $table->index(['tenant_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
