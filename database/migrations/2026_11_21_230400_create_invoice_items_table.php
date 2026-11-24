<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');                      // اسم / بيان البند
            $table->decimal('quantity', 10, 2)->default(1);   // الكمية
            $table->decimal('unit_price', 12, 2)->default(0); // سعر الوحدة
            $table->decimal('total_price', 12, 2);            // المجموع
            $table->string('notes')->nullable();              // ملاحظات
            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
