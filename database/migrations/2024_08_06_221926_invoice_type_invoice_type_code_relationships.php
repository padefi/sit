<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('invoice_type_invoice_type_code_relationships', function (Blueprint $table) {
            $table->unsignedBigInteger('idIT')->comment('id invoice type');
            $table->unsignedBigInteger('idITCode')->comment('id voucher type');

            $table->index('idIT');
            $table->index('idITCode');

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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('invoice_type_invoice_type_code_relationships', function (Blueprint $table) {
            $table->dropForeign(['idIT']);
            $table->dropForeign(['idITCode']);
        });

        Schema::dropIfExists('invoice_type_invoice_type_code_relationships');
    }
};
