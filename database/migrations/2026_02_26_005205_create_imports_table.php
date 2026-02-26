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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('filename');
            $table->enum('type', ['xlsx', 'csv', 'url'])->default('xlsx');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');

            $table->integer('total_rows')->default(0);
            $table->integer('imported')->default(0);
            $table->integer('duplicated')->default(0);
            $table->integer('errors')->default(0);

            $table->json('mapping')->nullable();
            $table->json('deduplication_rules')->nullable();
            $table->json('tags')->nullable();
            $table->json('report')->nullable();

            $table->foreignId('assigned_operator_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
