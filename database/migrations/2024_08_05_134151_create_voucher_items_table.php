<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('voucher_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idVoucher');
            $table->string('description', 100)->collation('utf8mb4_general_ci');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('idVat');
            $table->decimal('subtotalAmount', 10, 2);

            $table->index('idVoucher');
            $table->index('idVat');

            $table->foreign('idVoucher')
                ->references('id')
                ->on('vouchers')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idVat')
                ->references('id')
                ->on('vat_rates')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('voucher_items', function (Blueprint $table) {
            $table->dropForeign(['idVoucher']);
            $table->dropForeign(['idVat']);
        });

        Schema::dropIfExists('voucher_items');
    }
};
