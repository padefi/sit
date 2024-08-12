<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('accountNumber', 50)->collation('utf8mb4_general_ci');
            $table->string('cbu', 22)->collation('utf8mb4_general_ci');
            $table->string('alias', 20)->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('idBank');
            $table->unsignedBigInteger('idAT');
            $table->unsignedBigInteger('idUserCreated');
            $table->unsignedBigInteger('idUserUpdated')->nullable();
            $table->timestamps();
            $table->boolean('status')->default(true);

            $table->index('idBank');
            $table->index('idAT');
            $table->index('idUserCreated');
            $table->index('idUserUpdated');

            $table->foreign('idBank')
                ->references('id')
                ->on('banks')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('idAT')
                ->references('id')
                ->on('bank_account_types')
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['idBank']);
            $table->dropForeign(['idAT']);
            $table->dropForeign(['idUserCreated']);
            $table->dropForeign(['idUserUpdated']);
        });

        Schema::dropIfExists('bank_accounts');
    }
};
