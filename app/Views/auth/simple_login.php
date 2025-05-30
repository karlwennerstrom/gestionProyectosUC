<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Login - Sistema Multi-Área UC' ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #374151;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            font-size: 1.5rem;
            color: #1e40af;
            margin-bottom: 0.5rem;
        }

        .logo p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn {
            width: 100%;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .alert {
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .help-text {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .test-credentials {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
            border: 1px solid #e2e8f0;
        }

        .test-credentials h4 {
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .test-credentials p {
            color: #6b7280;
            font-size: 0.75rem;
            margin: 0.25rem 0;
        }

        .back-link {
            text-align: center;
            margin-top: 1rem;
        }

        .back-link a {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.875rem;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🏛️ Sistema Multi-Área UC</h1>
            <p>Acceso Temporal al Sistema</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form action="/auth/process-login" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       placeholder="usuario@uc.cl"
                       value="<?= old('email') ?>">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Ingresa tu contraseña">
            </div>

            <button type="submit" class="btn">
                🔐 Iniciar Sesión
            </button>
        </form>

        <div class="test-credentials">
            <h4>🧪 Credenciales de Prueba:</h4>
            <p><strong>Super Admin:</strong> admin@uc.cl / admin123</p>
            <p><strong>Admin:</strong> carlos.mendoza@uc.cl / admin123</p>
            <p><strong>Usuario:</strong> juan.perez@uc.cl / admin123</p>
        </div>

        <div class="help-text">
            <p>Este es un login temporal para pruebas.</p>
            <p>En producción se usará autenticación CAS de la UC.</p>
        </div>

        <div class="back-link">
            <a href="/">← Volver al inicio</a>
        </div>
    </div>

    <script>
        // Auto-focus en el campo email
        document.getElementById('email').focus();

        // Limpiar alerts después de 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>