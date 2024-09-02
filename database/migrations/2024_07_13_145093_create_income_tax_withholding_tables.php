<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('income_tax_withholding_tables', function (Blueprint $table) {
            $table->unsignedBigInteger('idCat')->unique();
            $table->string('table', 100)->collation('utf8mb4_general_ci');

            $table->index('idCat');

            $table->foreign('idCat')
                ->references('id')
                ->on('categories')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('income_tax_withholding_tables', function (Blueprint $table) {
            $table->dropForeign(['idCat']);
        });

        Schema::dropIfExists('income_tax_withholding_tables');
    }
};
