<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idTV')->comment('id treasury voucher');
            $table->decimal('amount', 10, 2)->default(0);
            $table->unsignedBigInteger('idUserConfirmed')->nullable();
            $table->unsignedBigInteger('idUserVoided')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->boolean('status')->default(true);

            $table->index('idTV');
            $table->index('idUserConfirmed');
            $table->index('idUserVoided');

            $table->foreign('idTV')
                ->references('id')
                ->on('treasury_vouchers')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idUserConfirmed')
                ->references('id')
                ->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idUserVoided')
                ->references('id')
                ->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropForeign(['idTV']);
            $table->dropForeign(['idUserConfirmed']);
            $table->dropForeign(['idUserVoided']);
        });

        Schema::dropIfExists('cash_transactions');
    }
};
