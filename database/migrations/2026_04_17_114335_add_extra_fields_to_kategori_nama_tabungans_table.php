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
            $table->string('kategori_kas')->nullable();
            $table->decimal('target_saldo', 15, 2)->nullable();
            $table->string('icon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_nama_tabungans', function (Blueprint $table) {
            $table->dropColumn(['kategori_kas', 'target_saldo', 'icon']);
        });
    }
};
