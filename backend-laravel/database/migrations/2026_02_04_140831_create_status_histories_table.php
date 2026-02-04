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
        Schema::create('status_history', function (Blueprint $table) { // Note: using 'status_history' table name to match Prisma map
            $table->uuid('id')->primary();
            $table->foreignUuid('orderId')->constrained('orders')->onDelete('cascade');
            $table->string('status');
            $table->string('changedBy');
            $table->timestamp('changedAt')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};
