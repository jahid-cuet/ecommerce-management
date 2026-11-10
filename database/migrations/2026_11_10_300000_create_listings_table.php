<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A listing assigns a catalog product to a shop, with that shop's own price,
     * details and thumbnail. Customers only ever see products through listings.
     */
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            // Optional overrides; when empty, the catalog product's details and thumbnail are shown.
            $table->text('description')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->timestamps();

            // A shop lists each catalog product once.
            $table->unique(['shop_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
