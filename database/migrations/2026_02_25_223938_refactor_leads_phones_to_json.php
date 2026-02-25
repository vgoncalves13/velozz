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
        // Step 1: Add new JSON columns
        Schema::table('leads', function (Blueprint $table) {
            $table->json('phones')->nullable()->after('email');
            $table->json('whatsapps')->nullable()->after('phones');
        });

        // Step 2: Migrate existing data
        $leads = DB::table('leads')->get();
        foreach ($leads as $lead) {
            $phones = [];
            $whatsapps = [];

            // Collect all non-null phones
            for ($i = 1; $i <= 10; $i++) {
                $phoneField = 'phone_'.$i;
                if (! empty($lead->$phoneField)) {
                    $phones[] = $lead->$phoneField;
                }
            }

            // Collect all non-null whatsapps
            for ($i = 1; $i <= 10; $i++) {
                $whatsappField = 'whatsapp_'.$i;
                if (! empty($lead->$whatsappField)) {
                    $whatsapps[] = $lead->$whatsappField;
                }
            }

            // Update with JSON arrays
            DB::table('leads')
                ->where('id', $lead->id)
                ->update([
                    'phones' => ! empty($phones) ? json_encode($phones) : null,
                    'whatsapps' => ! empty($whatsapps) ? json_encode($whatsapps) : null,
                    // Convert primary_whatsapp_index from 1-based to 0-based
                    'primary_whatsapp_index' => $lead->primary_whatsapp_index > 0
                        ? $lead->primary_whatsapp_index - 1
                        : 0,
                ]);
        }

        // Step 3: Drop old columns
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'phone_1', 'phone_2', 'phone_3', 'phone_4', 'phone_5',
                'phone_6', 'phone_7', 'phone_8', 'phone_9', 'phone_10',
                'whatsapp_1', 'whatsapp_2', 'whatsapp_3', 'whatsapp_4', 'whatsapp_5',
                'whatsapp_6', 'whatsapp_7', 'whatsapp_8', 'whatsapp_9', 'whatsapp_10',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Re-add old columns
        Schema::table('leads', function (Blueprint $table) {
            for ($i = 1; $i <= 10; $i++) {
                $table->string('phone_'.$i)->nullable();
                $table->string('whatsapp_'.$i)->nullable();
            }
        });

        // Step 2: Migrate data back
        $leads = DB::table('leads')->get();
        foreach ($leads as $lead) {
            $update = [];

            // Restore phones
            if (! empty($lead->phones)) {
                $phones = json_decode($lead->phones, true);
                foreach ($phones as $index => $phone) {
                    if ($index < 10) {
                        $update['phone_'.($index + 1)] = $phone;
                    }
                }
            }

            // Restore whatsapps
            if (! empty($lead->whatsapps)) {
                $whatsapps = json_decode($lead->whatsapps, true);
                foreach ($whatsapps as $index => $whatsapp) {
                    if ($index < 10) {
                        $update['whatsapp_'.($index + 1)] = $whatsapp;
                    }
                }
            }

            // Convert primary_whatsapp_index from 0-based to 1-based
            $update['primary_whatsapp_index'] = $lead->primary_whatsapp_index + 1;

            DB::table('leads')->where('id', $lead->id)->update($update);
        }

        // Step 3: Drop JSON columns
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['phones', 'whatsapps']);
        });
    }
};
