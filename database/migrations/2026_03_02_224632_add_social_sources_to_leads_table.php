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
        Schema::table('leads', function (Blueprint $table) {
            $table->enum('source', ['import', 'manual', 'api', 'form', 'whatsapp', 'instagram', 'facebook_messenger'])
                ->default('manual')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->enum('source', ['import', 'manual', 'api', 'form'])
                ->default('manual')
                ->change();
        });
    }
};
