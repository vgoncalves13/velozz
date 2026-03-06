<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->foreignId('opportunity_stage_id')->nullable()->after('stage')->constrained('opportunity_stages')->nullOnDelete();
        });

        // Migrate existing string stages to default opportunity stages per tenant
        $tenants = DB::table('opportunities')->distinct()->pluck('tenant_id');

        foreach ($tenants as $tenantId) {
            $defaultStages = [
                ['name' => __('opportunity_stages.defaults.proposal'), 'color' => '#3B82F6', 'order' => 0, 'icon' => 'heroicon-o-document-text'],
                ['name' => __('opportunity_stages.defaults.negotiation'), 'color' => '#F59E0B', 'order' => 1, 'icon' => 'heroicon-o-chat-bubble-left-right'],
                ['name' => __('opportunity_stages.defaults.won'), 'color' => '#10B981', 'order' => 2, 'icon' => 'heroicon-o-check-circle'],
                ['name' => __('opportunity_stages.defaults.lost'), 'color' => '#EF4444', 'order' => 3, 'icon' => 'heroicon-o-x-circle'],
            ];

            $stageMap = [];
            foreach ($defaultStages as $stageData) {
                $stageId = DB::table('opportunity_stages')->insertGetId([
                    'tenant_id' => $tenantId,
                    'name' => $stageData['name'],
                    'color' => $stageData['color'],
                    'order' => $stageData['order'],
                    'icon' => $stageData['icon'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $stageMap[$stageData['order']] = $stageId;
            }

            // Map string stage values to new stage IDs
            $stringToOrder = [
                'proposal' => 0,
                'negotiation' => 1,
                'closed_won' => 2,
                'closed_lost' => 3,
            ];

            foreach ($stringToOrder as $stringStage => $order) {
                DB::table('opportunities')
                    ->where('tenant_id', $tenantId)
                    ->where('stage', $stringStage)
                    ->update(['opportunity_stage_id' => $stageMap[$order]]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('opportunity_stage_id');
        });
    }
};
