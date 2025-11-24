<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientMessage;
use App\Models\WhatsappInstance;
use App\Models\Client as ClientModel;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::alert('WEBHOOK HIT: La solicitud llegó a Laravel.');
        $data = $request->all();
        // Log::debug('Payload recibido (RAW)', $data); // Descomenta si necesitas ver todo el JSON

        try {
            // 1. BUSCAR LA INSTANCIA
            $sessionApiKey = $data['sessionId'] ?? null;

            if (!$sessionApiKey) {
                return response()->json(['status' => 'ignored: missing root sessionId'], 200);
            }

            $instance = WhatsappInstance::where('api_key', $sessionApiKey)->first();

            if (!$instance) {
                Log::error('Instancia no encontrada para la API Key: ' . $sessionApiKey);
                return response()->json(['status' => 'instance_not_found'], 200);
            }

            // 2. PROCESAR EL MENSAJE
            $event = strtolower($data['event'] ?? 'message');

            if (str_contains($event, 'messages.received')) {

                $msgData = $data['data']['messages'] ?? null;

                if (!$msgData) {
                    return response()->json(['status' => 'unknown_structure'], 200);
                }

                // Ignorar mensajes salientes (ecos)
                if ($msgData['key']['fromMe'] ?? false) {
                    return response()->json(['status' => 'ignored_outbound_echo'], 200);
                }

                // --- DEFINICIÓN DE VARIABLES (AQUÍ ESTABA EL ERROR ANTES) ---
                $content = $msgData['message']['conversation'] ?? $msgData['messageBody'] ?? null;
                $timestamp = $msgData['messageTimestamp'] ?? time();
                // Esta es la línea que faltaba:
                $pushName = $msgData['pushName'] ?? 'Cliente Chat';

                if (!$content || trim($content) === '') {
                    return response()->json(['status' => 'ignored_empty'], 200);
                }

                // ========== EXTRACCIÓN CORRECTA DEL NÚMERO ==========

                $senderPn = $msgData['key']['senderPn'] ?? null;
                $cleanedSenderPn = $msgData['key']['cleanedSenderPn'] ?? null;
                $remoteJid = $msgData['key']['remoteJid'] ?? null;

                // Prioridad 1: senderPn (El más confiable)
                // Prioridad 2: cleanedSenderPn
                // Prioridad 3: remoteJid (SOLO si NO contiene @lid)

                $candidatePhone = null;

                if (!empty($senderPn)) {
                    $candidatePhone = $senderPn;
                } elseif (!empty($cleanedSenderPn)) {
                    $candidatePhone = $cleanedSenderPn;
                } elseif (!empty($remoteJid) && !str_contains($remoteJid, '@lid')) {
                    $candidatePhone = $remoteJid;
                }

                // Limpiar el número candidato
                $cleanPhone = $this->cleanSalvadorPhone($candidatePhone);

                Log::info('📱 Procesando Identidad:', [
                    'JID_Original' => $remoteJid,
                    'SenderPn' => $senderPn,
                    'Phone_Limpio' => $cleanPhone
                ]);

                // LÓGICA DE BÚSQUEDA Y CREACIÓN DE CLIENTE
                $client = null;

                // 1. Buscar por teléfono limpio (si existe)
                if ($cleanPhone) {
                    $client = ClientModel::where('phone', $cleanPhone)->first();
                }

                // 2. Si no, buscar por JID/LID (si ya estaba vinculado)
                if (!$client && $remoteJid) {
                    $client = ClientModel::where('whatsapp_jid', $remoteJid)->first();
                }

                // 3. Crear o Ignorar
                if (!$client) {
                    if ($cleanPhone) {
                        $client = ClientModel::create([
                            'phone' => $cleanPhone,
                            'whatsapp_jid' => $remoteJid,
                            'name' => $pushName, // Aquí se usa la variable que arreglamos
                            'dui' => '000000000',
                            'date' => Carbon::now()->toDateString()
                        ]);
                        Log::info('✅ Nuevo cliente creado.', ['id' => $client->id]);
                    } else {
                        Log::warning('⚠️ Mensaje de LID sin número asociado. Se ignora.', ['jid' => $remoteJid]);
                        return response()->json(['status' => 'ignored_no_phone_number'], 200);
                    }
                } else {
                    // Actualizar JID si es necesario
                    if ($client->whatsapp_jid !== $remoteJid) {
                        $client->update(['whatsapp_jid' => $remoteJid]);
                    }
                }

                // --- ASIGNACIÓN DE USUARIO Y GUARDADO DE MENSAJE ---

                $user = $client->users()->first()
                    ?? $instance->users()->first()
                    ?? User::role('admin')->first();
                $userId = $user?->id;

                if ($user && !$client->users->contains($user)) {
                    $client->users()->attach($user->id);
                }

                ClientMessage::create([
                    'client_id' => $client->id,
                    'user_id' => $userId,
                    'whatsapp_instance_id' => $instance->id,
                    'from_number' => $cleanPhone ?? 'Desconocido', // Fallback visual
                    'from_jid' => $remoteJid,
                    'to_number' => $instance->phone,
                    'message' => $content,
                    'direction' => 'inbound',
                    'is_read' => false,
                    'received_at' => Carbon::createFromTimestamp($timestamp),
                ]);

                Log::info('💾 MENSAJE GUARDADO CORRECTAMENTE', [
                    'client_id' => $client->id,
                    'from' => $cleanPhone
                ]);

            } else {
                Log::info('Evento no procesado: ' . $event);
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error FATAL en webhook: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Limpia números de El Salvador de forma SEGURA.
     */
    private function cleanSalvadorPhone($phone)
    {
        if (!$phone) return null;

        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Si tiene más de 12 dígitos, es basura/LID.
        if (strlen($cleaned) > 12) {
            return null;
        }

        // Si es 503 + 8 dígitos
        if (str_starts_with($cleaned, '503') && strlen($cleaned) === 11) {
            return substr($cleaned, 3, 8);
        }

        // Si son 8 dígitos exactos
        if (strlen($cleaned) === 8) {
            return $cleaned;
        }

        // Recuperación de formato extraño
        if (strlen($cleaned) > 8 && str_starts_with($cleaned, '503')) {
            return substr($cleaned, -8);
        }

        return $cleaned;
    }
}
