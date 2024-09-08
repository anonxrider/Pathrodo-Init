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
            $table->string('item_name'); // Product name
            $table->text('item_description')->nullable(); // Product description
            $table->decimal('gst', 5, 2);
            $table->string('hsn_code', 10); // HSN code, up to 10 characters
            $table->string('bar_code')->nullable();
            $table->string('item_code', 10);
            $table->string('serial_number', 10);
            $table->date('manufacture_date')->nullable(); 
            $table->date('expiry_date')->nullable(); 
            $table->decimal('rate', 8, 2); // Product rate
            //$table->foreignId('unit_id')->constrained('units')->onDelete('set null');
            $table->timestamps();
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
