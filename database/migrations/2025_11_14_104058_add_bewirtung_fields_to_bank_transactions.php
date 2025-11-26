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
            // Add Bewirtung (entertainment expense) tracking
            $table->boolean('is_bewirtung')->default(false)->after('is_business_expense');

            // JSON field for Bewirtung details (bewirtete Person, Anlass, Ort, Tag)
            $table->json('bewirtung_data')->nullable()->after('is_bewirtung');

            // Remove duplicate note field (keeping description)
            $table->dropColumn('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropColumn(['is_bewirtung', 'bewirtung_data']);
            $table->text('note')->nullable();
        });
    }
};
