@extends('layouts.chat')

@section('title', 'Panel de Respuestas | ' . ($instance->name ?? 'Selecciona Área'))

@push('styles')
{{-- Incluye Font Awesome para todos los íconos --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
/* ================================================= */
/* 1. LAYOUT GENERAL */
/* ================================================= */
.whatsapp-app-container {
    display: flex;
    height: 100%; /* Ocupa el 100% del <main> */
    width: 100%;
    margin: 0 auto;
    max-width: 1600px;
    background-color: #f0f2f5;
}

.chat-layout {
    display: flex;
    height: 100%;
    width: 100%;
    overflow: hidden;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}

/* ================================================= */
/* 2. SIDEBAR Y CONTACTOS */
/* ================================================= */
.chat-sidebar {
    width: 350px;
    min-width: 300px;
    background-color: #fff;
    border-right: 1px solid #ddd;
    overflow-y: auto; /* SCROLL para la lista de contactos */
    flex-shrink: 0;
}
.chat-contact {
    padding: 12px 16px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background-color 0.2s;
}
.chat-contact:hover { background-color: #f5f5f5; }
.chat-contact.active { background-color: #ebebeb; }
.chat-contact a {
    text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px;
}
.header-avatar {
    width: 40px; height: 40px; border-radius: 50%; background-color: #00a884;
    display: flex; align-items: center; justify-content: center; color: #fff;
    font-weight: bold; font-size: 14px; flex-shrink: 0;
}

/* --- NUEVO: Estilos para lista de chat (No Leídos) --- */
.contact-info {
    flex-grow: 1;
    min-width: 0; /* Soluciona problemas de overflow */
}
.contact-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.contact-time {
    font-size: 11px;
    color: #888;
    flex-shrink: 0;
    margin-left: 8px;
}
.contact-row .last-message {
    font-size: 13px;
    color: #555;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex-grow: 1;
}
.unread-badge {
    background-color: #25d366; /* Verde WhatsApp */
    color: white;
    font-size: 11px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
    flex-shrink: 0;
}
/* --- FIN NUEVO --- */


/* ================================================= */
/* 3. CABECERAS */
/* ================================================= */
.chat-sidebar-header, .chat-header {
    min-height: 59px;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #ededed;
    background-color: #f0f2f5;
    font-size: 16px;
}
.chat-sidebar-header {
    justify-content: space-between; /* Para alinear el avatar y los botones */
}
.chat-header {
    background-color: #ededed;
    justify-content: flex-start;
    gap: 12px;
}

/* ================================================= */
/* 4. CHAT PRINCIPAL (CORRECCIÓN DE SCROLL) */
/* ================================================= */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column; /* CRÍTICO para scroll */
    background: url('https://i.imgur.com/gK37Q9h.png') repeat;
    background-size: cover;
}

.chat-thread {
    flex: 1; /* CRÍTICO: Ocupa todo el espacio restante para que el scroll funcione */
    padding: 20px 8%;
    overflow-y: auto; /* HABILITA EL SCROLL aquí */
    display: flex;
    flex-direction: column;
}

/* ================================================= */
/* 5. BURBUJAS Y FORMULARIO */
/* ================================================= */
.chat-bubble {
    max-width: 65%; margin-bottom: 9px; padding: 8px 9px; border-radius: 8px;
    box-shadow: 0 1px 0.5px rgba(0,0,0,0.13); font-size: 14px; line-height: 1.3;
    word-wrap: break-word;
}
.chat-bubble.inbound {
    background-color: #fff; align-self: flex-start; border-radius: 0 7.5px 7.5px 7.5px;
}
.chat-bubble.outbound {
    background-color: #dcf8c6; align-self: flex-end; border-radius: 7.5px 0 7.5px 7.5px;
}
.chat-bubble img { max-width: 100%; border-radius: 5px; }
.chat-bubble audio { width: 100%; max-width: 300px; height: 40px; margin: 5px 0; }
.chat-meta {
    font-size: 11px; color: #888; margin-top: 5px; text-align: right; display: flex; justify-content: flex-end; gap: 4px;
}
.read-status { color: #53bdeb; }

.chat-input {
    display: flex; align-items: center; padding: 8px 16px; border-top: 1px solid #e0e0e0; background-color: #f0f2f5; gap: 8px;
    position: relative; /* Para el preview del archivo */
}
.chat-input textarea {
    flex: 1; resize: none; padding: 10px 14px; border: none; border-radius: 20px; background-color: #fff; font-size: 14px; min-height: 40px; max-height: 120px; overflow-y: auto;
}
.chat-input button[type="submit"] {
    background-color: #00a884; color: white; border: none; border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;
}
.icon-button {
    background: none; border: none; color: #54656f; font-size: 24px; cursor: pointer; padding: 8px; border-radius: 50%;
}
#file-name-preview {
    position: absolute;
    top: -28px;
    left: 80px;
    background: #fff;
    padding: 4px 10px;
    border-radius: 10px;
    font-size: 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
    display: none; /* Se muestra con JS */
}
</style>
@endpush

@section('content')
@php
    $numeroSeleccionado = preg_replace('/[^0-9]/', '', $numeroSeleccionado ?? '');
    $userName = Auth::check() ? Auth::user()->name : 'Agente';
@endphp

<div class="whatsapp-app-container">
    {{-- Alertas --}}
    @if(session('warning') || session('success') || $errors->any())
        <div style="position: fixed; top: 0; width: 100%; z-index: 1000; text-align: center;">
            @if(session('warning'))
                <div class="alert alert-warning p-2 text-center mb-0">⚠️ {{ session('warning') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success p-2 text-center mb-0">✅ {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger p-2 text-center mb-0" style="max-height: 100px; overflow-y: auto;">
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span><br>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="chat-layout">

        {{-- ================================================= --}}
        {{-- 1. SIDEBAR (LISTA DE CONTACTOS)                   --}}
        {{-- ================================================= --}}
        <div class="chat-sidebar">
            <div class="chat-sidebar-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-user-circle fa-2x"></i>
                    <h5>{{ $instance->name ?? $userName }}</h5>
                </div>
                <div>
                    <button class="icon-button" title="Menú"><i class="fas fa-ellipsis-v"></i></button>
                </div>
            </div>

            @forelse($chatsConRespuestas ?? [] as $numeroCliente => $conversacionItem)
                @php
                    $ultimo = $conversacionItem->first(); // El último mensaje (porque está en 'desc')
                    $numeroClienteLimpio = preg_replace('/[^0-9]/', '', $numeroCliente);
                    
                    // Contamos los mensajes no leídos (is_read = false) que sean entrantes (inbound)
                    $unreadCount = $conversacionItem
                        ->where('is_read', false)
                        ->where('direction', 'inbound')
                        ->count();
                @endphp
                
                <div class="chat-contact {{ $numeroSeleccionado === $numeroClienteLimpio ? 'active' : '' }}">
                    <a href="{{ url('/responses?phone=' . $numeroClienteLimpio) }}">
                        <div class="header-avatar">{{ substr($numeroClienteLimpio, -2) }}</div>
                        
                        <div class="contact-info"> {{-- Flex-grow (ocupa el espacio) --}}
                            <div class="contact-row"> {{-- Fila de arriba --}}
                                <strong>{{ $numeroClienteLimpio }}</strong>
                                <small class="contact-time">
                                    {{ $ultimo->received_at ? \Carbon\Carbon::parse($ultimo->received_at)->format('H:i') : '' }}
                                </small>
                            </div>
                            <div class="contact-row"> {{-- Fila de abajo --}}
                                <small class="last-message"> {{-- Ocupa espacio --}}
                                    @if($ultimo->direction == 'outbound')
                                        <i class="fas fa-check-double" style="color: #53bdeb; font-size: 11px;"></i>
                                    @endif
                                    @if(isset($ultimo->media_type) && $ultimo->media_type === 'image')
                                        <i class="fas fa-image"></i> Imagen
                                    @elseif(isset($ultimo->media_type) && $ultimo->media_type === 'audio')
                                        <i class="fas fa-microphone"></i> Audio
                                    @else
                                        {{ \Illuminate\Support\Str::limit($ultimo->message ?? 'Sin mensajes', 30) }}
                                    @endif
                                </small>
                                
                                {{-- ¡AQUÍ ESTÁ EL BADGE! --}}
                                @if($unreadCount > 0)
                                    <span class="unread-badge">{{ $unreadCount }}</span>
                                @endif
                            </div>
                        </div>

                    </a>
                </div>
            @empty
                <p class="p-3 text-center text-muted">No hay conversaciones activas.</p>
            @endforelse {{-- <-- ESTA ES LA LÍNEA CORREGIDA --}}
        </div>

        {{-- ================================================= --}}
        {{-- 2. PANEL DE CONVERSACIÓN PRINCIPAL                --}}
        {{-- ================================================= --}}
        <div class="chat-main">
            <div class="chat-header">
                @if ($numeroSeleccionado)
                    <div class="header-avatar" style="background-color: #54656f;">
                        {{ substr($numeroSeleccionado, -2) }}
                    </div>
                    <div>
                        <strong>{{ $numeroSeleccionado }}</strong><br>
                        <small class="text-muted">En línea</small>
                    </div>
                @else
                    <h5 style="margin: 0; color: #555;">Selecciona un chat para empezar a responder</h5>
                @endif
            </div>

            {{-- HILO DE MENSAJES --}}
            <div class="chat-thread" id="chat-thread">
                @forelse($conversacion ?? [] as $mensaje)
                    @php
                        $isOutbound = $mensaje->direction === 'outbound';
                        $hasContent = !empty($mensaje->message) || !empty($mensaje->media_url);
                    @endphp

                    @if($hasContent)
                    <div class="chat-bubble {{ $isOutbound ? 'outbound' : 'inbound' }}">
                        @if($mensaje->media_type === 'image' && $mensaje->media_url)
                            <a href="{{ Storage::url($mensaje->media_url) }}" target="_blank">
                                <img src="{{ Storage::url($mensaje->media_url) }}" alt="Imagen Adjunta">
                            </a>
                        @elseif($mensaje->media_type === 'audio' && $mensaje->media_url)
                            <audio controls src="{{ Storage::url($mensaje->media_url) }}"></audio>
                        @endif

                        @if(!empty($mensaje->message))
                            {{-- Corrige para renderizar saltos de línea --}}
                            <div class="chat-text">{!! nl2br(e($mensaje->message)) !!}</div>
                        @endif

                        <div class="chat-meta">
                            <span>{{ $mensaje->received_at ? \Carbon\Carbon::parse($mensaje->received_at)->format('H:i') : '' }}</span>
                            @if($isOutbound)
                                <span class="read-status" title="Leído"><i class="fas fa-check-double"></i></span>
                            @endif
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="text-center mt-5 p-5">
                        <h4 class="text-muted">Inicia la conversación</h4>
                    </div>
                @endforelse
            </div>

            {{-- FORMULARIO DE RESPUESTA --}}
            @if($numeroSeleccionado && $instance)
                <form action="{{ route('responses.reply') }}" method="POST" class="chat-input" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="phone" value="{{ $numeroSeleccionado }}">
                    <input type="hidden" name="whatsapp_instance_id" value="{{ $instance->id }}">

                    <button type="button" class="icon-button" title="Emojis"><i class="far fa-smile"></i></button>

                    <label for="media_file" class="icon-button" title="Adjuntar archivo">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" name="media_file" id="media_file" 
                               accept="image/*,audio/*,video/*,.pdf" 
                               style="display: none;" 
                               onchange="showFileName(this)">
                    </label>
                    <span id="file-name-preview"></span>

                    {{-- CORREGIDO: 'required' quitado para permitir envío de solo archivos --}}
                    <textarea name="message" placeholder="Escribe un mensaje..."></textarea>

                    <button type="submit" title="Enviar mensaje">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

@endpush

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Scroll automático al último mensaje
        const thread = document.getElementById('chat-thread');
        if (thread) {
            thread.scrollTo({ top: thread.scrollHeight, behavior: 'auto' });
        }

        // 2. Auto-resize del textarea
        const textarea = document.querySelector('.chat-input textarea');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto'; // Resetea la altura
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });
        }
    });

    // 3. Función para mostrar el nombre del archivo seleccionado
    function showFileName(input) {
        const preview = document.getElementById('file-name-preview');
        if (input.files && input.files[0]) {
            preview.textContent = input.files[0].name;
            preview.style.display = 'inline-block';
        } else {
            preview.textContent = '';
            preview.style.display = 'none';
        }
    }
</script>
@endpush

@endsection