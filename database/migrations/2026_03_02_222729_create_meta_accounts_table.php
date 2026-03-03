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
        Schema::create('meta_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // instagram | facebook_messenger
            $table->string('page_id');
            $table->string('page_name');
            $table->string('instagram_user_id')->nullable();
            $table->text('access_token');
            $table->string('status')->default('disconnected'); // disconnected | connected | error
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['page_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_accounts');
    }
};
