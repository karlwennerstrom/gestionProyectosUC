<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard Administrador' ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            color: #374151;
        }

        .navbar {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .navbar-brand {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .welcome-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .welcome-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: #6b7280;
            font-size: 1.125rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 500;
        }

        .actions-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .actions-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .action-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);
        }

        .action-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .action-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .action-description {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            margin-right: 0.5rem;
        }

        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                gap: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">
                🏛️ Sistema Multi-Área UC - Administrador
            </div>
            <div class="navbar-user">
                <div class="user-badge">
                    <span class="status-indicator"></span>
                    <?= esc($user['full_name']) ?>
                </div>
                <a href="/logout" class="btn btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">¡Bienvenido, Administrador!</h1>
            <p class="welcome-subtitle">Panel de control de área especializada</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-number"><?= $stats['pending_reviews'] ?></div>
                <div class="stat-label">Pendientes de Revisión</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?= $stats['approved_projects'] ?></div>
                <div class="stat-label">Proyectos Aprobados</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div class="stat-number"><?= $stats['rejected_projects'] ?></div>
                <div class="stat-label">Proyectos Rechazados</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number">Normal</div>
                <div class="stat-label">Carga de Trabajo</div>
            </div>
        </div>

        <!-- Actions Section -->
        <div class="actions-section">
            <h2 class="actions-title">Acciones de Área</h2>
            <div class="actions-grid">
                <a href="#" class="action-card">
                    <div class="action-icon">📋</div>
                    <div class="action-title">Revisar Proyectos</div>
                    <div class="action-description">Aprobar o rechazar proyectos asignados</div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">📎</div>
                    <div class="action-title">Gestionar Documentos</div>
                    <div class="action-description">Revisar documentos subidos por usuarios</div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">📊</div>
                    <div class="action-title">Reportes de Área</div>
                    <div class="action-description">Ver estadísticas de tu área</div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">⚙️</div>
                    <div class="action-title">Configuración</div>
                    <div class="action-description">Ajustes de notificaciones y área</div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">👥</div>
                    <div class="action-title">Asignar Revisores</div>
                    <div class="action-description">Delegar proyectos a otros revisores</div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">🔔</div>
                    <div class="action-title">Notificaciones</div>
                    <div class="action-description">Ver notificaciones pendientes</div>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Mensaje de bienvenida
        console.log('Dashboard Administrador cargado correctamente');
        
        // Mostrar información del usuario
        console.log('Usuario:', <?= json_encode($user) ?>);
        
        // Auto-actualizar estadísticas cada 60 segundos
        setInterval(() => {
            console.log('Actualizando estadísticas de área...');
            // Aquí se podría hacer una petición AJAX para actualizar stats
        }, 60000);
    </script>
</body>
</html>