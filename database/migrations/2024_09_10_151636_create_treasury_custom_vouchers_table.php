<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('treasury_custom_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idTV')->comment('id original treasury voucher');
            $table->unsignedBigInteger('idSupplier');
            $table->unsignedBigInteger('idType');
            $table->unsignedBigInteger('idSubtype');
            $table->unsignedBigInteger('idExpense')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('notes', 250)->collation('utf8mb4_general_ci')->nullable();
            $table->date('voucherDate');
            $table->unsignedBigInteger('idUserCreated');
            $table->unsignedBigInteger('idUserUpdated')->nullable();
            $table->timestamps();

            $table->index('idTV');
            $table->index('idSupplier');
            $table->index('idType');
            $table->index('idSubtype');
            $table->index('idExpense');
            $table->index('idUserCreated');
            $table->index('idUserUpdated');

            $table->foreign('idTV')
                ->references('id')
                ->on('treasury_vouchers')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idSupplier')
                ->references('id')
                ->on('suppliers')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idType')
                ->references('id')
                ->on('voucher_types')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idSubtype')
                ->references('id')
                ->on('voucher_subtypes')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idExpense')
                ->references('id')
                ->on('voucher_expenses')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idUserCreated')
                ->references('id')
                ->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idUserUpdated')
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
        Schema::table('treasury_custom_vouchers', function (Blueprint $table) {
            $table->dropForeign(['idTV']);
            $table->dropForeign(['idSupplier']);
            $table->dropForeign(['idType']);
            $table->dropForeign(['idSubtype']);
            $table->dropForeign(['idExpense']);
            $table->dropForeign(['idUserCreated']);
            $table->dropForeign(['idUserUpdated']);
        });

        Schema::dropIfExists('treasury_custom_vouchers');
    }
};
