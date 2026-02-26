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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('value', 10, 2);
            $table->string('stage')->default('proposal'); // proposal, negotiation, closed_won, closed_lost
            $table->integer('probability')->default(0); // 0-100

            $table->date('expected_close_date')->nullable();
            $table->text('loss_reason')->nullable();

            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index('lead_id');
            $table->index('product_id');
            $table->index('stage');
            $table->index('assigned_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
