<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="height: 100%;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- ✅ CRÍTICO: Añadir el token CSRF para formularios y seguridad --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    {{-- Scripts y Estilos Base --}}
{{--    @vite(['resources/css/app.css', 'resources/js/app.js'])--}}

    {{-- Aquí se inyectarán los estilos específicos de la vista de chat --}}
    @stack('styles')

</head> {{-- <-- SOLO UNA ETIQUETA DE CIERRE --}}
<body class="font-sans antialiased" style="height: 100%; margin: 0; padding: 0; overflow: hidden;">

    {{-- Contenedor principal de la app --}}
    <div class="min-h-full flex flex-col" style="height: 100%;">

        @include('layouts.navigation')

        {{-- El <main> es flexible y ocupa el espacio restante --}}
        <main class="flex-grow p-0" style="overflow: hidden;">
            @yield('content')
        </main>
    </div>

    {{-- Aquí se inyectarán los scripts específicos de la vista de chat --}}
    @stack('scripts')

</body>
</html>
