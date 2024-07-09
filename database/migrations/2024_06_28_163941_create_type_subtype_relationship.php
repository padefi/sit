<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('type_subtype_relationship', function (Blueprint $table) {
            $table->unsignedBigInteger('idType');
            $table->unsignedBigInteger('idSubtype');
            $table->unsignedBigInteger('idUserRelated');
            $table->timestamp('related_at')->nullable();

            $table->index('idType');
            $table->index('idSubtype');
            $table->index('idUserRelated');

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
        Schema::table('type_expense_relationship', function (Blueprint $table) {
            $table->dropForeign(['idType']);
            $table->dropForeign(['idSubtype']);
            $table->dropForeign(['idUserRelated']);
        });

        Schema::dropIfExists('type_expense_relationship');
    }
};
