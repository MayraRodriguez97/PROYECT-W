<?php

namespace App\Models;
use App\Models\Scopes\InstanceScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientMessage extends Model
{
    use HasFactory;
    protected static function booted(): void
    {
        static::addGlobalScope(new InstanceScope);
    }

    protected $fillable = [
        'from_number',
        'to_number',
        'message',
        'direction',
        'received_at',
        'phone',
        'whatsapp_instance_id',
        'client_id',
        'user_id',
    ];

    // Relación con el cliente
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // Relación con el encargado
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function whatsappInstance()
    {
        return $this->belongsTo(WhatsappInstance::class, 'whatsapp_instance_id');
    }
    // Accessor para obtener el número limpio
    public function getNumeroLimpioAttribute()
    {
        $numero = $this->direction === 'inbound' ? $this->from_number : $this->to_number;
        return preg_replace('/[^0-9]/', '', $numero);
    }

    // Scope para filtrar por número limpio
    public function scopeConNumeroLimpio($query, $numero)
    {
        $numeroLimpio = preg_replace('/[^0-9]/', '', $numero);
        return $query->where(function ($q) use ($numeroLimpio) {
            $q->where('from_number', 'LIKE', "%{$numeroLimpio}%")
              ->orWhere('to_number', 'LIKE', "%{$numeroLimpio}%");
        });
    }
}
