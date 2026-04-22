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
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->enum('subscription_status', ['trial', 'subscriber'])->default('trial');
            $blueprint->timestamp('subscription_until')->nullable();
            $blueprint->integer('ai_usage_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['subscription_status', 'subscription_until', 'ai_usage_count']);
        });
    }
};
