<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 20)->unique()->collation('utf8mb4_general_ci');
            $table->string('email', 255)->unique()->collation('utf8mb4_general_ci');
            $table->string('password', 255)->collation('utf8mb4_general_ci');
            $table->string('name', 50)->collation('utf8mb4_general_ci');
            $table->string('surname', 50)->collation('utf8mb4_general_ci');
            $table->boolean('is_active')->default(true);;
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('users');
    }
};
