<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First-party, privacy-friendly analytics. No raw IPs: visitor_hash is a
        // SHA-256 of (ip, user agent, date, app key) and rotates daily.
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // store_view|link_click|product_view
            $table->foreignId('store_link_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->char('visitor_hash', 64)->nullable();
            $table->string('referrer_host')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['store_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
