<?php
// app/Views/dashboard/user.php
$this->extend('layouts/main');

// Configuración específica del dashboard
$navbar_type = 'dashboard';
$title = $title ?? 'Dashboard Usuario - Sistema Multi-Área UC';

$this->section('content');
?>

<div class="container">
    <!-- Welcome Section -->
    <section class="card mb-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-sm">
                    ¡Bienvenido al Sistema Multi-Área!
                </h1>
                <p class="text-xl text-gray-500">
                    Gestiona tus proyectos y da seguimiento a su progreso
                </p>
            </div>
            <div class="text-right">
                <div class="user-badge text-lg mb-sm">
                    <span class="status-indicator"></span>
                    <?= esc($user['full_name']) ?>
                </div>
                <p class="text-sm text-gray-500">
                    Último acceso: <?= date('d/m/Y H:i') ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Stats Grid -->
    <section class="grid grid-cols-4 gap-lg mb-2xl">
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
    </section>

    <!-- Quick Actions -->
    <section class="card mb-2xl">
        <h2 class="card-title">🚀 Acciones Rápidas</h2>
        <div class="grid grid-cols-3 gap-md">
            <a href="/projects/create" class="action-card">
                <div class="action-icon">➕</div>
                <div class="action-title">Crear Proyecto</div>
                <div class="action-description">Inicia un nuevo proyecto de desarrollo</div>
            </a>

            <a href="/projects" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-title">Mis Proyectos</div>
                <div class="action-description">Ver y gestionar todos mis proyectos</div>
            </a>

            <a href="/documents/upload" class="action-card">
                <div class="action-icon">📎</div>
                <div class="action-title">Subir Documentos</div>
                <div class="action-description">Agregar documentos a proyectos existentes</div>
            </a>

            <a href="/notifications" class="action-card">
                <div class="action-icon">🔔</div>
                <div class="action-title">Notificaciones</div>
                <div class="action-description">Ver actualizaciones y alertas</div>
                <?php if (($notification_count ?? 0) > 0): ?>
                    <div class="absolute -top-sm -right-sm bg-error-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">
                        <?= $notification_count ?>
                    </div>
                <?php endif; ?>
            </a>

            <a href="/reports" class="action-card">
                <div class="action-icon">📊</div>
                <div class="action-title">Reportes</div>
                <div class="action-description">Ver progreso y estadísticas detalladas</div>
            </a>

            <a href="/help" class="action-card">
                <div class="action-icon">❓</div>
                <div class="action-title">Centro de Ayuda</div>
                <div class="action-description">FAQ y documentación del sistema</div>
            </a>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-xl">
        <!-- Recent Projects -->
        <section class="card">
            <div class="flex items-center justify-between mb-lg">
                <h2 class="card-title mb-0">📁 Proyectos Recientes</h2>
                <a href="/projects" class="btn btn-secondary btn-sm">Ver Todos</a>
            </div>
            
            <?php if (!empty($recent_projects)): ?>
                <div class="space-y-md">
                    <?php foreach (array_slice($recent_projects, 0, 5) as $project): ?>
                        <div class="flex items-center justify-between p-md bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center gap-sm mb-xs">
                                    <span class="font-semibold text-primary">
                                        <?= esc($project['code']) ?>
                                    </span>
                                    <span class="text-xs px-sm py-xs rounded-full <?= getStatusClass($project['status']) ?>">
                                        <?= getStatusLabel($project['status']) ?>
                                    </span>
                                </div>
                                <h3 class="font-medium text-gray-800 mb-xs">
                                    <?= esc($project['title']) ?>
                                </h3>
                                <div class="flex items-center gap-md text-sm text-gray-500">
                                    <span>📅 <?= date('d/m/Y', strtotime($project['created_at'])) ?></span>
                                    <span>📊 <?= number_format($project['completion_percentage'], 0) ?>%</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-xs">
                                <a href="/projects/<?= $project['id'] ?>" 
                                   class="btn btn-sm btn-secondary" 
                                   data-tooltip="Ver proyecto">
                                    👁️
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state py-lg">
                    <div class="empty-state-icon text-2xl">📁</div>
                    <div class="empty-state-text">No tienes proyectos aún</div>
                    <div class="empty-state-subtext mb-md">
                        Crea tu primer proyecto para comenzar
                    </div>
                    <a href="/projects/create" class="btn btn-primary">
                        ➕ Crear Proyecto
                    </a>
                </div>
            <?php endif; ?>
        </section>

        <!-- Recent Notifications -->
        <section class="card">
            <div class="flex items-center justify-between mb-lg">
                <h2 class="card-title mb-0">🔔 Notificaciones Recientes</h2>
                <a href="/notifications" class="btn btn-secondary btn-sm">Ver Todas</a>
            </div>
            
            <?php if (!empty($pending_notifications)): ?>
                <div class="space-y-sm">
                    <?php foreach (array_slice($pending_notifications, 0, 5) as $notification): ?>
                        <div class="flex items-start gap-sm p-sm border-l-4 <?= getNotificationClass($notification['type']) ?> bg-gray-50 rounded">
                            <div class="text-lg">
                                <?= getNotificationIcon($notification['type']) ?>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-800 text-sm">
                                    <?= esc($notification['title']) ?>
                                </h4>
                                <p class="text-xs text-gray-600 mb-xs">
                                    <?= esc(substr($notification['message'], 0, 80)) ?>...
                                </p>
                                <div class="text-xs text-gray-500">
                                    <?= timeAgo($notification['created_at']) ?>
                                </div>
                            </div>
                            <?php if (!$notification['read_status']): ?>
                                <span class="w-2 h-2 bg-primary-500 rounded-full"></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state py-lg">
                    <div class="empty-state-icon text-2xl">🔔</div>
                    <div class="empty-state-text">No hay notificaciones</div>
                    <div class="empty-state-subtext">
                        Las notificaciones aparecerán aquí
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- System Status -->
    <section class="card mt-xl">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-sm">Estado del Sistema</h3>
                <div class="flex items-center gap-lg text-sm">
                    <div class="flex items-center gap-xs">
                        <span class="status-indicator"></span>
                        <span class="text-gray-600">Sistema Operativo</span>
                    </div>
                    <div class="text-gray-500">
                        Última actualización: <?= date('d/m/Y H:i') ?>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500 mb-xs">Versión del Sistema</div>
                <div class="font-semibold text-primary">v1.0.0</div>
            </div>
        </div>
    </section>
</div>

<?php
// Funciones auxiliares para el template
function getStatusClass($status) {
    $classes = [
        'draft' => 'bg-gray-100 text-gray-700',
        'submitted' => 'bg-blue-100 text-blue-700',
        'in_progress' => 'bg-yellow-100 text-yellow-700',
        'on_hold' => 'bg-orange-100 text-orange-700',
        'completed' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
        'cancelled' => 'bg-gray-100 text-gray-700'
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-700';
}

function getStatusLabel($status) {
    $labels = [
        'draft' => 'Borrador',
        'submitted' => 'Enviado',
        'in_progress' => 'En Progreso',
        'on_hold' => 'En Pausa',
        'completed' => 'Completado',
        'rejected' => 'Rechazado',
        'cancelled' => 'Cancelado'
    ];
    return $labels[$status] ?? ucfirst($status);
}

function getNotificationClass($type) {
    $classes = [
        'success' => 'border-green-400',
        'warning' => 'border-yellow-400',
        'error' => 'border-red-400',
        'info' => 'border-blue-400',
        'project_created' => 'border-green-400',
        'project_updated' => 'border-blue-400',
        'document_uploaded' => 'border-purple-400'
    ];
    return $classes[$type] ?? 'border-gray-400';
}

function getNotificationIcon($type) {
    $icons = [
        'success' => '✅',
        'warning' => '⚠️',
        'error' => '❌',
        'info' => 'ℹ️',
        'project_created' => '📋',
        'project_updated' => '📝',
        'document_uploaded' => '📎'
    ];
    return $icons[$type] ?? '📢';
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Hace unos segundos';
    if ($time < 3600) return 'Hace ' . floor($time/60) . ' minutos';
    if ($time < 86400) return 'Hace ' . floor($time/3600) . ' horas';
    if ($time < 2592000) return 'Hace ' . floor($time/86400) . ' días';
    
    return date('d/m/Y', strtotime($datetime));
}
?>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
// JavaScript específico del dashboard de usuario
console.log('Dashboard Usuario cargado correctamente');
console.log('Usuario:', <?= json_encode($user) ?>);

// Actualizar datos del dashboard cada 60 segundos
setInterval(() => {
    updateDashboardData();
}, 60000);

function updateDashboardData() {
    console.log('Actualizando datos del dashboard...');
    
    // Actualizar notificaciones no leídas
    fetch('/api/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.action-card .absolute');
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count;
                } else {
                    // Crear badge si no existe
                    const notificationCard = document.querySelector('a[href="/notifications"]');
                    if (notificationCard) {
                        const badge = document.createElement('div');
                        badge.className = 'absolute -top-sm -right-sm bg-error-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center';
                        badge.textContent = data.count;
                        notificationCard.style.position = 'relative';
                        notificationCard.appendChild(badge);
                    }
                }
            } else if (badge) {
                badge.remove();
            }
        })
        .catch(error => console.log('Error actualizando notificaciones:', error));
}

// Animación de estadísticas al cargar
const statNumbers = document.querySelectorAll('.stat-number');
statNumbers.forEach((stat, index) => {
    const finalValue = parseInt(stat.textContent);
    stat.textContent = '0';
    
    setTimeout(() => {
        animateNumber(stat, 0, finalValue, 1000);
    }, index * 200);
});

function animateNumber(element, start, end, duration) {
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = Math.floor(progress * (end - start) + start);
        
        element.textContent = current;
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

// Marcar notificaciones como leídas al hacer hover
document.querySelectorAll('.border-l-4').forEach(notification => {
    notification.addEventListener('mouseenter', function() {
        const unreadDot = this.querySelector('.w-2.h-2');
        if (unreadDot) {
            setTimeout(() => {
                unreadDot.style.opacity = '0.5';
            }, 1000);
        }
    });
});

// Shortcuts de teclado
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'n': // Ctrl+N = Nuevo proyecto
                e.preventDefault();
                window.location.href = '/projects/create';
                break;
            case 'p': // Ctrl+P = Mis proyectos
                e.preventDefault();
                window.location.href = '/projects';
                break;
            case 'h': // Ctrl+H = Ayuda
                e.preventDefault();
                window.location.href = '/help';
                break;
        }
    }
});

// Mostrar shortcuts al usuario
console.log('Shortcuts disponibles:');
console.log('Ctrl+N: Crear nuevo proyecto');
console.log('Ctrl+P: Ver mis proyectos');
console.log('Ctrl+H: Centro de ayuda');
<?php $this->endSection(); ?>