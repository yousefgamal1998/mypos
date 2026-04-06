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
        if (! Schema::hasTable('orders') || Schema::hasColumn('orders', 'customer_id')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->after('id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasColumn('orders', 'customer_id')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });
    }
};