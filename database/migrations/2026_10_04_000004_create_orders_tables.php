<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('orders', function (Blueprint $table) {
   $table->id();
   $table->foreignId('store_id')->constrained()->cascadeOnDelete();
   $table->string('customer_name', 120);
   $table->string('customer_email');
   $table->decimal('amount', 15, 2);
   $table->string('currency', 3);
   $table->string('status', 30)->default('pending');
   $table->string('payment_provider', 50)->nullable();
   $table->string('payment_reference')->nullable();
   $table->timestamps();
  });
  Schema::create('order_items', function (Blueprint $table) {
   $table->id();
   $table->foreignId('order_id')->constrained()->cascadeOnDelete();
   $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
   $table->string('name', 160);
   $table->unsignedInteger('quantity');
   $table->decimal('unit_price', 15, 2);
   $table->timestamps();
  });
 }
 public function down(): void {
  Schema::dropIfExists('order_items');
  Schema::dropIfExists('orders');
 }
};