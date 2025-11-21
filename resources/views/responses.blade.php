@extends('layouts.chat')

@section('title', 'Panel de Respuestas | ' . ($instance->name ?? 'Selecciona Área'))

{{-- ======================================================================= --}}
{{-- 1. SECCIÓN DE ESTILOS (CSS)                                           --}}
{{-- ======================================================================= --}}
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
    max-width: 100%;
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

/* --- MEJORA: Avatar real (imagen) --- */
.header-avatar {
    width: 40px; height: 40px; border-radius: 50%; background-color: #00a884;
    display: flex; align-items: center; justify-content: center; color: #fff;
    font-weight: bold; font-size: 14px; flex-shrink: 0;
    overflow: hidden; /* Para que la imagen no se salga */
}
.header-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Asegura que la imagen cubra el círculo */
}
/* --- FIN MEJORA --- */


/* --- Estilos para lista de chat (No Leídos) --- */
.contact-info {
    flex-grow: 1;
    min-width: 0;
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
    background-color: #25d366;
    color: white;
    font-size: 11px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
    flex-shrink: 0;
}


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
    justify-content: space-between;
}
.chat-header {
    background-color: #ffffffff;
    justify-content: flex-start;
    gap: 12px;
}

/* ================================================= */
/* 4. CHAT PRINCIPAL */
/* ================================================= */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background-color: #E0F2E9;
    background-size: cover;
}

.chat-thread {
    flex: 1;
    padding: 20px 8%;
    overflow-y: auto;
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
    position: relative; /* Para el preview del archivo Y EL EMOJI PICKER */
}
.chat-input textarea {
    flex: 1; resize: none; padding: 10px 14px; border: none; border-radius: 20px; background-color: #fff; font-size: 14px; min-height: 40px; max-height: 120px; overflow-y: auto;
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
    display: none;
}

.chat-input .send-button,
.chat-input .mic-button {
    background-color: #00a884; /* Verde WhatsApp */
    color: white;
    border: none;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

/* --- MEJORA: Estilo para el botón de Micrófono grabando --- */
.mic-button.is-recording {
    background-color: #e60023; /* Rojo */
    animation: pulse 1s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(230, 0, 35, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(230, 0, 35, 0); }
    100% { box-shadow: 0 0 0 0 rgba(230, 0, 35, 0); }
}

/* --- MEJORA: Estilos para el Emoji Picker --- */
emoji-picker {
    position: absolute;
    bottom: 60px; /* Justo encima del input */
    left: 10px;
    z-index: 10;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
    border: 1px solid #ddd;
    display: none; /* Oculto por defecto */
}
emoji-picker.visible {
    display: block;
}
/* ================================================= */
/* 🌙 DARK MODE ACTIVADO CON: html.dark-mode */
/* ================================================= */
html.dark-mode .whatsapp-app-container {
    background-color: #111b21 !important;
}

html.dark-mode .chat-sidebar {
    background-color: #202c33 !important;
    border-right: 1px solid #111 !important;
}

html.dark-mode .chat-contact {
    background-color: #202c33;
    border-bottom: 1px solid #2b3942;
    color: #e9edef;
}
html.dark-mode .chat-contact:hover {
    background-color: #2a3942;
}
html.dark-mode .chat-contact.active {
    background-color: #2b3942;
}

/* Texto en lista */
html.dark-mode .contact-time {
    color: #8696a0;
}
html.dark-mode .last-message {
    color: #d1d7db;
}

/* Avatar borde */
html.dark-mode .header-avatar {
    background-color: #005c4b;
}

/* Sidebar Header */
html.dark-mode .chat-sidebar-header {
    background-color: #202c33;
    border-bottom: 1px solid #2a3942;
    color: #e9edef;
}

html.dark-mode .chat-header {
    background-color: #202c33 !important;
    border-bottom: 1px solid #2a3942;
    color: #e9edef;
}

/* ================================================= */
/* CHAT PRINCIPAL */
/* ================================================= */

html.dark-mode .chat-main {
    background-color: #0b141a !important;
}

/* Contenedor mensajes */
html.dark-mode .chat-thread {
    scrollbar-color: #555 #111;
}

/* ================================================= */
/* BURBUJAS */
/* ================================================= */
html.dark-mode .chat-bubble.inbound {
    background-color: #202c33 !important;
    color: #e9edef !important;
}

html.dark-mode .chat-bubble.outbound {
    background-color: #005c4b !important;
    color: #e9edef !important;
}

html.dark-mode .chat-meta {
    color: #8696a0 !important;
}
html.dark-mode .read-status {
    color: #53bdeb !important;
}

/* ================================================= */
/* INPUT DE ESCRITURA */
/* ================================================= */
html.dark-mode .chat-input {
    background-color: #202c33 !important;
    border-top: 1px solid #2a3942 !important;
}

html.dark-mode .chat-input textarea {
    background-color: #2a3942 !important;
    color: #e9edef !important;
}

html.dark-mode .chat-input textarea::placeholder {
    color: #8696a0 !important;
}

html.dark-mode .icon-button {
    color: #c3c9cf !important;
}

html.dark-mode #file-name-preview {
    background: #2a3942 !important;
    color: #e9edef !important;
}

/* Botón verde WhatsApp */
html.dark-mode .send-button,
html.dark-mode .mic-button {
    background-color: #00a884 !important;
    color: white !important;
}

/* ================================================= */
/* EMOJI PICKER */
/* ================================================= */

html.dark-mode emoji-picker {
    background-color: #1f2c33 !important;
    border-color: #2b3b44 !important;
    color: #e9edef !important;
}

html.dark-mode emoji-picker::part(category-button) {
    filter: invert(1) brightness(1.4);
}

html.dark-mode emoji-picker::part(emoji) {
    filter: invert(1) brightness(1.4);
}

</style>
@endpush

{{-- ======================================================================= --}}
{{-- 2. SECCIÓN DE CONTENIDO (HTML)                                         --}}
{{-- ======================================================================= --}}
@section('content')
@php
    $numeroSeleccionado = preg_replace('/[^0-9]/', '', $numeroSeleccionado ?? '');
    $userName = Auth::check() ? Auth::user()->name : 'Agente';
    // MEJORA: Obtenemos el avatar del usuario (agente)
    // Asume que tu User model tiene 'avatar_url' (si no, cámbialo o déjalo)
    $userAvatar = Auth::check() ? Auth::user()->avatar_url : null;
@endphp

<div class="whatsapp-app-container">
    {{-- Alertas (Sin cambios) --}}
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
        {{-- 1. SIDEBAR (LISTA DE CONTACTOS)                 --}}
        {{-- ================================================= --}}
        <div class="chat-sidebar">
            <div class="chat-sidebar-header">
                <div style="display: flex; align-items: center; gap: 10px;">

                    <div class="header-avatar">
                        @if($userAvatar)
                            <img src="{{ $userAvatar }}" alt="Avatar">
                        @else
                            {{-- Fallback al ícono si no hay foto --}}
                            <i class="fas fa-user-circle" style="font-size: 40px; color: #ccc;"></i>
                        @endif
                    </div>
                    <h5>{{ $instance->name ?? $userName }}</h5>
                </div>
                <div>
                    <button class="icon-button" title="Menú"><i class="fas fa-ellipsis-v"></i></button>
                </div>
            </div>

            @forelse($chatsConRespuestas ?? [] as $numeroCliente => $conversacionItem)
                @php
                    $ultimo = $conversacionItem->first();
                    $numeroClienteLimpio = preg_replace('/[^0-9]/', '', $numeroCliente);

                    $unreadCount = $conversacionItem
                        ->where('is_read', false)
                        ->where('direction', 'inbound')
                        ->count();

                    // MEJORA: Esta es la variable que tu CONTROLADOR debe enviar
                    // gracias a la corrección que hicimos en `showResponses`
                    $contactAvatarUrl = $ultimo->contact_avatar_url ?? null;
                @endphp

                <div class="chat-contact {{ $numeroSeleccionado === $numeroClienteLimpio ? 'active' : '' }}">
                    <a href="{{ url('/responses?phone=' . $numeroClienteLimpio) }}">

                        <div class="header-avatar">
                            @if($contactAvatarUrl)
                                <img src="{{ $contactAvatarUrl }}" alt="Avatar de {{ $numeroClienteLimpio }}">
                            @else
                                {{-- Fallback a los números (como lo tenías) --}}
                                {{ substr($numeroClienteLimpio, -2) }}
                            @endif
                        </div>
                        <div class="contact-info">
                            <div class="contact-row">
                                <strong>{{ $numeroClienteLimpio }}</strong>
                                <small class="contact-time">
                                    {{ $ultimo->received_at ? \Carbon\Carbon::parse($ultimo->received_at)->format('H:i') : '' }}
                                </small>
                            </div>
                            <div class="contact-row">
                                <small class="last-message">
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

                                @if($unreadCount > 0)
                                    <span class="unread-badge">{{ $unreadCount }}</span>
                                @endif
                            </div>
                        </div>

                    </a>
                </div>
            @empty
                <p class="p-3 text-center text-muted">No hay conversaciones activas.</p>
            @endforelse
        </div>

        {{-- ================================================= --}}
        {{-- 2. PANEL DE CONVERSACIÓN PRINCIPAL                --}}
        {{-- ================================================= --}}
        <div class="chat-main">
            <div class="chat-header">
                @if ($numeroSeleccionado)
                    @php
                        // MEJORA: Busca la foto del chat seleccionado
                        $headerAvatarUrl = null;
                        if (isset($chatsConRespuestas[$numeroSeleccionado])) {
                            $headerAvatarUrl = $chatsConRespuestas[$numeroSeleccionado]->first()->contact_avatar_url ?? null;
                        }
                    @endphp

                    <div class="header-avatar" style="background-color: #54656f;">
                         @if($headerAvatarUrl)
                            <img src="{{ $headerAvatarUrl }}" alt="Avatar de {{ $numeroSeleccionado }}">
                        @else
                            {{ substr($numeroSeleccionado, -2) }}
                        @endif
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

            {{-- FORMULARIO DE RESPUESTA (CON TODOS LOS IDs) --}}
            @if($numeroSeleccionado && $instance)
                <form action="{{ route('responses.reply') }}" method="POST" class="chat-input" id="reply-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="phone" value="{{ $numeroSeleccionado }}">
                    <input type="hidden" name="whatsapp_instance_id" value="{{ $instance->id }}">

                    <emoji-picker id="emoji-picker"></emoji-picker>

                    <button type="button" class="icon-button" id="emoji-button" title="Emojis"><i class="far fa-smile"></i></button>

                    <label for="media_file" class="icon-button" title="Adjuntar archivo">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" name="media_file" id="media_file"
                            accept="image/*,audio/*,video/*,.pdf"
                            style="display: none;"
                            onchange="showFileName(this)">
                    </label>
                    <span id="file-name-preview"></span>

                    <textarea name="message" id="message-input" placeholder="Escribe un mensaje..."></textarea>

                    <button type="submit" class="icon-button send-button" id="send-button" title="Enviar mensaje" style="display: none;">
                        <i class="fas fa-paper-plane"></i>
                    </button>

                    <button type="button" class="icon-button mic-button" id="mic-button" title="Grabar audio">
                        <i class="fas fa-microphone"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection {{-- <-- FIN DE @section('content') --}}


{{-- ======================================================================= --}}
{{-- 3. SECCIÓN DE SCRIPTS (JAVASCRIPT)                                    --}}
{{-- ======================================================================= --}}
@push('scripts')
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<script>
    // Variables globales para la grabación de audio
    let mediaRecorder;
    let recordedChunks = [];
    let isRecording = false;

    // TODO EL CÓDIGO AHORA VA DENTRO DE ESTE BLOQUE
    document.addEventListener("DOMContentLoaded", function() {

        // 1. Scroll automático al último mensaje
        const thread = document.getElementById('chat-thread');
        if (thread) {
            thread.scrollTo({ top: thread.scrollHeight, behavior: 'auto' });
        }

        // --- Declaración de todos los elementos ---
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        const micButton = document.getElementById('mic-button');
        const replyForm = document.getElementById('reply-form');
        const emojiPicker = document.getElementById('emoji-picker');
        const emojiButton = document.getElementById('emoji-button');

        // 2. Auto-resize del textarea
        if (messageInput) {
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });
        }

        // --- LÓGICA DEL BOTÓN MIC/ENVIAR ---
        if (messageInput && sendButton && micButton) {
            messageInput.addEventListener('input', function() {
                const text = this.value.trim();
                if (text.length > 0) {
                    sendButton.style.display = 'flex';
                    micButton.style.display = 'none';
                } else {
                    sendButton.style.display = 'none';
                    micButton.style.display = 'flex';
                }
            });
        } else {
            if (document.getElementById('reply-form')) {
                 console.log("Formulario de respuesta cargado, pero no se encontraron botones (normal si no hay chat seleccionado).");
            }
        }

        // --- LÓGICA DEL PICKER DE EMOJIS ---
        if (emojiButton && emojiPicker && messageInput) {
            emojiButton.addEventListener('click', (e) => {
                e.stopPropagation();
                emojiPicker.classList.toggle('visible');
            });

            emojiPicker.addEventListener('emoji-click', event => {
                messageInput.value += event.detail.emoji.unicode;
                // Dispara el evento 'input' manualmente para que cambie el botón
                messageInput.dispatchEvent(new Event('input', { bubbles: true }));
            });

            // Ocultar si se hace click fuera
            document.body.addEventListener('click', (e) => {
                if (emojiPicker.classList.contains('visible') && e.target.id !== 'emoji-button' && !emojiPicker.contains(e.target)) {
                    emojiPicker.classList.remove('visible');
                }
            });
        }

        // --- LÓGICA PARA GRABAR AUDIO ---
        if (micButton && replyForm && messageInput) {

            const startRecording = async () => {
                try {
                    // 1. Pedir permiso
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

                    // 2. Iniciar MediaRecorder
                    mediaRecorder = new MediaRecorder(stream);
                    recordedChunks = [];

                    mediaRecorder.addEventListener('dataavailable', event => {
                        if (event.data.size > 0) recordedChunks.push(event.data);
                    });

                    mediaRecorder.addEventListener('stop', () => {
                        // 4. Convertir a archivo
                        const audioBlob = new Blob(recordedChunks, { type: 'audio/ogg; codecs=opus' });
                        const audioFile = new File([audioBlob], `audio_grabado_${Date.now()}.ogg`, {
                            type: 'audio/ogg; codecs=opus',
                        });

                        // 5. Adjuntar al input <input type="file">
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(audioFile);

                        const fileInput = document.getElementById('media_file');
                        fileInput.files = dataTransfer.files;

                        // 6. Enviar formulario
                        replyForm.submit();
                    });

                    // 3. Empezar
                    mediaRecorder.start();
                    isRecording = true;
                    micButton.classList.add('is-recording'); // Poner rojo
                } catch (err) {
                    console.error("Error al grabar audio: ", err);
                    alert('No se pudo iniciar la grabación. ¿Diste permiso para usar el micrófono? (Revisa el candado 🔒 en la URL)');
                }
            };

            const stopRecording = () => {
                if (mediaRecorder && isRecording) {
                    mediaRecorder.stop();
                    isRecording = false;
                    micButton.classList.remove('is-recording');
                }
            };

            // Simular "mantener presionado"
            micButton.addEventListener('mousedown', startRecording);
            micButton.addEventListener('mouseup', stopRecording);
            micButton.addEventListener('mouseleave', () => { // Si saca el mouse
                if (isRecording) stopRecording();
            });
        }
    }); // <-- FIN DEL DOMContentLoaded

    // Esta función va afuera porque es llamada por el HTML (onchange)
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
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const html = document.documentElement;
        const icon = document.getElementById("themeIcon");

        // Aplicar estado guardado
        if (localStorage.theme === "dark") {
            html.classList.add("dark-mode");
            icon.classList.remove("fa-moon");
            icon.classList.add("fa-sun");
        }

        document.getElementById("themeToggle").addEventListener("click", function(e) {
            e.preventDefault();

            const isDark = html.classList.toggle("dark-mode");

            if (isDark) {
                localStorage.theme = "dark";
                icon.classList.remove("fa-moon");
                icon.classList.add("fa-sun");
            } else {
                localStorage.theme = "light";
                icon.classList.remove("fa-sun");
                icon.classList.add("fa-moon");
            }
        });
    });
</script>


@endpush
