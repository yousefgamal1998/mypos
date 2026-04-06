<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('categories', 'name')) {
            $defaultLocale = (string) config('app.locale', 'en');
            $timestamp = now();

            $rows = DB::table('categories')
                ->select(['id', 'name'])
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->get()
                ->map(fn ($category) => [
                    'category_id' => $category->id,
                    'name' => $category->name,
                    'locale' => $defaultLocale,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])
                ->all();

            if ($rows !== []) {
                DB::table('category_translations')->insertOrIgnore($rows);
            }

            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('categories', 'name')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('name')->nullable()->after('id');
            });
        }

        $defaultLocale = (string) config('app.locale', 'en');

        $translations = DB::table('category_translations')
            ->select(['category_id', 'name'])
            ->where('locale', $defaultLocale)
            ->get();

        foreach ($translations as $translation) {
            DB::table('categories')
                ->where('id', $translation->category_id)
                ->update(['name' => $translation->name]);
        }
    }
};
