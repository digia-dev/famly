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
        Schema::table('kategori_nama_tabungans', function (Blueprint $table) {
            if (!Schema::hasColumn('kategori_nama_tabungans', 'wallet_type')) {
                $table->string('wallet_type')->default('pos')->after('icon'); // 'pos' or 'savings'
            }
            if (!Schema::hasColumn('kategori_nama_tabungans', 'status')) {
                $table->string('status')->nullable()->after('wallet_type'); 
            }
            if (!Schema::hasColumn('kategori_nama_tabungans', 'color')) {
                $table->string('color')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_nama_tabungans', function (Blueprint $table) {
            $table->dropColumn(['wallet_type', 'status', 'color']);
        });
    }
};
