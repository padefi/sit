<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('voucher_type_invoice_type_relationships', function (Blueprint $table) {
            $table->unsignedBigInteger('idVT')->comment('id voucher type');
            $table->unsignedBigInteger('idIT')->comment('id invoice type');

            $table->index('idVT');
            $table->index('idIT');

            $table->foreign('idVT')
                ->references('id')
                ->on('voucher_types')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idIT')
                ->references('id')
                ->on('invoice_types')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('voucher_type_invoice_type_relationships', function (Blueprint $table) {
            $table->dropForeign(['idVT']);
            $table->dropForeign(['idIT']);
        });

        Schema::dropIfExists('voucher_type_invoice_type_relationships');
    }
};
