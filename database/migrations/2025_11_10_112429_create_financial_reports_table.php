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
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('total_income', 10, 2);
            $table->decimal('total_expenses', 10, 2);
            $table->decimal('net_income', 10, 2);
            $table->json('data'); // Detailed breakdown of income/expenses
            $table->string('report_pdf_path')->nullable();
            $table->timestamps();
            $table->unique(['month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
    }
};
