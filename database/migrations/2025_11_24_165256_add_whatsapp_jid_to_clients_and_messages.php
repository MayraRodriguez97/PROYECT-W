<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Agregar campo whatsapp_jid a la tabla clients
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'whatsapp_jid')) {
                $table->string('whatsapp_jid', 255)->nullable()->after('phone');
                $table->index('whatsapp_jid');
            }
        });

        // Agregar campo from_jid a la tabla client_messages
        Schema::table('client_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('client_messages', 'from_jid')) {
                $table->string('from_jid', 255)->nullable()->after('from_number');
            }
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'whatsapp_jid')) {
                $table->dropColumn('whatsapp_jid');
            }
        });

        Schema::table('client_messages', function (Blueprint $table) {
            if (Schema::hasColumn('client_messages', 'from_jid')) {
                $table->dropColumn('from_jid');
            }
        });
    }
};
