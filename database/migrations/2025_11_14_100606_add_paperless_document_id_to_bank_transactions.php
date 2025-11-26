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
        Schema::table('bank_transactions', function (Blueprint $table) {
            // Link to Paperless document (nullable - not all transactions have documents)
            $table->integer('paperless_document_id')->nullable()->after('vat_amount');
            $table->string('paperless_document_title')->nullable()->after('paperless_document_id');

            // Month and year for better organization
            $table->integer('month')->nullable()->after('transaction_date');
            $table->integer('year')->nullable()->after('month');

            $table->index(['month', 'year']);
            $table->index('paperless_document_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropIndex(['month', 'year']);
            $table->dropIndex(['paperless_document_id']);
            $table->dropColumn(['paperless_document_id', 'paperless_document_title', 'month', 'year']);
        });
    }
};
