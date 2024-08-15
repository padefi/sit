<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('voucher_to_treasury', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idVoucher');
            $table->unsignedBigInteger('idTV')->comment('id treasury voucher');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('idUserSent');
            $table->timestamp('related_at');

            $table->index('idVoucher');
            $table->index('idTV');
            $table->index('idUserSent');

            $table->foreign('idVoucher')
                ->references('id')
                ->on('vouchers')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idTV')
                ->references('id')
                ->on('treasury_vouchers')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idUserSent')
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
        Schema::table('voucher_to_treasury', function (Blueprint $table) {
            $table->dropForeign(['idVoucher']);
            $table->dropForeign(['idTV']);
            $table->dropForeign(['idUserSent']);
        });

        Schema::dropIfExists('voucher_to_treasury');
    }
};
