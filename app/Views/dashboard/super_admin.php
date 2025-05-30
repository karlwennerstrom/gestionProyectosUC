<?php
// app/Views/dashboard/super_admin.php
$this->extend('layouts/main');

// Configuración específica del dashboard super admin
$navbar_type = 'super_admin';
$title = $title ?? 'Dashboard Super Administrador - Sistema Multi-Área UC';

$this->section('content');
?>

<div class="container">
    <!-- Welcome Section -->
    <section class="card mb-2xl" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-sm">
                    ¡Bienvenido, Super Administrador!
                </h1>
                <p class="text-xl text-gray-500">
                    Panel de control global del Sistema Multi-Área UC
                </p>
                <div class="flex items-center gap-md mt-md">
                    <span class="inline-block px-md py-sm rounded-lg text-sm font-medium bg-red-100 text-red-700">
                        🛡️ Acceso Total del Sistema
                    </span>
                    <span class="text-sm text-gray-500">
                        Última conexión: <?= date('d/m/Y H:i') ?>
                    </span>
                </div>
            </div>
            <div class="text-right">
                <div class="user-badge text-lg mb-sm" style="background: rgba(220, 38, 38, 0.1); color: #dc2626;">
                    <span class="status-indicator" style="background: #dc2626;"></span>
                    <?= esc($user['full_name']) ?>
                </div>
                <p class="text-sm text-gray-500">
                    Super Administrador del Sistema
                </p>
            </div>
        </div>
    </section>

    <!-- System Health Alert -->
    <section class="card mb-2xl" style="border-left: 4px solid #10b981;">
        <div class="flex items-center gap-md">
            <div class="text-2xl">✅</div>
            <div class="flex-1">
                <h3 class="font-semibold text-green-800">Estado del Sistema: Operativo</h3>
                <p class="text-sm text-green-600">
                    Todos los servicios funcionan correctamente. Última verificación: <?= date('H:i') ?>
                </p>
            </div>
            <div class="flex items-center gap-sm">
                <a href="/super-admin/monitoring" class="btn btn-success btn-sm">
                    📊 Ver Monitoreo
                </a>
                <button onclick="checkSystemHealth()" class="btn btn-outline btn-sm">
                    🔄 Verificar
                </button>
            </div>
        </div>
    </section>

    <!-- Global Stats Grid -->
    <section class="grid grid-cols-4 gap-lg mb-2xl">
        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-icon">👥</div>
            <div class="stat-number"><?= $stats['total_users'] ?></div>
            <div class="stat-label">Usuarios Totales</div>
            <div class="text-xs text-gray-500 mt-xs">
                +<?= $stats['new_users_today'] ?? 0 ?> hoy
            </div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #f59e0b;">
            <div class="stat-icon">🔑</div>
            <div class="stat-number"><?= $stats['total_admins'] ?></div>
            <div class="stat-label">Administradores</div>
            <div class="text-xs text-gray-500 mt-xs">
                En <?= $stats['active_areas'] ?? 0 ?> áreas
            </div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-icon">📋</div>
            <div class="stat-number"><?= $stats['total_projects'] ?></div>
            <div class="stat-label">Proyectos Activos</div>
            <div class="text-xs text-gray-500 mt-xs">
                <?= $stats['completed_today'] ?? 0 ?> completados hoy
            </div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #ef4444;">
            <div class="stat-icon">⚡</div>
            <div class="stat-number system-health">100%</div>
            <div class="stat-label">Salud del Sistema</div>
            <div class="text-xs text-gray-500 mt-xs">
                Todos los servicios OK
            </div>
        </div>
    </section>

    <!-- Critical Actions -->
    <?php if (($stats['pending_approvals'] ?? 0) > 0 || ($stats['failed_jobs'] ?? 0) > 0): ?>
    <section class="card mb-2xl" style="border-left: 4px solid #ef4444;">
        <div class="flex items-center gap-md mb-lg">
            <div class="text-2xl">🚨</div>
            <div>
                <h2 class="text-xl font-semibold text-error">Atención Requerida</h2>
                <p class="text-gray-600">Elementos críticos que necesitan intervención inmediata</p>
            </div>
        </div>
        
        <div class="grid grid-cols-3 gap-md">
            <?php if (($stats['pending_approvals'] ?? 0) > 0): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-red-800">Aprobaciones Pendientes</h3>
                        <p class="text-sm text-red-600">
                            <?= $stats['pending_approvals'] ?> elementos necesitan aprobación
                        </p>
                    </div>
                    <a href="/super-admin/approvals" class="btn btn-error btn-sm">
                        Revisar
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (($stats['failed_jobs'] ?? 0) > 0): ?>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-orange-800">Trabajos Fallidos</h3>
                        <p class="text-sm text-orange-600">
                            <?= $stats['failed_jobs'] ?> trabajos en cola fallaron
                        </p>
                    </div>
                    <a href="/super-admin/jobs" class="btn btn-warning btn-sm">
                        Ver Cola
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (($stats['storage_usage'] ?? 0) > 85): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-yellow-800">Espacio de Almacenamiento</h3>
                        <p class="text-sm text-yellow-600">
                            <?= $stats['storage_usage'] ?>% del espacio utilizado
                        </p>
                    </div>
                    <a href="/super-admin/storage" class="btn btn-warning btn-sm">
                        Gestionar
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Administrative Actions -->
    <section class="card mb-2xl">
        <h2 class="card-title">🎛️ Acciones Administrativas</h2>
        <div class="grid grid-cols-3 gap-md">
            <a href="/super-admin/users" class="action-card">
                <div class="action-icon">👥</div>
                <div class="action-title">Gestionar Usuarios</div>
                <div class="action-description">Crear, editar y administrar usuarios del sistema</div>
            </a>

            <a href="/super-admin/areas" class="action-card">
                <div class="action-icon">🏢</div>
                <div class="action-title">Gestionar Áreas</div>
                <div class="action-description">Configurar áreas y asignar administradores</div>
            </a>

            <a href="/super-admin/reports" class="action-card">
                <div class="action-icon">📊</div>
                <div class="action-title">Reportes Globales</div>
                <div class="action-description">Estadísticas y métricas de todo el sistema</div>
            </a>

            <a href="/super-admin/settings" class="action-card">
                <div class="action-icon">🔧</div>
                <div class="action-title">Configuración Global</div>
                <div class="action-description">Ajustes generales del sistema</div>
            </a>

            <a href="/super-admin/audit" class="action-card">
                <div class="action-icon">🔍</div>
                <div class="action-title">Auditoría</div>
                <div class="action-description">Revisar logs y actividad del sistema</div>
            </a>

            <a href="/super-admin/maintenance" class="action-card">
                <div class="action-icon">⚙️</div>
                <div class="action-title">Mantenimiento</div>
                <div class="action-description">Herramientas de mantenimiento y backup</div>
            </a>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-xl">
        <!-- Real-time Activity -->
        <section class="card">
            <div class="flex items-center justify-between mb-lg">
                <h2 class="card-title mb-0">🔴 Actividad en Tiempo Real</h2>
                <button onclick="toggleAutoRefresh()" class="btn btn-secondary btn-sm" id="refresh-toggle">
                    ⏸️ Pausar
                </button>
            </div>
            
            <div id="activity-feed" class="space-y-sm max-h-80 overflow-y-auto">
                <?php if (!empty($recent_activity)): ?>
                    <?php foreach (array_slice($recent_activity, 0, 10) as $activity): ?>
                        <div class="flex items-start gap-sm p-sm border-l-4 <?= getActivityColor($activity['type']) ?> bg-gray-50 rounded">
                            <div class="text-sm">
                                <?= getActivityIcon($activity['type']) ?>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800">
                                    <?= esc($activity['description']) ?>
                                </p>
                                <div class="flex items-center gap-md text-xs text-gray-500">
                                    <span>👤 <?= esc($activity['user_name']) ?></span>
                                    <span>🕐 <?= timeAgo($activity['created_at']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state py-lg">
                        <div class="empty-state-icon text-2xl">📊</div>
                        <div class="empty-state-text">Sin actividad reciente</div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- System Performance -->
        <section class="card">
            <h2 class="card-title">⚡ Rendimiento del Sistema</h2>
            
            <!-- Performance Metrics -->
            <div class="space-y-lg">
                <div>
                    <div class="flex justify-between text-sm mb-xs">
                        <span class="text-gray-600">CPU Usage</span>
                        <span class="font-semibold" id="cpu-usage">45%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-300" 
                             id="cpu-bar" style="width: 45%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-xs">
                        <span class="text-gray-600">Memory Usage</span>
                        <span class="font-semibold" id="memory-usage">68%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full transition-all duration-300" 
                             id="memory-bar" style="width: 68%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-xs">
                        <span class="text-gray-600">Disk Usage</span>
                        <span class="font-semibold" id="disk-usage"><?= $stats['storage_usage'] ?? 35 ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full transition-all duration-300" 
                             id="disk-bar" style="width: <?= $stats['storage_usage'] ?? 35 ?>%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-xs">
                        <span class="text-gray-600">Database Connections</span>
                        <span class="font-semibold" id="db-connections">12/100</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-orange-500 h-2 rounded-full transition-all duration-300" 
                             id="db-bar" style="width: 12%"></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-lg pt-lg border-t border-gray-200">
                <div class="flex items-center gap-sm flex-wrap">
                    <button onclick="clearCache()" class="btn btn-secondary btn-sm">
                        🗑️ Clear Cache
                    </button>
                    <button onclick="optimizeDatabase()" class="btn btn-secondary btn-sm">
                        🗄️ Optimize DB
                    </button>
                    <button onclick="generateBackup()" class="btn btn-secondary btn-sm">
                        💾 Backup
                    </button>
                </div>
            </div>
        </section>
    </div>

    <!-- Quick Tools -->
    <section class="card mt-xl">
        <h2 class="card-title">🛠️ Herramientas de Super Administrador</h2>
        <div class="grid grid-cols-4 gap-md">
            <button onclick="App.openModal('broadcast-modal')" 
                    class="btn btn-primary">
                📢 Enviar Comunicado Global
            </button>
            
            <button onclick="toggleMaintenanceMode()" 
                    class="btn btn-warning">
                🚧 Modo Mantenimiento
            </button>
            
            <button onclick="generateSystemReport()" 
                    class="btn btn-secondary">
                📋 Reporte Completo
            </button>
            
            <a href="/super-admin/logs" class="btn btn-outline">
                📄 Ver Logs del Sistema
            </a>
        </div>
    </section>
</div>

<?php
function getActivityColor($type) {
    $colors = [
        'user_login' => 'border-green-400',
        'project_created' => 'border-blue-400',
        'project_completed' => 'border-green-400',
        'user_created' => 'border-purple-400',
        'system_error' => 'border-red-400',
        'area_updated' => 'border-yellow-400'
    ];
    return $colors[$type] ?? 'border-gray-400';
}

function getActivityIcon($type) {
    $icons = [
        'user_login' => '🔐',
        'project_created' => '📋',
        'project_completed' => '✅',
        'user_created' => '👤',
        'system_error' => '❌',
        'area_updated' => '🏢'
    ];
    return $icons[$type] ?? '📊';
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'hace ' . $time . 's';
    if ($time < 3600) return 'hace ' . floor($time/60) . 'm';
    if ($time < 86400) return 'hace ' . floor($time/3600) . 'h';
    
    return date('d/m H:i', strtotime($datetime));
}
?>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
// JavaScript específico del dashboard de super administrador
console.log('Dashboard Super Administrador cargado correctamente');
console.log('Usuario:', <?= json_encode($user) ?>);

let autoRefreshEnabled = true;
let refreshInterval;

// Inicializar auto-refresh
function initAutoRefresh() {
    refreshInterval = setInterval(() => {
        if (autoRefreshEnabled) {
            updateSystemMetrics();
            updateActivityFeed();
        }
    }, 5000); // Cada 5 segundos
}

// Toggle auto-refresh
function toggleAutoRefresh() {
    autoRefreshEnabled = !autoRefreshEnabled;
    const button = document.getElementById('refresh-toggle');
    
    if (autoRefreshEnabled) {
        button.innerHTML = '⏸️ Pausar';
        button.classList.remove('btn-success');
        button.classList.add('btn-secondary');
    } else {
        button.innerHTML = '▶️ Reanudar';
        button.classList.remove('btn-secondary');
        button.classList.add('btn-success');
    }
}

// Actualizar métricas del sistema
function updateSystemMetrics() {
    fetch('/super-admin/api/metrics')
        .then(response => response.json())
        .then(data => {
            // Actualizar CPU
            document.getElementById('cpu-usage').textContent = data.cpu_usage + '%';
            document.getElementById('cpu-bar').style.width = data.cpu_usage + '%';
            updateBarColor('cpu-bar', data.cpu_usage);
            
            // Actualizar Memory
            document.getElementById('memory-usage').textContent = data.memory_usage + '%';
            document.getElementById('memory-bar').style.width = data.memory_usage + '%';
            updateBarColor('memory-bar', data.memory_usage);
            
            // Actualizar Disk
            document.getElementById('disk-usage').textContent = data.disk_usage + '%';
            document.getElementById('disk-bar').style.width = data.disk_usage + '%';
            updateBarColor('disk-bar', data.disk_usage);
            
            // Actualizar DB Connections
            document.getElementById('db-connections').textContent = `${data.db_connections}/${data.max_db_connections}`;
            const dbPercentage = (data.db_connections / data.max_db_connections) * 100;
            document.getElementById('db-bar').style.width = dbPercentage + '%';
            updateBarColor('db-bar', dbPercentage);
            
            // Actualizar salud del sistema
            const healthPercentage = Math.min(100, Math.max(0, 100 - (data.cpu_usage + data.memory_usage + data.disk_usage) / 3));
            document.querySelector('.system-health').textContent = Math.round(healthPercentage) + '%';
        })
        .catch(error => console.log('Error actualizando métricas:', error));
}

// Actualizar color de barras según porcentaje
function updateBarColor(barId, percentage) {
    const bar = document.getElementById(barId);
    bar.classList.remove('bg-green-500', 'bg-yellow-500', 'bg-red-500');
    
    if (percentage < 60) {
        bar.classList.add('bg-green-500');
    } else if (percentage < 80) {
        bar.classList.add('bg-yellow-500');
    } else {
        bar.classList.add('bg-red-500');
    }
}

// Actualizar feed de actividad
function updateActivityFeed() {
    fetch('/super-admin/api/activity')
        .then(response => response.json())
        .then(data => {
            // Solo actualizar si hay nuevas actividades
            if (data.activities && data.activities.length > 0) {
                const feed = document.getElementById('activity-feed');
                // Implementar actualización incremental del feed
            }
        })
        .catch(error => console.log('Error actualizando actividad:', error));
}

// Verificar salud del sistema
function checkSystemHealth() {
    App.setLoading(event.target, true);
    
    fetch('/super-admin/api/health-check', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'healthy') {
                App.showNotification('Sistema funcionando correctamente', 'success');
            } else {
                App.showNotification(`Problemas detectados: ${data.issues.join(', ')}`, 'warning');
            }
        })
        .catch(error => {
            App.showNotification('Error verificando sistema', 'error');
        })
        .finally(() => {
            App.setLoading(event.target, false);
        });
}

// Limpiar caché
function clearCache() {
    if (!confirm('¿Estás seguro de limpiar toda la caché del sistema?')) return;
    
    App.setLoading(event.target, true);
    
    fetch('/super-admin/api/clear-cache', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showNotification('Caché limpiada exitosamente', 'success');
            } else {
                App.showNotification('Error limpiando caché', 'error');
            }
        })
        .catch(error => {
            App.showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            App.setLoading(event.target, false);
        });
}

// Optimizar base de datos
function optimizeDatabase() {
    if (!confirm('¿Optimizar la base de datos? Este proceso puede tomar varios minutos.')) return;
    
    App.setLoading(event.target, true);
    
    fetch('/super-admin/api/optimize-database', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showNotification(`Base de datos optimizada. ${data.message}`, 'success');
            } else {
                App.showNotification('Error optimizando base de datos', 'error');
            }
        })
        .catch(error => {
            App.showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            App.setLoading(event.target, false);
        });
}

// Generar backup
function generateBackup() {
    if (!confirm('¿Generar backup completo del sistema?')) return;
    
    App.setLoading(event.target, true);
    
    fetch('/super-admin/api/generate-backup', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showNotification(`Backup generado: ${data.filename}`, 'success');
                // Opcionalmente descargar el backup
                if (data.download_url) {
                    window.open(data.download_url, '_blank');
                }
            } else {
                App.showNotification('Error generando backup', 'error');
            }
        })
        .catch(error => {
            App.showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            App.setLoading(event.target, false);
        });
}

// Toggle modo mantenimiento
function toggleMaintenanceMode() {
    const isMaintenanceMode = document.body.dataset.maintenanceMode === 'true';
    const action = isMaintenanceMode ? 'desactivar' : 'activar';
    
    if (!confirm(`¿Estás seguro de ${action} el modo mantenimiento?`)) return;
    
    App.setLoading(event.target, true);
    
    fetch('/super-admin/api/maintenance-mode', { 
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ enabled: !isMaintenanceMode })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.body.dataset.maintenanceMode = data.maintenance_mode;
                const button = event.target;
                if (data.maintenance_mode) {
                    button.textContent = '✅ Desactivar Mantenimiento';
                    button.classList.remove('btn-warning');
                    button.classList.add('btn-success');
                    App.showNotification('Modo mantenimiento activado', 'warning');
                } else {
                    button.textContent = '🚧 Modo Mantenimiento';
                    button.classList.remove('btn-success');
                    button.classList.add('btn-warning');
                    App.showNotification('Modo mantenimiento desactivado', 'success');
                }
            } else {
                App.showNotification('Error cambiando modo mantenimiento', 'error');
            }
        })
        .catch(error => {
            App.showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            App.setLoading(event.target, false);
        });
}

// Generar reporte completo del sistema
function generateSystemReport() {
    App.setLoading(event.target, true);
    
    fetch('/super-admin/api/system-report', { method: 'POST' })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `system_report_${new Date().toISOString().split('T')[0]}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            
            App.showNotification('Reporte del sistema generado', 'success');
        })
        .catch(error => {
            App.showNotification('Error generando reporte', 'error');
        })
        .finally(() => {
            App.setLoading(event.target, false);
        });
}

// Inicializar dashboard
initAutoRefresh();
updateSystemMetrics();

// Shortcuts de teclado para super admin
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'u': // Ctrl+U = Gestionar usuarios
                e.preventDefault();
                window.location.href = '/super-admin/users';
                break;
            case 'a': // Ctrl+A = Gestionar áreas
                e.preventDefault();
                window.location.href = '/super-admin/areas';
                break;
            case 'm': // Ctrl+M = Modo mantenimiento
                e.preventDefault();
                toggleMaintenanceMode();
                break;
            case 'b': // Ctrl+B = Backup
                e.preventDefault();
                generateBackup();
                break;
        }
    }
    
    // F5 para refresh manual
    if (e.key === 'F5') {
        e.preventDefault();
        updateSystemMetrics();
        updateActivityFeed();
        App.showNotification('Dashboard actualizado', 'info');
    }
});

// Alertas críticas del sistema
function checkCriticalAlerts() {
    fetch('/super-admin/api/critical-alerts')
        .then(response => response.json())
        .then(data => {
            if (data.alerts && data.alerts.length > 0) {
                data.alerts.forEach(alert => {
                    App.showNotification(alert.message, alert.level, 10000);
                });
            }
        })
        .catch(error => console.log('Error verificando alertas:', error));
}

// Verificar alertas críticas cada 30 segundos
setInterval(checkCriticalAlerts, 30000);
checkCriticalAlerts(); // Verificar al cargar

// Prevenir cierre accidental en modo mantenimiento
window.addEventListener('beforeunload', function(e) {
    if (document.body.dataset.maintenanceMode === 'true') {
        e.preventDefault();
        e.returnValue = 'El sistema está en modo mantenimiento. ¿Estás seguro de salir?';
    }
});

// Logs en consola para debugging
console.log('=== DASHBOARD SUPER ADMIN ===');
console.log('Auto-refresh:', autoRefreshEnabled);
console.log('Usuario:', <?= json_encode($user) ?>);
console.log('Stats:', <?= json_encode($stats) ?>);
console.log('');
console.log('Shortcuts disponibles:');
console.log('Ctrl+U: Gestionar usuarios');
console.log('Ctrl+A: Gestionar áreas');
console.log('Ctrl+M: Toggle modo mantenimiento');
console.log('Ctrl+B: Generar backup');
console.log('F5: Actualizar dashboard');
console.log('=========================');
<?php $this->endSection(); ?>