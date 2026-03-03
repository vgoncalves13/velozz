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
        Schema::create('social_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meta_account_id')->constrained()->cascadeOnDelete();
            $table->string('channel'); // instagram | facebook_messenger
            $table->string('direction'); // incoming | outgoing
            $table->string('type')->default('text'); // text | image | audio | video | document
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->string('status')->default('pending'); // pending | sent | delivered | read | failed
            $table->string('external_message_id')->nullable();
            $table->string('external_thread_id')->nullable();
            $table->string('sender_id')->nullable();
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['external_message_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_messages');
    }
};
