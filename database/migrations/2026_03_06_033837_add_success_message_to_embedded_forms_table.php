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
        Schema::table('embedded_forms', function (Blueprint $table) {
            $table->string('success_message')->nullable()->after('redirect_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('embedded_forms', function (Blueprint $table) {
            $table->dropColumn('success_message');
        });
    }
};
