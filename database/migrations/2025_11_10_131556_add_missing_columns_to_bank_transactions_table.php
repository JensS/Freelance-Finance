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
            $table->string('category')->nullable()->after('type');
            $table->text('description')->nullable()->after('title');
            $table->foreignId('invoice_id')->nullable()->constrained()->after('bank_statement_id');
            $table->boolean('is_business_expense')->default(false)->after('is_validated');
            $table->text('raw_data')->nullable()->after('is_business_expense');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn(['category', 'description', 'invoice_id', 'is_business_expense', 'raw_data']);
        });
    }
};
