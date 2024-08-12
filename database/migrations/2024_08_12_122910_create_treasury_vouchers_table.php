<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('treasury_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idType')->comment('id voucher type');
            $table->unsignedBigInteger('idSupplier');
            $table->unsignedBigInteger('idPM')->comment('id payment method');
            $table->unsignedBigInteger('idBA')->nullable()->comment('id bank account');
            $table->unsignedBigInteger('idVS')->comment('id voucher status');
            $table->decimal('amount', 10, 2);
            $table->decimal('incomeTaxAmount', 10, 2)->default(0);
            $table->decimal('socialTaxAmount', 10, 2)->default(0);
            $table->decimal('vatTaxAmount', 10, 2)->default(0);
            $table->decimal('totalAmount', 10, 2);
            $table->string('notes', 250)->collation('utf8mb4_general_ci')->nullable();
            $table->unsignedBigInteger('idUserCreated');
            $table->unsignedBigInteger('idUserUpdated')->nullable();
            $table->timestamps();

            $table->index('idType');
            $table->index('idSupplier');
            $table->index('idPM');
            $table->index('idBA');
            $table->index('idVS');
            $table->index('idUserCreated');
            $table->index('idUserUpdated');

            $table->foreign('idType')
                ->references('id')
                ->on('voucher_types')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idSupplier')
                ->references('id')
                ->on('suppliers')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idPM')
                ->references('id')
                ->on('payment_methods')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idBA')
                ->references('id')
                ->on('bank_accounts')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idVS')
                ->references('id')
                ->on('treasury_voucher_statuses')
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
        Schema::table('treasury_vouchers', function (Blueprint $table) {
            $table->dropForeign(['idType']);
            $table->dropForeign(['idSupplier']);
            $table->dropForeign(['idPM']);
            $table->dropForeign(['idBA']);
            $table->dropForeign(['idVS']);
            $table->dropForeign(['idUserCreated']);
            $table->dropForeign(['idUserUpdated']);
        });

        Schema::dropIfExists('treasury_vouchers');
    }
};
