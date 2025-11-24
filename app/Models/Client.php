<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\MoratoriumClassification;
use App\Models\MessageTemplate;

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

    // Relación con usuarios encargados
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_client');
    }

    // Relación con clasificación de mora
    public function classification()
    {
        return $this->belongsTo(MoratoriumClassification::class, 'moratorium_classification_id');
    }

    // Relación indirecta con plantilla de mensaje
    public function template()
    {
        return $this->hasOneThrough(
            MessageTemplate::class,
            MoratoriumClassification::class,
            'id', // Foreign key on moratorium_classifications
            'moratorium_classification_id', // Foreign key on message_templates
            'moratorium_classification_id', // Local key on clients
            'id' // Local key on moratorium_classifications
        );
    }

    // ✅ Genera mensaje personalizado usando relaciones Eloquent
    public static function getMessage($clientId)
    {
        $client = self::with(['template'])->find($clientId);

        if (!$client || !$client->template) {
            return 'Cliente no encontrado o sin plantilla asociada.';
        }

        $message = str_replace(
            [':nombre_cliente', '{nombre}'],
            $client->name,
            $client->template->template
        );

        return $message;
    }

    // ✅ Envío masivo con manejo de errores
    public static function sendMassive($clientMessage, $sendMessageUrl, $apiToken)
    {
        $clients = self::with(['template'])->get();

        foreach ($clients as $client) {
            if (!$client->template) {
                \Log::warning('Cliente sin plantilla, se omite', ['client_id' => $client->id]);
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
                \Log::error('Error al enviar mensaje masivo', [
                    'client_id' => $client->id,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
    }
}
