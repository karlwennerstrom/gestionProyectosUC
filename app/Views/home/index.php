<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema Multi-Área UC' ?></title>
    <meta name="description" content="<?= $description ?? 'Sistema de gestión de proyectos Universidad Católica' ?>">
    
    <!-- Estilos básicos -->
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
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s ease;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        /* Main Content */
        .main {
            padding: 4rem 0;
        }

        .hero {
            text-align: center;
            margin-bottom: 4rem;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.25rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Features */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .feature-description {
            color: #6b7280;
        }

        /* Stats */
        .stats {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 4rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 500;
        }

        /* Footer */
        .footer {
            background: #1f2937;
            color: white;
            padding: 2rem 0;
            text-align: center;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: #f9fafb;
        }

        .footer-section p,
        .footer-section a {
            color: #d1d5db;
            text-decoration: none;
        }

        .footer-section a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid #374151;
            padding-top: 2rem;
            color: #9ca3af;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .features {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Status indicator */
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            margin-right: 8px;
        }

        /* Environment badge */
        .env-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    🏛️ Sistema Multi-Área UC
                    <?php if ($environment === 'development'): ?>
                        <span class="env-badge">DEV</span>
                    <?php endif; ?>
                </div>
                <nav class="nav-links">
                    <span class="status-indicator"></span>
                    <a href="/about">Acerca de</a>
                    <a href="/help">Ayuda</a>
                    <a href="/status">Estado</a>
                    <?php if ($show_login ?? false): ?>
                        <a href="/auth/login" class="cta-button">Iniciar Sesión</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <!-- Hero Section -->
            <section class="hero">
                <h1>Sistema Multi-Área UC</h1>
                <p><?= $description ?? 'Gestión integral de proyectos con flujo de aprobaciones por áreas especializadas' ?></p>
                <?php if ($show_login ?? false): ?>
                    <a href="/auth/login" class="cta-button">
                        🔐 Acceder al Sistema
                    </a>
                <?php endif; ?>
            </section>

            <!-- Features -->
            <section class="features">
                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3 class="feature-title">Autenticación Segura</h3>
                    <p class="feature-description">
                        Sistema de autenticación seguro y centralizado para acceso controlado al sistema.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📋</div>
                    <h3 class="feature-title">Gestión de Proyectos</h3>
                    <p class="feature-description">
                        Crea, gestiona y da seguimiento a tus proyectos a través de un flujo estructurado de aprobaciones.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3 class="feature-title">Flujo Multi-Área</h3>
                    <p class="feature-description">
                        Aprobaciones automáticas por múltiples áreas especializadas: Arquitectura, Seguridad, Infraestructura y más.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📎</div>
                    <h3 class="feature-title">Gestión de Documentos</h3>
                    <p class="feature-description">
                        Sube, versiona y gestiona todos los documentos de tu proyecto de forma segura y organizada.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <h3 class="feature-title">Notificaciones</h3>
                    <p class="feature-description">
                        Recibe notificaciones automáticas por email sobre el estado de tus proyectos y documentos.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3 class="feature-title">Reportes y Auditoría</h3>
                    <p class="feature-description">
                        Genera reportes detallados y mantén un historial completo de todas las acciones del sistema.
                    </p>
                </div>
            </section>

            <!-- System Stats -->
            <section class="stats">
                <div class="stats-grid">
                    <div>
                        <div class="stat-number">9</div>
                        <div class="stat-label">Áreas Especializadas</div>
                    </div>
                    <div>
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Disponibilidad</div>
                    </div>
                    <div>
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Seguro</div>
                    </div>
                    <div>
                        <div class="stat-number">v1.0</div>
                        <div class="stat-label">Versión Actual</div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Sistema Multi-Área UC</h3>
                    <p>Plataforma integral para la gestión de proyectos de desarrollo tecnológico de la Universidad Católica.</p>
                </div>
                <div class="footer-section">
                    <h3>Enlaces Rápidos</h3>
                    <p><a href="/about">Acerca del Sistema</a></p>
                    <p><a href="/help">Centro de Ayuda</a></p>
                    <p><a href="/status">Estado del Sistema</a></p>
                </div>
                <div class="footer-section">
                    <h3>Soporte Técnico</h3>
                    <p>Email: soporte-multiarea@uc.cl</p>
                    <p>Teléfono: +56 2 2354 4000</p>
                    <p>Horario: Lunes a Viernes 8:00 - 18:00</p>
                </div>
                <div class="footer-section">
                    <h3>Universidad Católica</h3>
                    <p><a href="https://www.uc.cl" target="_blank">Sitio Principal</a></p>
                    <p><a href="https://portal.uc.cl" target="_blank">Portal UC</a></p>
                    <p><a href="https://tic.uc.cl" target="_blank">TIC UC</a></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Universidad Católica de Chile. Todos los derechos reservados.</p>
                <p>Sistema Multi-Área v1.0 - Desarrollado con CodeIgniter 4</p>
            </div>
        </div>
    </footer>

    <script>
        // Verificar estado del sistema
        fetch('/status')
            .then(response => response.json())
            .then(data => {
                const indicator = document.querySelector('.status-indicator');
                if (data.system === 'operational') {
                    indicator.style.background = '#10b981'; // Verde
                } else if (data.system === 'degraded') {
                    indicator.style.background = '#f59e0b'; // Amarillo
                } else {
                    indicator.style.background = '#ef4444'; // Rojo
                }
            })
            .catch(() => {
                document.querySelector('.status-indicator').style.background = '#6b7280'; // Gris
            });
    </script>
</body>
</html>