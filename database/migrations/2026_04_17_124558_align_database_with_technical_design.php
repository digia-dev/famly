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
        Schema::table('tabungans', function (Blueprint $table) {
            if (!Schema::hasColumn('tabungans', 'status')) {
                $table->string('status')->default('verified')->after('keterangan');
            }
            $table->json('metadata_ai')->nullable()->after('status');
        });

        Schema::table('planned_transactions', function (Blueprint $table) {
            $table->foreignId('family_id')->nullable()->constrained()->onDelete('set null')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tabungans', function (Blueprint $table) {
            $table->dropColumn(['status', 'metadata_ai']);
        });

        Schema::table('planned_transactions', function (Blueprint $table) {
            $table->dropForeign(['family_id']);
            $table->dropColumn('family_id');
        });
    }
};
