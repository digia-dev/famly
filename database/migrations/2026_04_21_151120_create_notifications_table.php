<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $header) {
            $header->id();
            $header->foreignId('user_id')->constrained()->onDelete('cascade');
            $header->string('title');
            $header->text('message');
            $header->string('type')->default('info'); // info, success, warning, danger
            $header->string('link')->nullable();
            $header->boolean('is_read')->default(false);
            $header->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
