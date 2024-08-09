<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idSupplier');
            $table->unsignedBigInteger('idType');
            $table->unsignedBigInteger('idSubtype');
            $table->unsignedBigInteger('idExpense');
            $table->unsignedBigInteger('idIT')->comment('id invoice type');
            $table->unsignedBigInteger('idITCode')->comment('id invoice type code');
            $table->integer('pointOfNumber');
            $table->integer('invoiceNumber');
            $table->date('invoiceDate');
            $table->date('invoicePaymentDate');
            $table->unsignedBigInteger('idPC')->comment('id pay condition');
            $table->string('notes', 250)->collation('utf8mb4_general_ci')->nullable();
            $table->decimal('totalAmount', 10, 2);
            $table->unsignedBigInteger('idUserCreated');
            $table->unsignedBigInteger('idUserUpdated')->nullable();
            $table->timestamps();

            $table->index('idSupplier');
            $table->index('idType');
            $table->index('idSubtype');
            $table->index('idExpense');
            $table->index('idIT');
            $table->index('idITCode');
            $table->index('idPC');
            $table->index('idUserCreated');
            $table->index('idUserUpdated');

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

            $table->foreign('idIT')
                ->references('id')
                ->on('invoice_types')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idITCode')
                ->references('id')
                ->on('invoice_type_codes')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idPC')
                ->references('id')
                ->on('pay_conditions')
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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['idSupplier']);
            $table->dropForeign(['idType']);
            $table->dropForeign(['idSubtype']);
            $table->dropForeign(['idExpense']);
            $table->dropForeign(['idIT']);
            $table->dropForeign(['idITCode']);
            $table->dropForeign(['idPC']);
            $table->dropForeign(['idUserCreated']);
            $table->dropForeign(['idUserUpdated']);
        });

        Schema::dropIfExists('vouchers');
    }
};
