<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('title', 100);
            $table->string('url', 2048);
            $table->string('icon', 20)->default('custom');
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamps();

            $table->index(['store_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_links');
    }
};
