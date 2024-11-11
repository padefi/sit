<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('voided_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idVoucher');
            $table->string('notes', 250)->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('idUserVoided');
            $table->timestamp('voided_at');

            $table->index('idVoucher');
            $table->index('idUserVoided');

            $table->foreign('idVoucher')
                ->references('id')
                ->on('vouchers')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idUserVoided')
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
        Schema::table('voided_vouchers', function (Blueprint $table) {
            $table->dropForeign(['idVoucher']);
            $table->dropForeign(['idUserVoided']);
        });

        Schema::dropIfExists('voided_vouchers');
    }
};
