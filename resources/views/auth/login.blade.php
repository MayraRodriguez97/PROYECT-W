<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login Page</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">

    <link href="{{ asset('css/custom-login.css') }}" rel="stylesheet">
</head>

<body>

<div class="login-wrapper">
    <div class="login-container">
        <div class="login-left">
            <div class="app-logo">

            </div>
            <div class="illustration-area">
            </div>
            <div class="quote-area">
                <p>Plataforma de envío masivo de mensajes por WhatsApp, con recepción y gestión de respuestas de tus clientes en tiempo real.</p>
                <span class="author">Mayra Rodriguez</span>
            </div>
        </div>

        <div class="login-right">
            <div class="login-form-content">
                <h2 class="welcome-title">Bienvenido</h2>
                <p class="welcome-subtitle">Introduce tus credenciales para acceder a tu cuenta.</p>

                @if (session('status'))
                    <div class="alert alert-success mt-4" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mt-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error de credenciales: Por favor, verifica tu correo y contraseña.
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <div class="input-group custom-input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   class="form-control custom-form-control @error('email') is-invalid @enderror"
                                   placeholder="Introduce tu correo electrónico"
                                   value="{{ old('email') }}"
                                   required autofocus>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label mb-0">Contraseña</label>
                        </div>
                        <div class="input-group custom-input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control custom-form-control @error('password') is-invalid @enderror"
                                   placeholder="Introduce tu contraseña"
                                   required>
                            <button type="button" class="btn btn-toggle-password" id="togglePassword">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary custom-btn-primary w-100 mb-3">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function (e) {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
    });
</script>

</body>
</html>
