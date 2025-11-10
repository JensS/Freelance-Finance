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
        Schema::create('cash_receipts', function (Blueprint $table) {
            $table->id();
            $table->date('receipt_date');
            $table->string('correspondent');
            $table->text('description');
            $table->string('category'); // Geschäftsausgabe 0%, 7%, 19%, Bewirtung, etc.
            $table->decimal('amount', 10, 2);
            $table->text('note')->nullable();
            $table->string('paperless_document_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_receipts');
    }
};
