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
        // Esta vez, logueamos el payload real sin modificarlo
        Log::debug(' Payload recibido (RAW)', $data); 

        try {
            // ------------------------------------------------------------------
            // 1. BUSCAR LA INSTANCIA
            // ------------------------------------------------------------------

            // 'sessionId' está en el NIVEL RAÍZ del payload ($data)
            $sessionApiKey = $data['sessionId'] ?? null;

            if (!$sessionApiKey) {
                // Si esto falla, el payload es totalmente inesperado
                Log::warning(' Webhook ignorado: No se encontró "sessionId" en la raíz del payload.', $data);
                return response()->json(['status' => 'ignored: missing root sessionId'], 200);
            }

            // ¡Buscamos la instancia por su 'api_key' (que es el sessionId)!
            $instance = WhatsappInstance::where('api_key', $sessionApiKey)->first();
            
            if (!$instance) {
                Log::error('Instancia no encontrada para la API Key (sessionId): ' . $sessionApiKey);
                return response()->json(['status' => 'instance_not_found'], 200);
            }

            // ------------------------------------------------------------------
            // 2. PROCESAR EL MENSAJE
            // ------------------------------------------------------------------

            // 'event' también está en el NIVEL RAÍZ
            $event = strtolower($data['event'] ?? 'message');
            Log::info('🔍 Evento recibido:', ['event' => $event]);

            if (str_contains($event, 'messages.received')) {
                
                // Pero el mensaje está en el NIVEL ANIDADO: $data['data']['messages']
                $msgData = $data['data']['messages'] ?? null; 
                
                if (!$msgData) {
                    Log::error(' Estructura de mensaje desconocida, falta "data.data.messages".', $data);
                    return response()->json(['status' => 'unknown_structure'], 200);
                }

                // Ignoramos los mensajes que nosotros mismos enviamos (ecos)
                if ($msgData['key']['fromMe'] ?? false) {
                    Log::info('📤 Mensaje saliente (eco de webhook), se ignora.');
                    return response()->json(['status' => 'ignored_outbound_echo'], 200);
                }

                // Ajustamos los nombres de los campos a la estructura REAL del log
                $fromJid = $msgData['key']['remoteJid'] ?? null; 
                $content = $msgData['message']['conversation'] ?? $msgData['messageBody'] ?? null;
                $timestamp = $msgData['messageTimestamp'] ?? time();

                
                if ($fromJid && $content && trim($content) !== '') {
                    
                    // --- INICIO DE LA CORRECCIÓN DE NORMALIZACIÓN (REEMPLAZAR ESTE BLOQUE) ---
                    // 1. Limpieza básica: Eliminar todo lo que no sea número del JID
                    $rawPhone = preg_replace('/[^0-9]/', '', $fromJid); 

                    // 2. Extracción del ID de Cliente (8 dígitos)
                    // Este será el ID de búsqueda en tu tabla 'clients'.
                    if (strlen($rawPhone) >= 8) {
                        // Tomamos los últimos 8 dígitos. Esto funciona para:
                        // 503XXXXXXXX -> XXXXXXXX
                        // (CWID largo) -> últimos 8 dígitos
                        $cleanClientPhone = substr($rawPhone, -8);
                    } else {
                        // Si es menor a 8 dígitos, puede ser inválido o un ID corto.
                        $cleanClientPhone = $rawPhone;
                    }

                    // 3. Formato E.164 (11 dígitos) para guardar en ClientMessages
                    // Esto asegura que la columna 'from_number' no tenga el sufijo JID
                    $messageFromNumber = '503' . $cleanClientPhone;
                    // --- FIN DE LA CORRECCIÓN DE NORMALIZACIÓN ---

                    
                    // 1. Encontrar o crear al cliente
                    $client = ClientModel::firstOrCreate(
                        ['phone' => $cleanClientPhone], // USANDO EL NÚMERO DE 8 DÍGITOS ESTANDARIZADO
                        ['name' => $msgData['pushName'] ?? 'Cliente Chat', 'dui' => '000000000', 'date' => Carbon::now()->toDateString()]
                    );

                    // 2. Asignar el mensaje a un usuario
                    $user = $client->users()->first()
                        ?? $instance->users()->first()
                        ?? User::role('admin')->first();
                    $userId = $user?->id;

                    // 3. Vincular cliente y usuario
                    if ($user && !$client->users->contains($user)) {
                        $client->users()->attach($user->id);
                    }

                    // 4. Guardar el mensaje en la base de datos
                    ClientMessage::create([
                        'client_id' => $client->id,
                        'user_id' => $userId,
                        'whatsapp_instance_id' => $instance->id,
                        'from_number' => $messageFromNumber, // <--- ¡USANDO EL FORMATO ESTANDARIZADO DE 11 DÍGITOS!
                        'to_number' => $instance->phone, 
                        'message' => $content,
                        'direction' => 'inbound',
                        'is_read' => false,
                        'received_at' => Carbon::createFromTimestamp($timestamp),
                    ]);

                    Log::info(' MENSAJE ENTRANTE GUARDADO! Cliente: ' . $cleanClientPhone);
                
                } else {
                    Log::info(' Mensaje incompleto o vacío, no se guarda.', $msgData);
                    return response()->json(['status' => 'ignored_empty'], 200);
                }
            } else {
                Log::info(' Evento no procesado: ' . $event);
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Error FATAL en webhook: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}