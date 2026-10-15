<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Seller's own QRIS image, shown to buyers after they order (manual payments).
            $table->string('payment_qris_path')->nullable()->after('payment_instructions');
            // Seller's WhatsApp number for buyers to confirm payment. Digits only, international format (62...).
            $table->string('whatsapp_number', 20)->nullable()->after('payment_qris_path');
            // Email the seller when a new order comes in.
            $table->boolean('notify_new_orders')->default(true)->after('whatsapp_number');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['payment_qris_path', 'whatsapp_number', 'notify_new_orders']);
        });
    }
};
