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
        Schema::create('stripe_payments', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_payment_intent_id')->unique()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->bigInteger('amount'); // Amount in cents
            $table->string('currency', 3)->default('usd');
            $table->string('status')->index(); // PaymentIntent status
            $table->string('payment_method')->nullable();
            $table->text('client_secret')->nullable();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->text('last_payment_error')->nullable();
            $table->timestamps();

            // Foreign keys
            // Uncomment if you want to enforce foreign key constraints
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('stripe_customer_id')->references('stripe_customer_id')->on('stripe_customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_payments');
    }
};
