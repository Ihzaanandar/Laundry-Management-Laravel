<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('paymentId')->nullable(); // External Payment ID (e.g. Midtrans)
            $table->foreignUuid('orderId')->unique()->constrained('orders')->onDelete('cascade'); // Prisma: One-to-One
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('TUNAI');
            $table->timestamp('paidAt')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
