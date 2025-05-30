<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard Usuario' ?></title>
    
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
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
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
            color: #10b981;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 500;
        }

        .quick-actions {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .quick-actions-title {
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
            border-color: #10b981;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1);
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

        .projects-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .projects-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .empty-state-text {
            font-size: 1.125rem;
            margin-bottom: 1rem;
        }

        .empty-state-subtext {
            font-size: 0.875rem;
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
                🏛️ Sistema Multi-Área UC
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
            <h1 class="welcome-title">¡Bienvenido al Sistema Multi-Área!</h1>
            <p class="welcome-subtitle">Gestiona tus proyectos y da seguimiento a su progreso</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-number"><?= $stats['total_projects'] ?></div>
                <div class="stat-label">Proyectos Totales</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⚡</div>
                <div class="stat-number"><?= $stats['active_projects'] ?></div>
                <div class="stat-label">Proyectos Activos</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?= $stats['completed_projects'] ?></div>
                <div class="stat-label">Proyectos Completados</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-number"><?= $stats['pending_projects'] ?></div>
                <div class="stat-label">Proyectos Pendientes</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2 class="quick-actions-title">Acciones Rápidas</h2>
            <div class="actions-grid">
                <a href="#" class="action-card">
                    <div class="action-icon">➕</div>
                    <div class="action-title">Crear Proyecto</div>
                    <div class="action-description">Inicia un nuevo proyecto</div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">📋</div>
                    <div class="action-title">Mis Proyectos</div>
                    <div class="action-description">Ver todos mis proyectos</div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">📎</div>
                    <div class="action-title">Subir Documentos</div>
                    <div class="action-description">Agregar documentos a proyectos</div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">🔔</div>
                    <div class="action-title">Notificaciones</div>
                    <div class="action-description">Ver actualizaciones</div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">📊</div>
                    <div class="action-title">Reportes</div>
                    <div class="action-description">Ver progreso y estadísticas</div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-icon">❓</div>
                    <div class="action-title">Ayuda</div>
                    <div class="action-description">Centro de ayuda y FAQ</div>
                </a>
            </div>
        </div>

        <!-- Recent Projects -->
        <div class="projects-section">
            <h2 class="projects-title">Proyectos Recientes</h2>
            
            <?php if (empty($recent_projects)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📁</div>
                    <div class="empty-state-text">No tienes proyectos aún</div>
                    <div class="empty-state-subtext">
                        Crea tu primer proyecto haciendo clic en "Crear Proyecto"
                    </div>
                </div>
            <?php else: ?>
                <!-- Aquí irían los proyectos cuando existan -->
                <p>Proyectos recientes aparecerán aquí...</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Mensaje de bienvenida
        console.log('Dashboard Usuario cargado correctamente');
        
        // Mostrar información del usuario
        console.log('Usuario:', <?= json_encode($user) ?>);
        
        // Simular carga de datos
        setTimeout(() => {
            console.log('Datos del dashboard actualizados');
        }, 1000);
    </script>
</body>
</html>