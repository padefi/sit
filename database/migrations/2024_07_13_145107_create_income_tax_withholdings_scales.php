<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('income_tax_withholdings_scales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('idCat');
            $table->decimal('rate', 5, 2);
            $table->decimal('minAmount', 10, 2);
            $table->decimal('maxAmount', 10, 2);
            $table->decimal('fixedAmount', 10, 2);
            $table->date('startAt');
            $table->date('endAt');
            $table->unsignedBigInteger('idUserCreated');
            $table->unsignedBigInteger('idUserUpdated')->nullable();
            $table->timestamps();

            $table->index('idUserCreated');
            $table->index('idUserUpdated');

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
        Schema::table('income_tax_withholdings_scales', function (Blueprint $table) {
            $table->dropForeign(['idUserCreated']);
            $table->dropForeign(['idUserUpdated']);
        });

        Schema::dropIfExists('income_tax_withholdings_scales');
    }
};
