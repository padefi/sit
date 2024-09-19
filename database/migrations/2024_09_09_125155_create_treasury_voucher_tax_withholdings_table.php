<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('treasury_voucher_tax_withholdings', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('idVoucher')->nullable();
            $table->unsignedBigInteger('idOTV')->comment('id original treasury voucher');
            $table->unsignedBigInteger('idNTV')->comment('id new treasury voucher');
            $table->unsignedBigInteger('idTT')->comment('id tax type');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('idUserCreated');
            $table->timestamp('created_at');

            // $table->index('idVoucher');
            $table->index('idOTV');
            $table->index('idNTV');
            $table->index('idTT');
            $table->index('idUserCreated');

            /* $table->foreign('idVoucher')
                ->references('id')
                ->on('vouchers')
                ->onUpdate('restrict')
                ->onDelete('restrict'); */

            $table->foreign('idOTV')
                ->references('id')
                ->on('treasury_vouchers')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idNTV')
                ->references('id')
                ->on('treasury_vouchers')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idTT')
                ->references('id')
                ->on('tax_types')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idUserCreated')
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
        Schema::table('treasury_voucher_tax_withholdings', function (Blueprint $table) {
            // $table->dropForeign(['idVoucher']);
            $table->dropForeign(['idOTV']);
            $table->dropForeign(['idNTV']);
            $table->dropForeign(['idTT']);
            $table->dropForeign(['idUserCreated']);
        });

        Schema::dropIfExists('treasury_voucher_tax_withholdings');
    }
};
