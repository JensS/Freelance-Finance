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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['project', 'general'])->default('general');
            $table->string('project_name')->nullable();
            $table->date('service_period_start')->nullable();
            $table->date('service_period_end')->nullable();
            $table->string('service_location')->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->json('items'); // [{description, quantity, unit_price, unit, total}]
            $table->decimal('subtotal', 10, 2);
            $table->decimal('vat_rate', 5, 2)->default(19.00);
            $table->decimal('vat_amount', 10, 2);
            $table->decimal('total', 10, 2);
            $table->text('notes')->nullable();
            $table->string('paperless_document_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
