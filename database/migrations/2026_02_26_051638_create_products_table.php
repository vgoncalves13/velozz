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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();

            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('unit')->nullable(); // piece, hour, kg, etc

            $table->string('status')->default('active'); // active, inactive
            $table->string('image_url')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
