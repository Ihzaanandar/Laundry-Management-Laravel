<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Add indexes to frequently queried columns for better performance
     */
    public function up(): void
    {
        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('paymentStatus');
            $table->index('createdAt');
            $table->index(['status', 'paymentStatus']);
            $table->index(['createdAt', 'paymentStatus']);
        });

        // Customers table indexes
        Schema::table('customers', function (Blueprint $table) {
            $table->index('name');
            $table->index('phone');
        });

        // Order Items table indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('orderId');
            $table->index('serviceId');
        });

        // Services table indexes
        Schema::table('services', function (Blueprint $table) {
            $table->index('isActive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['paymentStatus']);
            $table->dropIndex(['createdAt']);
            $table->dropIndex(['status', 'paymentStatus']);
            $table->dropIndex(['createdAt', 'paymentStatus']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['phone']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['orderId']);
            $table->dropIndex(['serviceId']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['isActive']);
        });
    }
};
