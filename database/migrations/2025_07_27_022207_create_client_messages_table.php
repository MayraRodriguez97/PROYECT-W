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
        Schema::create('client_messages', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->unsignedBigInteger('client_id')->nullable(); // Cliente relacionado
            $table->unsignedBigInteger('user_id')->nullable();   // Encargado del cliente

            // Teléfonos (para trazabilidad externa)
            $table->string('from_number')->nullable();  // número del remitente
            $table->string('to_number')->nullable();    // número del destinatario

            // Contenido del mensaje
            $table->text('message');

            // Dirección del mensaje
            $table->enum('direction', ['inbound', 'outbound']);

            // Fecha/hora que envía WAAPI (no siempre coincide con created_at)
            $table->timestamp('received_at')->nullable();

            $table->timestamps();

            // Llaves foráneas
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->unsignedBigInteger('sender_user_id')->nullable();
            $table->foreign('sender_user_id')->references('id')->on('users')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_messages');
    }
};
