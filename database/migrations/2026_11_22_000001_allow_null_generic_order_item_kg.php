<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('quantity_kg', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('order_items')
            ->whereNull('quantity_kg')
            ->update(['quantity_kg' => 0]);

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('quantity_kg', 12, 2)->nullable(false)->change();
        });
    }
};
