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
        Schema::table('planned_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('planned_transactions', 'is_group')) {
                $table->boolean('is_group')->default(true)->after('id');
            }
            if (!Schema::hasColumn('planned_transactions', 'activity_type')) {
                $table->string('activity_type')->default('task')->after('is_group'); // task, reminder, ritual
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planned_transactions', function (Blueprint $table) {
            $table->dropColumn(['is_group', 'activity_type']);
        });
    }
};
