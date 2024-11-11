<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->collation('utf8mb4_general_ci');
            $table->string('street', 100)->collation('utf8mb4_general_ci');
            $table->integer('streetNumber');
            $table->string('city', 100)->collation('utf8mb4_general_ci');
            $table->string('state', 100)->collation('utf8mb4_general_ci');
            $table->string('country', 100)->collation('utf8mb4_general_ci');
            $table->tinyText('postalCode')->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('osm_id');
            $table->string('latitude', 20);
            $table->string('longitude', 20);
            $table->string('phone', 20)->collation('utf8mb4_general_ci')->nullable();
            $table->string('email', 100)->collation('utf8mb4_general_ci')->nullable();
            $table->string('notes', 250)->collation('utf8mb4_general_ci')->nullable();
            $table->unsignedBigInteger('idUserCreated');
            $table->unsignedBigInteger('idUserUpdated')->nullable();
            $table->timestamps();

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
        Schema::table('banks', function (Blueprint $table) {
            $table->dropForeign(['idUserCreated']);
            $table->dropForeign(['idUserUpdated']);
        });

        Schema::dropIfExists('banks');
    }
};
