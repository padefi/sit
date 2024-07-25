<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('subtype_expense_relationships', function (Blueprint $table) {
            $table->unsignedBigInteger('idSubtype');
            $table->unsignedBigInteger('idExpense');
            $table->unsignedBigInteger('idUserRelated');
            $table->timestamp('related_at')->nullable();

            $table->index('idSubtype');
            $table->index('idExpense');
            $table->index('idUserRelated');

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
        Schema::table('subtype_expense_relationships', function (Blueprint $table) {
            $table->dropForeign(['idSubtype']);
            $table->dropForeign(['idExpense']);
            $table->dropForeign(['idUserRelated']);
        });

        Schema::dropIfExists('subtype_expense_relationships');
    }
};
