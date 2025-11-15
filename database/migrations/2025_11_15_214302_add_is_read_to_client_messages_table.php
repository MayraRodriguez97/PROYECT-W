<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // IF para evitar error si ya existe
        if (!Schema::hasColumn('client_messages', 'is_read')) {
            Schema::table('client_messages', function (Blueprint $table) {
                $table->boolean('is_read')->default(false)->after('message');
            });
        }
    }

    public function down()
    {
        // IF para evitar error al revertir
        if (Schema::hasColumn('client_messages', 'is_read')) {
            Schema::table('client_messages', function (Blueprint $table) {
                $table->dropColumn('is_read');
            });
        }
    }
};
