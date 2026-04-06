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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('categories')
                ->nullOnDelete();
            $table->string('image')->nullable()->after('category_id');
            $table->decimal('purchase_price', 10, 2)->default(0)->after('image');
            $table->decimal('selling_price', 10, 2)->default(0)->after('purchase_price');
            $table->unsignedInteger('stock')->default(0)->after('selling_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['image', 'purchase_price', 'selling_price', 'stock']);
        });
    }
};
