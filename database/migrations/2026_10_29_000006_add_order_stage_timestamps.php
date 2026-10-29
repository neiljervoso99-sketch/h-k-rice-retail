<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
        });

        DB::table('orders')
            ->whereIn('status', ['accepted', 'completed'])
            ->update(['processing_at' => DB::raw('COALESCE(accepted_at, updated_at)')]);

        DB::table('orders')
            ->whereNotNull('assigned_at')
            ->update(['shipped_at' => DB::raw('assigned_at')]);

        DB::table('orders')
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->update(['delivered_at' => DB::raw('completed_at')]);

        DB::table('orders')
            ->where('status', 'cancelled')
            ->update(['cancelled_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['processing_at', 'shipped_at', 'delivered_at', 'cancelled_at']);
        });
    }
};