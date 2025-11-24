<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\MoratoriumClassification;
use App\Models\MessageTemplate;
use Illuminate\Support\Facades\Log; // ✅ Corrección: importar Log correctamente

class Client extends Model
{
    protected $fillable = [
        'phone',
        'whatsapp_jid',
        'name',
        'dui',
        'date',
        'moratorium_classification_id'
    ];

    /**
     * Relación muchos a muchos con usuarios encargados.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_client');
    }

    /**
     * Relación con clasificación de mora.
     */
    public function classification()
    {
        return $this->belongsTo(MoratoriumClassification::class, 'moratorium_classification_id');
    }

    /**
     * Relación con plantilla de mensaje (vía clasificación).
     */
    public function template()
    {
        return $this->hasOneThrough(
            MessageTemplate::class,
            MoratoriumClassification::class,
            'id',                             // FK en classification
            'moratorium_classification_id',   // FK en template
            'moratorium_classification_id',   // FK local en client
            'id'                              // PK en classification
        );
    }

    /**
     * Genera mensaje personalizado basado en plantilla.
     */
    public static function getMessage($clientId)
    {
        $client = self::with(['template'])->find($clientId);

        if (!$client || !$client->template) {
            return 'Cliente no encontrado o sin plantilla asociada.';
        }

        return str_replace(
            [':nombre_cliente', '{nombre}'],
            $client->name,
            $client->template->template
        );
    }

    /**
     * Envío masivo con manejo de errores.
     */
    public static function sendMassive($clientMessage, $sendMessageUrl, $apiToken)
    {
        $clients = self::with(['template'])->get();

        foreach ($clients as $client) {
            if (!$client->template) {
                Log::warning('Cliente sin plantilla, se omite', ['client_id' => $client->id]);
                continue;
            }

            $phone = preg_replace('/[^0-9]/', '', $client->phone);
            $message = self::getMessage($client->id);

            try {
                $clientMessage->request('POST', $sendMessageUrl, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiToken,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'chatId' => "503{$phone}@c.us",
                        'message' => $message,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::error('Error al enviar mensaje masivo', [
                    'client_id' => $client->id,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
    }
}
