<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('voided_treasury_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idTV')->comment('id treasury voucher');
            $table->string('notes', 250)->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('idUserVoided');
            $table->timestamp('voided_at');

            $table->index('idTV');
            $table->index('idUserVoided');

            $table->foreign('idTV')
                ->references('id')
                ->on('treasury_vouchers')
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
        Schema::table('voided_treasury_vouchers', function (Blueprint $table) {
            $table->dropForeign(['idTV']);
            $table->dropForeign(['idUserVoided']);
        });

        Schema::dropIfExists('voided_treasury_vouchers');
    }
};