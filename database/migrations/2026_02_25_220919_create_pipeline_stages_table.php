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
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();

            $table->string('name');
            $table->string('color')->default('#3B82F6'); // Tailwind blue-500
            $table->integer('order')->default(0);
            $table->string('icon')->nullable();

            // Will be expanded in Phase 4 with automations
            $table->integer('sla_hours')->nullable();
            $table->json('entry_automation')->nullable();
            $table->json('exit_automation')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index(['tenant_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};
