<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('clients', function (Blueprint $table) {
        // --- PEGA ESTA LÍNEA ---
        $table->string('avatar_url')->nullable()->after('dui');
        // ------------------------
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('clients', function (Blueprint $table) {
        // --- PEGA ESTA LÍNEA ---
        $table->dropColumn('avatar_url');
        // ------------------------
    });
}
};
