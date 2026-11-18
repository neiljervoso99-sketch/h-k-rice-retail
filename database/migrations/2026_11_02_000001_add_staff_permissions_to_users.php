<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('staff_permissions')->nullable();
        });

        DB::table('users')
            ->where('role', 'staff')
            ->update([
                'staff_permissions' => json_encode([
                    'sales',
                    'manual_transactions',
                ]),
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('staff_permissions');
        });
    }
};