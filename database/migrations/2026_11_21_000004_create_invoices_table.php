<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('month_id')->constrained()->cascadeOnDelete();
            $table->string('number');
            $table->text('description')->nullable();
            $table->date('invoice_date');
            $table->decimal('amount', 12, 2);              // المبلغ الخاضع
            $table->boolean('has_due')->default(false);    // هل يوجد مستحق 5%
            $table->date('due_date')->nullable();          // تاريخ الاستحقاق
            $table->boolean('due_paid')->default(false);   // هل تم استحقاق المبلغ
            $table->timestamp('due_paid_at')->nullable();
            $table->string('image_path')->nullable();
            $table->date('last_reminded_on')->nullable();  // لمنع تكرار التذكير في نفس اليوم
            $table->timestamps();

            $table->index(['has_due', 'due_paid', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
