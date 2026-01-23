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
        Schema::create('mercadopago_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // References
            $table->string('external_reference')->unique()->index();
            $table->string('mercadopago_id')->nullable()->unique()->index();
            $table->string('order_id')->nullable()->index();

            // Payment details
            $table->string('payment_method');
            $table->string('payment_type');
            $table->string('status')->index();
            $table->string('status_detail')->nullable();

            // Amount
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('BRL');

            // Payer information
            $table->string('payer_email')->index();
            $table->string('payer_name')->nullable();
            $table->string('payer_document')->nullable();

            // Description and metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            // PIX specific fields
            $table->text('qr_code')->nullable();
            $table->longText('qr_code_base64')->nullable();

            // Boleto/Ticket specific fields
            $table->text('ticket_url')->nullable();
            $table->string('barcode')->nullable();

            // Expiration
            $table->timestamp('expiration_date')->nullable();

            // Status timestamps
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['status', 'created_at']);
            $table->index(['payment_method', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercadopago_payments');
    }
};
