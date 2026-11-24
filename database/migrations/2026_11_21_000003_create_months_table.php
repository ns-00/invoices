<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('ym', 7);                       // 2026-11
            $table->boolean('closed')->default(false);     // إقفال الشهر
            $table->timestamps();
            $table->unique(['project_id', 'ym']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('months');
    }
};
