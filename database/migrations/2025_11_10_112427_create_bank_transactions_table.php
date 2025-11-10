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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('correspondent');
            $table->text('title')->nullable();
            $table->string('type'); // Geschäftsausgabe 0%, 7%, 19%, Einkommen 19%, Privat, etc.
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('EUR');
            $table->decimal('original_amount', 10, 2)->nullable();
            $table->string('original_currency')->nullable();
            $table->text('note')->nullable();
            $table->string('matched_paperless_document_id')->nullable();
            $table->integer('bank_statement_id')->nullable(); // Link to which bank statement upload
            $table->boolean('is_validated')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
