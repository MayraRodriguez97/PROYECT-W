<?php

namespace App\Http\Controllers;

use App\Models\WhatsappInstance;
use App\Models\Client as ClientModel;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;
use App\Models\MessageTemplate;
use App\Models\MoratoriumClassification;
use App\Models\ClientMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    // ----------------------------------------------------------------------
    // FUNCIONES DE CARGA Y PREVISUALIZACIÓN (SIN CAMBIOS)
    // ----------------------------------------------------------------------

    public function formUploadExcel()
    {
        return view('subir-excel');
    }

    public function subirExcel(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $coleccion = Excel::toCollection(null, $request->file('excel'));
        if ($coleccion->isEmpty()) {
            return back()->withErrors(['El archivo está vacío o no se pudo leer.']);
        }

        $hoja = $coleccion->first()->slice(1);
        $datosPorCategoria = [];

        foreach ($hoja as $fila) {
            if (!isset($fila[0]) || !isset($fila[2]) || empty($fila[0]) || empty($fila[2])) {
                continue;
            }

            $numero = preg_replace('/\D/', '', $fila[0]);
            if (str_starts_with($numero, '503')) {
                $numero = substr($numero, 3);
            }
            
            $estatus = isset($fila[1]) ? trim($fila[1]) : '';
            $clasificacion = trim($fila[2]);
            $nombre = isset($fila[3]) ? trim($fila[3]) : '';
            $factura = isset($fila[4]) ? trim($fila[4]) : '';
            $monto = isset($fila[5]) ? trim($fila[5]) : '';
            $dui = isset($fila[6]) ? trim($fila[6]) : null;
            $encargadoCorreo = isset($fila[6]) ? trim(strtolower($fila[6])) : null;
            $encargadoId = $encargadoCorreo
            ? User::where('email', $encargadoCorreo)->value('id')
            : null;

            if ($numero && $clasificacion) {
                $datosPorCategoria[$clasificacion][] = [
                    'numero' => $numero,
                    'tipo_mensaje' => $estatus,
                    'nombre' => $nombre,
                    'factura' => $factura,
                    'monto' => $monto,
                    'dui' => $dui,
                    'encargado_id' => $encargadoId,
                    'encargadoCorreo' => $encargadoCorreo,
                ];

                if ($encargadoId) {
                    $cliente = ClientModel::firstOrCreate(
                        ['phone' => $numero], // Número de 8 dígitos
                        [
                            'name' => $nombre,
                            'dui' => $dui ?? '000000000',
                            'date' => now()->toDateString(),
                        ]
                    );
                    $cliente->users()->syncWithoutDetaching([$encargadoId]);
                }
            }
        }

        if (empty($datosPorCategoria)) {
            return back()->withErrors(['El archivo no contiene datos válidos con número y clasificación.']);
        }

        session(['numeros_por_categoria' => $datosPorCategoria]);
        return redirect()->route('messages-preview');
    }


    public function previewMessages()
    {
        $datosPorCategoria = session('numeros_por_categoria', []);
        $mensajesPorCategoria = [];

        foreach ($datosPorCategoria as $categoriaExcel => $numeros) {
            $clasificacion = MoratoriumClassification::where('name', 'LIKE', $categoriaExcel)->first();
            if (!$clasificacion) continue;

            $plantilla = MessageTemplate::where('moratorium_classification_id', $clasificacion->id)->first();
            if (!$plantilla) continue;

            foreach ($numeros as $dato) {
                $msgPersonalizado = str_replace(
                    ['{nombre}', '{factura}', '{monto}'],
                    [$dato['nombre'], $dato['factura'], $dato['monto']],
                    $plantilla->template
                );

                $mensajesPorCategoria[$clasificacion->name][] = [
                    'numero' => $dato['numero'],
                    'mensaje' => $msgPersonalizado,
                ];
            }
        }
        return view('messages-preview', compact('mensajesPorCategoria'));
    }
    
    public function formSendMessages()
    {
        $categorias = session('numeros_por_categoria', []);
        $instances = WhatsappInstance::all(); 
        return view('send-messages', compact('categorias', 'instances'));
    }


    // ----------------------------------------------------------------------
    // ENVÍO MASIVO
    // ----------------------------------------------------------------------

    public function sendMessage(Request $request)
    {
        $datosPorCategoria = session('numeros_por_categoria', []);
        if (empty($datosPorCategoria)) {
            return back()->withErrors(['message' => 'No hay números cargados.']);
        }

        $request->validate(['whatsapp_instance_id' => 'required|exists:whatsapp_instances,id']);
        
        $instance = WhatsappInstance::findOrFail($request->whatsapp_instance_id);
        
        $apiKey = $instance->api_key; 
        $sendMessageUrl = "https://wasenderapi.com/api/send-message";
        $sessionName = $instance->name; 
        
        $senderUserId = Auth::id();
        
        // Corrección para 'super admin'
        $isAdmin = Auth::user()->isSuperAdmin() || Auth::user()->hasRole('admin');

        // REGLA: Solo los administradores pueden enviar mensajes masivos
        if (!$isAdmin) {
            Log::warning("El usuario no-admin (ID: $senderUserId) intentó hacer un envío masivo y fue bloqueado.");
            return back()->withErrors(['message' => 'No tienes permiso de administrador para realizar envíos masivos.']);
        }
        
        $client = new Client(['verify' => false]); 
        $totalSent = 0;
        $totalFailed = 0;
        
        foreach ($datosPorCategoria as $categoria => $numeros) {
            $plantilla = MessageTemplate::whereHas('classification', function ($query) use ($categoria) {
                $query->where('name', $categoria);
            })->first();

            if (!$plantilla) continue;

            foreach ($numeros as $dato) {
                $clientName = $dato['nombre'] ?? 'Cliente Sin Nombre'; 
                $clientDui = $dato['dui'] ?? '000000000'; 
                $currentDate = now()->toDateString(); 
                
                $encargadoCorreo = $dato['encargadoCorreo'] ?? null;
                $encargadoId = $senderUserId; 

                if ($encargadoCorreo) {
                    $encargado = User::where('email', $encargadoCorreo)->first();
                    if ($encargado) {
                        $encargadoId = $encargado->id;
                    }
                }

                $clientModel = ClientModel::firstOrCreate(
                    ['phone' => $dato['numero']],
                    [
                        'name' => $clientName,
                        'dui' => $clientDui, 
                        'date' => $currentDate,
                        'moratorium_classification_id' => $plantilla->moratorium_classification_id ?? null 
                    ]
                );

                if ($encargadoId && $user = User::find($encargadoId)) {
                    $clientModel->users()->syncWithoutDetaching([$encargadoId]);
                }

                $chatId = "503{$dato['numero']}";
                $msgPersonalizado = str_replace(
                    ['{nombre}', '{factura}', '{monto}'],
                    [$dato['nombre'], $dato['factura'], $dato['monto']],
                    $plantilla->template
                );

                try {
                    $response = $client->post($sendMessageUrl, [
                        'headers' => $this->getDynamicHeaders($apiKey), 
                        'json' => [
                            'session' => $sessionName,
                            'to'      => $chatId, 
                            'text'    => $msgPersonalizado
                        ],
                    ]);

                    if ($response->getStatusCode() >= 400) {
                        $totalFailed++;
                        continue;
                    }

                    ClientMessage::create([
                        'client_id' => $clientModel->id,
                        'whatsapp_instance_id' => $instance->id,
                        'from_number' => $instance->phone,
                        'to_number' => $dato['numero'],
                        'message' => $msgPersonalizado,
                        'direction' => 'outbound',
                        'is_read' => true, 
                        'received_at' => now(),
                        'user_id' => $encargadoId,
                    ]);
                    $totalSent++;

                } catch (ClientException | ServerException | \Exception $e) {
                    
                    $numeroDebug = $dato['numero'] ?? 'desconocido';
                    $errorDetails = method_exists($e, 'hasResponse') ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
                    Log::error("Fallo de API: Error al intentar enviar al número $numeroDebug. Detalles: " . $errorDetails);

                    $totalFailed++;
                    continue; 
                }

                // --- ¡¡AQUÍ ESTÁ LA PAUSA!! ---
                sleep(5); // Pausa de 5 segundos para cumplir con la API

            } // Fin foreach $numeros
        } // Fin foreach $datosPorCategoria

        session()->forget('numeros_por_categoria'); 
        $mensajeFinal = "Proceso terminado. Mensajes enviados: {$totalSent}. Fallidos: {$totalFailed}.";
        return back()->with('status', $mensajeFinal);
    }


    // ----------------------------------------------------------------------
    // RESPUESTA MANUAL
    // ----------------------------------------------------------------------
    
    public function reply(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'whatsapp_instance_id' => 'required|exists:whatsapp_instances,id',
            'message' => 'nullable|string|max:4096',
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp3,ogg,mp4,pdf|max:10240',
        ]);

        if (empty($request->message) && !$request->hasFile('media_file')) {
            return back()->withErrors(['message' => 'Debes escribir un mensaje o adjuntar un archivo.']);
        }

        $instance = WhatsappInstance::findOrFail($request->whatsapp_instance_id);
        $numero = preg_replace('/[^0-9]/', '', $request->phone); 
        
        $senderUserId = Auth::id();
        $user = Auth::user();
        $clientGuzzle = new Client(['verify' => false]);
        
        $apiRecipient = "503{$numero}"; 

        $clientModel = ClientModel::firstOrCreate(
            ['phone' => $numero], 
            ['name' => 'Cliente Chat', 'dui' => '000000000', 'date' => now()->toDateString()]
        );
        $clientModel->users()->syncWithoutDetaching([$senderUserId]);

        // --- ¡¡CORRECCIÓN DE SUPER ADMIN (2/3)!! ---
        if (!$user->isSuperAdmin() && !$user->hasRole('admin') && !$clientModel->users->contains($user)) {
            return back()->withErrors(['message' => 'No tienes permiso para responder a este cliente.']);
        }

        $messageContent = $request->message;
        $mediaUrl = null;
        $mediaType = null;

        try {
            
            // CASO 1: SE ENVÍA UN ARCHIVO
            if ($request->hasFile('media_file')) {
                $file = $request->file('media_file');
                $mimeType = $file->getMimeType();
                
                $path = $file->store('media/' . $instance->id, 'public');
                $mediaUrl = $path; 
                
                if (str_starts_with($mimeType, 'image/')) $mediaType = 'image';
                elseif (str_starts_with($mimeType, 'audio/')) $mediaType = 'audio';
                elseif (str_starts_with($mimeType, 'video/')) $mediaType = 'video';
                else $mediaType = 'document';

                $sendFileUrl = "https://wasenderapi.com/api/send-file"; 

                $response = $clientGuzzle->post($sendFileUrl, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $instance->api_key,
                    ],
                    'multipart' => [
                        ['name' => 'session', 'contents' => $instance->name],
                        ['name' => 'to', 'contents' => $apiRecipient],
                        ['name' => 'caption', 'contents' => $messageContent], 
                        [
                            'name'     => 'file',
                            'contents' => fopen(storage_path('app/public/' . $path), 'r'),
                            'filename' => $file->getClientOriginalName()
                        ]
                    ]
                ]);

            // CASO 2: SE ENVÍA SOLO TEXTO
            } else {
                $sendMessageUrl = "https://wasenderapi.com/api/send-message";
                $response = $clientGuzzle->post($sendMessageUrl, [
                    'headers' => $this->getDynamicHeaders($instance->api_key), 
                    'json' => [
                        'session' => $instance->name,
                        'to'      => $apiRecipient,
                        'text'    => $messageContent
                    ],
                ]);
            }

            if ($response->getStatusCode() >= 400) {
                $errorBody = $response->getBody()->getContents();
                Log::error('API ERROR al responder:', ['error' => $errorBody]);
                return back()->withErrors(['message' => 'Error de la API: ' . $errorBody]);
            }

        } catch (ClientException | ServerException | \Exception $e) {
            $errorDetails = method_exists($e, 'hasResponse') ? $e->getResponse()->getBody()->getContents() : 'Error: ' . $e->getMessage();
            Log::error('Guzzle/API Error al responder:', ['error' => $errorDetails]);
            return back()->withErrors(['message' => $errorDetails]);
        }

        //  Guardar mensaje localmente
        try {
            ClientMessage::create([
                'client_id' => $clientModel->id,
                'whatsapp_instance_id' => $instance->id,
                'from_number' => $instance->phone, 
                'to_number' => $numero, 
                'message' => $messageContent, 
                'media_url' => $mediaUrl,
                'media_type' => $mediaType, 
                'direction' => 'outbound',
                'is_read' => true,
                'received_at' => now(),
                'user_id' => $senderUserId,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error FATAL al guardar respuesta localmente:', [$e->getMessage()]);
            return back()->withErrors(['message' => 'Error de BD al guardar la respuesta.']);
        }

        return redirect()->route('responses', ['phone' => $numero])->with(['success' => 'Mensaje enviado.']);
    }

    // ----------------------------------------------------------------------
    // FILTRADO DE RESPUESTAS
    // ----------------------------------------------------------------------

    public function showResponses(Request $request)
    {
        // Nota: Esta función asume que la ruta 'responses' está
        // protegida por 'middleware('auth')' en tu archivo routes/web.php
        
        $user = Auth::user();
        
        // --- ¡¡CORRECCIÓN DE SUPER ADMIN (3/3)!! ---
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');
        
        $numeroSeleccionado = $request->get('phone');
        $numeroLimpio = $numeroSeleccionado ? preg_replace('/[^0-9]/', '', $numeroSeleccionado) : null;

        $instance = $user->whatsappInstances()->first();

        $conversacion = collect();
        if ($numeroLimpio) {
            $clientModel = ClientModel::firstOrCreate(
                ['phone' => $numeroLimpio],
                ['name' => 'Cliente Chat', 'dui' => '000000000', 'date' => now()->toDateString()]
            );
            $clientModel->users()->syncWithoutDetaching([$user->id]);

            $conversacion = ClientMessage::where('client_id', $clientModel->id)
                ->orderBy('received_at', 'asc')
                ->get();
                
            $lastMessage = $conversacion->last();
            if ($lastMessage && $lastMessage->whatsapp_instance_id) {
                
                $chatInstance = WhatsappInstance::find($lastMessage->whatsapp_instance_id);
                
                if ($chatInstance && ($isAdmin || $user->whatsappInstances->contains($chatInstance))) {
                    $instance = $chatInstance; 
                }
            }
            
            if ($clientModel) {
                ClientMessage::where('client_id', $clientModel->id)
                    ->where('direction', 'inbound')
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }
        }

        // Cargar todos los mensajes (para la lista de la izquierda)
        $allUserMessages = ClientMessage::query()
            ->when(!$isAdmin, function ($query) use ($user) {
                // Si NO es Admin, solo muestra los mensajes
                // asignados a él O los que no tienen dueño
                $query->where('user_id', $user->id)
                        ->orWhereNull('user_id');
            })
            // Si ES Admin, el 'when' es falso y no se aplica
            // ningún filtro, por lo que trae TODO.
            ->orderBy('received_at', 'desc')
            ->get();

        $chatsConRespuestas = $allUserMessages->groupBy(function ($item) {
            $numero = $item->direction === 'inbound' ? $item->from_number : $item->to_number;
            $rawPhone = preg_replace('/[^0-9]/', '', $numero);
            if (str_starts_with($rawPhone, '503') && strlen($rawPhone) > 8) {
                return substr($rawPhone, 3); 
            }
            return $rawPhone; 
        });

        $phoneNumbers = $chatsConRespuestas->keys();

        $clientAvatars = ClientModel::whereIn('phone', $phoneNumbers) 
            ->whereNotNull('avatar_url') 
            ->pluck('avatar_url', 'phone'); 

        $chatsConRespuestas->each(function ($messages, $phoneNumber) use ($clientAvatars) {
            
            $avatarUrl = $clientAvatars->get($phoneNumber); 

            if ($avatarUrl) {
                $messages->each(function ($message) use ($avatarUrl) {
                    $message->contact_avatar_url = $avatarUrl;
                });
            }
        });
        

        return view('responses', compact('chatsConRespuestas', 'conversacion', 'numeroSeleccionado', 'instance'));
    }
    
    private function getDynamicHeaders(string $apiKey): array
    {
        return [
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
    
    
    public function testInstance($id)
    {
        $instance = WhatsappInstance::findOrFail($id);
        $client = new \GuzzleHttp\Client(['verify' => false]);
        $sessionName = $instance->name;
        $url = "https://wasenderapi.com/api/sessions/status/{$sessionName}";

        try {
            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $instance->api_key,
                    'Accept' => 'application/json',
                ]
            ]);
            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['status']) && $body['status'] == 'connected') {
                return response()->json(['status' => 'Activa']);
            } else {
                return response()->json(['status' => 'Desconectada']);
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            Log::error('Error en testInstance (4xx)', ['error' => $errorBody]);
            return response()->json(['status' => 'Error']);
        } catch (\Exception $e) {
            Log::error('Error en testInstance (general)', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'Error']);
        }
    }
}