<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            // One person, one identity, one storefront.
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('username', 30)->unique();
            $table->string('display_name');
            $table->text('bio')->nullable();
            $table->string('location')->nullable();
            $table->string('website', 2048)->nullable();
            $table->string('category')->nullable();
            $table->string('account_type', 20)->default('individual'); // individual|business|organisation
            $table->string('avatar_path')->nullable();
            $table->string('layout', 20)->default('minimal');
            $table->json('theme')->nullable(); // reserved for future appearance options
            $table->string('color_mode', 10)->default('light'); // light|dark
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->text('payment_instructions')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_suspended')->default(false);
            $table->timestamp('suspended_at')->nullable();
            $table->enum('verification_level', ['unverified', 'kuartal_id', 'business', 'official'])->default('unverified');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
