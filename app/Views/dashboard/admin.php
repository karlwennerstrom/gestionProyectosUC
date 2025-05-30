<?php
// app/Views/dashboard/admin.php
$this->extend('layouts/main');

// Configuración específica del dashboard admin
$navbar_type = 'admin';
$title = $title ?? 'Dashboard Administrador - Sistema Multi-Área UC';

$this->section('content');
?>

<div class="container">
    <!-- Welcome Section -->
    <section class="card mb-2xl" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-sm">
                    ¡Bienvenido, Administrador!
                </h1>
                <p class="text-xl text-gray-500">
                    Panel de control de área especializada
                </p>
                <?php if (!empty($user['assigned_areas'])): ?>
                    <div class="flex items-center gap-sm mt-md">
                        <span class="text-sm text-gray-600">Áreas asignadas:</span>
                        <?php foreach ($user['assigned_areas'] as $area): ?>
                            <span class="inline-block px-sm py-xs rounded text-xs font-medium"
                                  style="background: <?= $area['area_color'] ?>20; color: <?= $area['area_color'] ?>;">
                                <?= esc($area['area_name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-right">
                <div class="user-badge text-lg mb-sm" style="background: rgba(59, 130, 246, 0.1); color: #1e40af;">
                    <span class="status-indicator"></span>
                    <?= esc($user['full_name']) ?>
                </div>
                <p class="text-sm text-gray-500">
                    Rol: Administrador de Área
                </p>
            </div>
        </div>
    </section>

    <!-- Stats Grid -->
    <section class="grid grid-cols-4 gap-lg mb-2xl">
        <div class="stat-card" style="border-left: 4px solid #f59e0b;">
            <div class="stat-icon">⏳</div>
            <div class="stat-number"><?= $stats['pending_reviews'] ?></div>
            <div class="stat-label">Pendientes de Revisión</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-icon">✅</div>
            <div class="stat-number"><?= $stats['approved_projects'] ?></div>
            <div class="stat-label">Proyectos Aprobados</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #ef4444;">
            <div class="stat-icon">❌</div>
            <div class="stat-number"><?= $stats['rejected_projects'] ?></div>
            <div class="stat-label">Proyectos Rechazados</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-icon">📊</div>
            <div class="stat-number">Normal</div>
            <div class="stat-label">Carga de Trabajo</div>
        </div>
    </section>

    <!-- Priority Actions -->
    <?php if (($stats['pending_reviews'] ?? 0) > 0 || ($stats['overdue_projects'] ?? 0) > 0): ?>
    <section class="card mb-2xl" style="border-left: 4px solid #ef4444;">
        <div class="flex items-center gap-md mb-lg">
            <div class="text-2xl">🚨</div>
            <div>
                <h2 class="text-xl font-semibold text-error">Acciones Prioritarias</h2>
                <p class="text-gray-600">Elementos que requieren tu atención inmediata</p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-md">
            <?php if (($stats['pending_reviews'] ?? 0) > 0): ?>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-orange-800">Proyectos Pendientes</h3>
                        <p class="text-sm text-orange-600">
                            <?= $stats['pending_reviews'] ?> proyectos esperan tu revisión
                        </p>
                    </div>
                    <a href="/admin/projects/pending" class="btn btn-warning btn-sm">
                        Revisar Ahora
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (($stats['overdue_projects'] ?? 0) > 0): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-red-800">Proyectos Vencidos</h3>
                        <p class="text-sm text-red-600">
                            <?= $stats['overdue_projects'] ?> proyectos pasaron su fecha límite
                        </p>
                    </div>
                    <a href="/admin/projects/overdue" class="btn btn-error btn-sm">
                        Ver Vencidos
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Actions Grid -->
    <section class="card mb-2xl">
        <h2 class="card-title">🎯 Acciones de Área</h2>
        <div class="grid grid-cols-3 gap-md">
            <a href="/admin/projects/pending" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-title">Revisar Proyectos</div>
                <div class="action-description">Aprobar o rechazar proyectos asignados</div>
                <?php if (($stats['pending_reviews'] ?? 0) > 0): ?>
                    <div class="absolute -top-sm -right-sm bg-warning-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">
                        <?= $stats['pending_reviews'] ?>
                    </div>
                <?php endif; ?>
            </a>

            <a href="/admin/documents" class="action-card">
                <div class="action-icon">📎</div>
                <div class="action-title">Gestionar Documentos</div>
                <div class="action-description">Revisar documentos subidos por usuarios</div>
            </a>

            <a href="/admin/reports" class="action-card">
                <div class="action-icon">📊</div>
                <div class="action-title">Reportes de Área</div>
                <div class="action-description">Ver estadísticas y métricas de tu área</div>
            </a>

            <a href="/admin/settings" class="action-card">
                <div class="action-icon">⚙️</div>
                <div class="action-title">Configuración</div>
                <div class="action-description">Ajustes de notificaciones y área</div>
            </a>

            <a href="/admin/assign" class="action-card">
                <div class="action-icon">👥</div>
                <div class="action-title">Asignar Revisores</div>
                <div class="action-description">Delegar proyectos a otros revisores</div>
            </a>

            <a href="/admin/notifications" class="action-card">
                <div class="action-icon">🔔</div>
                <div class="action-title">Notificaciones</div>
                <div class="action-description">Gestionar alertas y comunicaciones</div>
            </a>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-xl">
        <!-- Pending Projects -->
        <section class="card">
            <div class="flex items-center justify-between mb-lg">
                <h2 class="card-title mb-0">📋 Proyectos Pendientes</h2>
                <a href="/admin/projects/pending" class="btn btn-secondary btn-sm">Ver Todos</a>
            </div>
            
            <?php if (!empty($pending_projects)): ?>
                <div class="space-y-md">
                    <?php foreach (array_slice($pending_projects, 0, 5) as $project): ?>
                        <div class="border border-gray-200 rounded-lg p-md hover:border-primary-300 transition-colors">
                            <div class="flex items-start justify-between mb-sm">
                                <div class="flex-1">
                                    <div class="flex items-center gap-sm mb-xs">
                                        <span class="font-semibold text-primary">
                                            <?= esc($project['code']) ?>
                                        </span>
                                        <span class="text-xs px-sm py-xs bg-yellow-100 text-yellow-700 rounded-full">
                                            <?= getPriorityLabel($project['priority']) ?>
                                        </span>
                                    </div>
                                    <h3 class="font-medium text-gray-800 mb-xs">
                                        <?= esc($project['title']) ?>
                                    </h3>
                                    <div class="flex items-center gap-md text-sm text-gray-500">
                                        <span>👤 <?= esc($project['requester_name']) ?></span>
                                        <span>📅 <?= date('d/m/Y', strtotime($project['created_at'])) ?></span>
                                        <?php if (!empty($project['due_date'])): ?>
                                            <span class="<?= strtotime($project['due_date']) < time() ? 'text-red-500' : '' ?>">
                                                ⏰ <?= date('d/m/Y', strtotime($project['due_date'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex items-center gap-xs">
                                    <a href="/admin/projects/<?= $project['id'] ?>/review" 
                                       class="btn btn-sm btn-primary" 
                                       data-tooltip="Revisar proyecto">
                                        👁️
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state py-lg">
                    <div class="empty-state-icon text-2xl">✅</div>
                    <div class="empty-state-text">No hay proyectos pendientes</div>
                    <div class="empty-state-subtext">
                        Todos los proyectos están al día
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Area Statistics -->
        <section class="card">
            <h2 class="card-title">📈 Estadísticas del Área</h2>
            
            <!-- Performance Metrics -->
            <div class="space-y-lg">
                <div>
                    <div class="flex justify-between text-sm mb-xs">
                        <span class="text-gray-600">Eficiencia de Revisión</span>
                        <span class="font-semibold">85%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 85%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-xs">
                        <span class="text-gray-600">Tiempo Promedio de Revisión</span>
                        <span class="font-semibold">2.3 días</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 70%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-xs">
                        <span class="text-gray-600">Satisfacción del Usuario</span>
                        <span class="font-semibold">4.7/5</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full" style="width: 94%"></div>
                    </div>
                </div>
            </div>

            <!-- Monthly Summary -->
            <div class="mt-lg pt-lg border-t border-gray-200">
                <h3 class="font-semibold text-gray-800 mb-md">Resumen del Mes</h3>
                <div class="grid grid-cols-2 gap-md text-sm">
                    <div class="text-center p-md bg-green-50 rounded">
                        <div class="font-bold text-green-600 text-lg">
                            <?= $stats['monthly_approved'] ?? 0 ?>
                        </div>
                        <div class="text-green-700">Aprobados</div>
                    </div>
                    <div class="text-center p-md bg-red-50 rounded">
                        <div class="font-bold text-red-600 text-lg">
                            <?= $stats['monthly_rejected'] ?? 0 ?>
                        </div>
                        <div class="text-red-700">Rechazados</div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Quick Tools -->
    <section class="card mt-xl">
        <h2 class="card-title">🛠️ Herramientas Rápidas</h2>
        <div class="flex items-center gap-md flex-wrap">
            <button onclick="App.openModal('bulk-approve-modal')" 
                    class="btn btn-success">
                ✅ Aprobación Masiva
            </button>
            
            <button onclick="exportAreaReport()" 
                    class="btn btn-secondary">
                📊 Exportar Reporte
            </button>
            
            <button onclick="App.openModal('notification-modal')" 
                    class="btn btn-secondary">
                📢 Enviar Notificación
            </button>
            
            <a href="/admin/templates" class="btn btn-secondary">
                📝 Plantillas de Respuesta
            </a>
            
            <button onclick="refreshDashboard()" 
                    class="btn btn-outline">
                🔄 Actualizar Datos
            </button>
        </div>
    </section>
</div>

<?php
function getPriorityLabel($priority) {
    $labels = [
        'low' => 'Baja',
        'medium' => 'Media', 
        'high' => 'Alta',
        'critical' => 'Crítica'
    ];
    return $labels[$priority] ?? ucfirst($priority);
}
?>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
// JavaScript específico del dashboard de administrador
console.log('Dashboard Administrador cargado correctamente');
console.log('Usuario:', <?= json_encode($user) ?>);

// Auto-actualizar estadísticas cada 30 segundos
setInterval(() => {
    updateAdminStats();
}, 30000);

function updateAdminStats() {
    console.log('Actualizando estadísticas de área...');
    
    fetch('/admin/api/stats')
        .then(response => response.json())
        .then(data => {
            // Actualizar números en las stats cards
            document.querySelector('.stat-card:nth-child(1) .stat-number').textContent = data.pending_reviews || 0;
            document.querySelector('.stat-card:nth-child(2) .stat-number').textContent = data.approved_projects || 0;
            document.querySelector('.stat-card:nth-child(3) .stat-number').textContent = data.rejected_projects || 0;
            
            // Actualizar badges de notificación
            const pendingBadge = document.querySelector('a[href="/admin/projects/pending"] .absolute');
            if (data.pending_reviews > 0) {
                if (pendingBadge) {
                    pendingBadge.textContent = data.pending_reviews;
                }
            } else if (pendingBadge) {
                pendingBadge.remove();
            }
        })
        .catch(error => console.log('Error actualizando estadísticas:', error));
}

// Función para exportar reporte
function exportAreaReport() {
    App.setLoading(event.target, true);
    
    fetch('/admin/reports/export', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            type: 'area_summary',
            format: 'excel'
        })
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `reporte_area_${new Date().toISOString().split('T')[0]}.xlsx`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        
        App.showNotification('Reporte exportado exitosamente', 'success');
    })
    .catch(error => {
        console.error('Error exportando reporte:', error);
        App.showNotification('Error al exportar reporte', 'error');
    })
    .finally(() => {
        App.setLoading(event.target, false);
    });
}

// Función para refrescar dashboard
function refreshDashboard() {
    App.setLoading(event.target, true);
    
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

// Notificaciones en tiempo real
if ('WebSocket' in window) {
    // Conectar a WebSocket para notificaciones en tiempo real
    // const ws = new WebSocket('wss://sistema.uc.cl/ws/admin');
    // ws.onmessage = function(event) {
    //     const data = JSON.parse(event.data);
    //     if (data.type === 'new_project') {
    //         App.showNotification(`Nuevo proyecto: ${data.project_code}`, 'info');
    //         updateAdminStats();
    //     }
    // };
}

// Shortcuts de teclado para administradores
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'r': // Ctrl+R = Revisar proyectos pendientes
                e.preventDefault();
                window.location.href = '/admin/projects/pending';
                break;
            case 'd': // Ctrl+D = Dashboard
                e.preventDefault();
                window.location.href = '/admin/dashboard';
                break;
            case 'e': // Ctrl+E = Exportar reporte
                e.preventDefault();
                exportAreaReport();
                break;
        }
    }
});

// Confirmación antes de cerrar si hay proyectos pendientes
window.addEventListener('beforeunload', function(e) {
    const pendingCount = <?= $stats['pending_reviews'] ?? 0 ?>;
    if (pendingCount > 0) {
        e.preventDefault();
        e.returnValue = `Tienes ${pendingCount} proyectos pendientes de revisión. ¿Estás seguro de salir?`;
    }
});

console.log('Shortcuts disponibles:');
console.log('Ctrl+R: Revisar proyectos pendientes');
console.log('Ctrl+D: Ir al dashboard');
console.log('Ctrl+E: Exportar reporte del área');
<?php $this->endSection(); ?>