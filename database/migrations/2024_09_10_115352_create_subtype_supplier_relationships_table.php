<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('subtype_supplier_relationships', function (Blueprint $table) {
            $table->unsignedBigInteger('idSubtype');
            $table->unsignedBigInteger('idSupplier');
            $table->unsignedBigInteger('idUserRelated');
            $table->timestamp('related_at')->nullable();

            $table->index('idSubtype');
            $table->index('idSupplier');
            $table->index('idUserRelated');

            $table->foreign('idSubtype')
                ->references('id')
                ->on('voucher_subtypes')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idSupplier')
                ->references('id')
                ->on('suppliers')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idUserRelated')
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
        Schema::table('subtype_supplier_relationships', function (Blueprint $table) {
            $table->dropForeign(['idSubtype']);
            $table->dropForeign(['idSupplier']);
            $table->dropForeign(['idUserRelated']);
        });

        Schema::dropIfExists('subtype_supplier_relationships');
    }
};
