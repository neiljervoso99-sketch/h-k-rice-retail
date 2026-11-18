<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_register_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('opening_cash', 12, 2);
            $table->decimal('closing_cash', 12, 2)->nullable();
            $table->decimal('expected_cash', 12, 2)->nullable();
            $table->decimal('cash_variance', 12, 2)->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['opened_by', 'status']);
            $table->index(['status', 'opened_at']);
        });

        Schema::create('cash_register_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_shift_id')
                ->constrained('cash_register_shifts')
                ->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('movement_type', 20);
            $table->decimal('amount', 12, 2);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['cash_register_shift_id', 'movement_type']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('cash_register_shift_id')
                ->nullable()
                ->after('user_id')
                ->constrained('cash_register_shifts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_register_shift_id');
        });

        Schema::dropIfExists('cash_register_movements');
        Schema::dropIfExists('cash_register_shifts');
    }
};
