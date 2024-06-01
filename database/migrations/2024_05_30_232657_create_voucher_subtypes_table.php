<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('voucher_subtypes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('idUserCreated');
            $table->unsignedBigInteger('idUserUpdated')->nullable();;
            $table->timestamps();
            $table->boolean('status')->default(true);

            $table->index('idUserCreated');
            $table->index('idUserUpdated');

            $table->foreign('idUserCreated')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('idUserUpdated')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('voucher_subtype', function (Blueprint $table) {
            $table->dropForeign(['idUserCreated']);
            $table->dropForeign(['idUserUpdated']);
        });

        Schema::dropIfExists('voucher_subtypes');
    }
};
