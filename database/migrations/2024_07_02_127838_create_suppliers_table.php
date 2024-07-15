<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->collation('utf8mb4_general_ci');
            $table->string('businessName', 100)->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('cuit')->unique();
            $table->unsignedBigInteger('idVC');
            $table->unsignedBigInteger('idCat');
            $table->string('street', 100)->collation('utf8mb4_general_ci');
            $table->integer('streetNumber');
            $table->tinyText('floor')->collation('utf8mb4_general_ci')->nullable();
            $table->tinyText('apartment')->collation('utf8mb4_general_ci')->nullable();
            $table->string('city', 100)->collation('utf8mb4_general_ci');
            $table->string('state', 100)->collation('utf8mb4_general_ci');
            $table->string('country', 100)->collation('utf8mb4_general_ci');
            $table->tinyText('postalCode')->collation('utf8mb4_general_ci');
            $table->unsignedBigInteger('osm_id');
            $table->string('latitude', 20);
            $table->string('longitude', 20);
            $table->string('phone', 20)->collation('utf8mb4_general_ci')->nullable();
            $table->string('email', 255)->collation('utf8mb4_general_ci')->nullable();
            $table->string('cbu', 22)->collation('utf8mb4_general_ci')->nullable();
            $table->string('notes', 250)->collation('utf8mb4_general_ci')->nullable();
            $table->boolean('incomeTaxWithholding')->default(false);
            $table->boolean('socialTax')->default(false);
            $table->boolean('vatTax')->default(false);
            $table->unsignedBigInteger('idUserCreated');
            $table->unsignedBigInteger('idUserUpdated')->nullable();
            $table->timestamps();

            $table->index('idVC');
            $table->index('idCat');
            $table->index('idUserCreated');
            $table->index('idUserUpdated');

            $table->foreign('idVC')
                ->references('id')
                ->on('vat_conditions')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('idCat')
                ->references('id')
                ->on('categories')
                ->onUpdate('restrict')
                ->onDelete('restrict');

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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['idVC']);
            $table->dropForeign(['idCat']);
            $table->dropForeign(['idUserCreated']);
            $table->dropForeign(['idUserUpdated']);
        });

        Schema::dropIfExists('suppliers');
    }
};
