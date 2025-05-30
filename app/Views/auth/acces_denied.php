<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Acceso Denegado' ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #374151;
        }

        .container {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }

        .error-icon {
            font-size: 4rem;
            color: #dc2626;
            margin-bottom: 1.5rem;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .error-message {
            color: #6b7280;
            font-size: 1.125rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .user-info {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .contact-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
            font-size: 0.875rem;
            color: #6b7280;
        }

        @media (max-width: 480px) {
            .container {
                padding: 2rem;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">🚫</div>
        
        <h1 class="error-title">Acceso Denegado</h1>
        
        <p class="error-message">
            <?= esc($message ?? 'No tienes los permisos necesarios para acceder a esta sección del sistema.') ?>
        </p>

        <?php if (isset($user_type)): ?>
        <div class="user-info">
            <strong>Tu tipo de usuario:</strong> <?= esc($user_type) ?><br>
            <small>Contacta al administrador si crees que esto es un error.</small>
        </div>
        <?php endif; ?>

        <div class="actions">
            <a href="javascript:history.back()" class="btn btn-secondary">
                ← Volver Atrás
            </a>
            <a href="/dashboard" class="btn btn-primary">
                🏠 Ir al Dashboard
            </a>
        </div>

        <div class="contact-info">
            <p><strong>¿Necesitas ayuda?</strong></p>
            <p>Contacta al soporte técnico: soporte-multiarea@uc.cl</p>
        </div>
    </div>

    <script>
        // Auto-redireccionar después de 30 segundos si no hay interacción
        let redirectTimer = setTimeout(() => {
            window.location.href = '/dashboard';
        }, 30000);

        // Cancelar auto-redirect si el usuario interactúa
        document.addEventListener('click', () => {
            clearTimeout(redirectTimer);
        });

        document.addEventListener('keypress', () => {
            clearTimeout(redirectTimer);
        });
    </script>
</body>
</html>