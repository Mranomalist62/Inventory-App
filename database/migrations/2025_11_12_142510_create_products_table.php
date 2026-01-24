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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->integer('discount')->default(0);
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit', 20)->default('pcs');
            $table->unsignedBigInteger('cost_price')->default(0); // harga modal
            $table->unsignedBigInteger('sell_price')->default(0); // harga jual
            $table->decimal('tax_rate', 5, 2)->default(0);        // mis. 11.00
            $table->unsignedInteger('min_stock')->default(0);
            $table->unsignedInteger('qty_on_hand')->default(0);   // single-location dulu
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
