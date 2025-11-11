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
        Schema::table('cash_receipts', function (Blueprint $table) {
            // Add net/gross breakdown fields
            $table->decimal('net_amount', 10, 2)->nullable()->after('amount');
            $table->decimal('vat_rate', 5, 2)->nullable()->after('net_amount');
            $table->decimal('vat_amount', 10, 2)->nullable()->after('vat_rate');
        });

        // Calculate net/gross for existing records based on category field
        DB::statement("
            UPDATE cash_receipts
            SET
                vat_rate = CASE
                    WHEN category LIKE '%0%' THEN 0.00
                    WHEN category LIKE '%7%' THEN 7.00
                    WHEN category LIKE '%19%' THEN 19.00
                    ELSE 19.00
                END,
                net_amount = CASE
                    WHEN category LIKE '%0%' THEN amount
                    WHEN category LIKE '%7%' THEN ROUND(amount / 1.07, 2)
                    WHEN category LIKE '%19%' THEN ROUND(amount / 1.19, 2)
                    ELSE ROUND(amount / 1.19, 2)
                END
        ");

        // Calculate VAT amount
        DB::statement("
            UPDATE cash_receipts
            SET vat_amount = amount - net_amount
            WHERE net_amount IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_receipts', function (Blueprint $table) {
            $table->dropColumn(['net_amount', 'vat_rate', 'vat_amount']);
        });
    }
};
