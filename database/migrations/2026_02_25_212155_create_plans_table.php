<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->integer('leads_limit_per_month')->default(1000);
            $table->integer('messages_limit_per_day')->default(500);
            $table->integer('operators_limit')->default(5);
            $table->integer('whatsapp_instances_limit')->default(1);
            $table->integer('trial_days')->default(30);
            $table->timestamps();

            $table->index('name');
        });

        // Add foreign key to tenants table now that plans exists
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
        });

        Schema::dropIfExists('plans');
    }
};
