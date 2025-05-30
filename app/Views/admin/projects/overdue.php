<?php
// app/Views/admin/projects/overdue.php
$this->extend('layouts/main');
$this->section('content');
?>

<div class="container">
    <!-- Header -->
    <section class="flex items-center justify-between mb-2xl">
        <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-sm">⚠️ Proyectos Vencidos</h1>
            <p class="text-gray-500">Proyectos que han superado su fecha límite de revisión</p>
        </div>
        <div class="flex items-center gap-md">
            <button onclick="notifyOverdueProjects()" class="btn btn-warning">
                📧 Notificar Solicitantes
            </button>
            <a href="/admin/dashboard" class="btn btn-outline">
                ← Volver al Dashboard
            </a>
        </div>
    </section>

    <!-- Alert -->
    <section class="card mb-2xl" style="background: #fef2f2; border-left: 4px solid #ef4444;">
        <div class="flex items-center gap-md">
            <div class="text-2xl">🚨</div>
            <div>
                <h3 class="font-semibold text-red-800">Atención: Proyectos Vencidos</h3>
                <p class="text-red-600">
                    Estos proyectos requieren acción inmediata para cumplir con los SLA establecidos.
                </p>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="grid grid-cols-4 gap-lg mb-2xl">
        <div class="stat-card" style="border-left: 4px solid #ef4444;">
            <div class="stat-icon">⚠️</div>
            <div class="stat-number"><?= count($overdue_projects ?? []) ?></div>
            <div class="stat-label">Total Vencidos</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #f59e0b;">
            <div class="stat-icon">🔥</div>
            <div class="stat-number">
                <?= count(array_filter($overdue_projects ?? [], function($p) { return ($p['days_overdue'] ?? 0) > 5; })) ?>
            </div>
            <div class="stat-label">Críticos (+5 días)</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-icon">📅</div>
            <div class="stat-number">
                <?= !empty($overdue_projects) ? max(array_column($overdue_projects, 'days_overdue')) : 0 ?>
            </div>
            <div class="stat-label">Máximo Días Vencido</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-icon">🎯</div>
            <div class="stat-number">85%</div>
            <div class="stat-label">SLA Objetivo</div>
        </div>
    </section>

    <!-- Overdue Projects List -->
    <?php if (!empty($overdue_projects)): ?>
    <section class="space-y-lg">
        <?php foreach ($overdue_projects as $project): ?>
            <div class="card project-card-overdue" data-project-id="<?= $project['id'] ?>" 
                 data-days-overdue="<?= $project['days_overdue'] ?>">
                
                <!-- Urgency Banner -->
                <?php if ($project['days_overdue'] > 5): ?>
                    <div class="bg-red-600 text-white px-lg py-sm rounded-t-lg">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">🚨 CRÍTICO - <?= $project['days_overdue'] ?> días vencido</span>
                            <span class="text-xs">Requiere escalamiento</span>
                        </div>
                    </div>
                <?php elseif ($project['days_overdue'] > 2): ?>
                    <div class="bg-orange-500 text-white px-lg py-sm rounded-t-lg">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">⚠️ URGENTE - <?= $project['days_overdue'] ?> días vencido</span>
                            <span class="text-xs">Acción inmediata requerida</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-yellow-500 text-white px-lg py-sm rounded-t-lg">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">⏰ VENCIDO - <?= $project['days_overdue'] ?> días</span>
                            <span class="text-xs">Revisar pronto</span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Project Content -->
                <div class="p-lg">
                    <div class="flex items-start justify-between mb-lg">
                        <div class="flex-1">
                            <div class="flex items-center gap-md mb-sm">
                                <h2 class="text-xl font-semibold text-primary">
                                    <?= esc($project['code']) ?>
                                </h2>
                                <span class="priority-badge priority-<?= $project['priority'] ?>">
                                    <?= getPriorityIcon($project['priority']) ?>
                                    <?= ucfirst($project['priority']) ?>
                                </span>
                                <span class="inline-block px-sm py-xs bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                    Vencido hace <?= $project['days_overdue'] ?> día(s)
                                </span>
                            </div>
                            
                            <h3 class="text-lg font-medium text-gray-800 mb-sm">
                                <?= esc($project['title']) ?>
                            </h3>
                            
                            <div class="grid grid-cols-4 gap-lg text-sm text-gray-600 mb-md">
                                <div>
                                    <span class="font-medium">👤 Solicitante:</span><br>
                                    <?= esc($project['requester_name']) ?>
                                </div>
                                <div>
                                    <span class="font-medium">📅 Creado:</span><br>
                                    <?= date('d/m/Y', strtotime($project['created_at'])) ?>
                                </div>
                                <div>
                                    <span class="font-medium">⏰ Debía completarse:</span><br>
                                    <span class="text-red-600 font-semibold">
                                        <?= date('d/m/Y', strtotime($project['due_date'])) ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="font-medium">📊 Impacto SLA:</span><br>
                                    <span class="text-red-600">
                                        -<?= round($project['days_overdue'] * 2, 1) ?>%
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Escalation Info -->
                        <div class="text-right ml-lg">
                            <?php if ($project['days_overdue'] > 5): ?>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-md">
                                    <div class="text-red-800 font-semibold text-sm mb-xs">
                                        🚨 Escalamiento Requerido
                                    </div>
                                    <div class="text-red-600 text-xs">
                                        Notificar a supervisor
                                    </div>
                                </div>
                            <?php elseif ($project['days_overdue'] > 2): ?>
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-md">
                                    <div class="text-orange-800 font-semibold text-sm mb-xs">
                                        ⚠️ Acción Inmediata
                                    </div>
                                    <div class="text-orange-600 text-xs">
                                        Revisar hoy
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Timeline -->
                    <div class="bg-gray-50 rounded-lg p-md mb-lg">
                        <h4 class="font-medium text-gray-800 mb-sm">📈 Línea de Tiempo</h4>
                        <div class="flex items-center gap-md text-xs">
                            <span class="text-gray-600">
                                Creado: <?= date('d/m', strtotime($project['created_at'])) ?>
                            </span>
                            <span class="text-gray-400">→</span>
                            <span class="text-red-600 font-semibold">
                                Vencimiento: <?= date('d/m', strtotime($project['due_date'])) ?>
                            </span>
                            <span class="text-gray-400">→</span>
                            <span class="text-red-700 font-bold">
                                Hoy: <?= date('d/m') ?> (+<?= $project['days_overdue'] ?> días)
                            </span>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-sm">
                            <button onclick="priorityReview(<?= $project['id'] ?>)" 
                                    class="btn btn-sm btn-warning">
                                🚀 Revisión Prioritaria
                            </button>
                            <button onclick="requestExtension(<?= $project['id'] ?>)" 
                                    class="btn btn-sm btn-secondary">
                                📅 Solicitar Extensión
                            </button>
                            <button onclick="escalateProject(<?= $project['id'] ?>)" 
                                    class="btn btn-sm btn-error">
                                📢 Escalar
                            </button>
                        </div>
                        <div class="flex items-center gap-xs">
                            <a href="/admin/projects/<?= $project['id'] ?>/review" 
                               class="btn btn-sm btn-primary">
                                👁️ Revisar Ahora
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
    
    <?php else: ?>
    <!-- No Overdue Projects -->
    <section class="card">
        <div class="empty-state py-2xl">
            <div class="empty-state-icon text-4xl">✅</div>
            <div class="empty-state-text">¡No hay proyectos vencidos!</div>
            <div class="empty-state-subtext mb-lg">
                Excelente trabajo manteniendo todos los proyectos al día
            </div>
            <a href="/admin/dashboard" class="btn btn-primary">
                Volver al Dashboard
            </a>
        </div>
    </section>
    <?php endif; ?>

    <!-- SLA Performance Summary -->
    <section class="card mt-xl">
        <h3 class="text-lg font-semibold mb-lg">📊 Resumen de Rendimiento SLA</h3>
        <div class="grid grid-cols-3 gap-lg">
            <div class="text-center p-lg bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600 mb-sm">85%</div>
                <div class="text-green-800 font-medium">SLA Cumplido</div>
                <div class="text-green-600 text-sm">Objetivo: 90%</div>
            </div>
            <div class="text-center p-lg bg-red-50 rounded-lg">
                <div class="text-2xl font-bold text-red-600 mb-sm"><?= count($overdue_projects ?? []) ?></div>
                <div class="text-red-800 font-medium">Proyectos Vencidos</div>
                <div class="text-red-600 text-sm">Meta: 0</div>
            </div>
            <div class="text-center p-lg bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600 mb-sm">
                    <?= !empty($overdue_projects) ? round(array_sum(array_column($overdue_projects, 'days_overdue')) / count($overdue_projects), 1) : 0 ?>
                </div>
                <div class="text-blue-800 font-medium">Días Promedio Vencido</div>
                <div class="text-blue-600 text-sm">Reducir a &lt; 1 día</div>
            </div>
        </div>
    </section>
</div>

<?php
function getPriorityIcon($priority) {
    $icons = [
        'low' => '⬇️',
        'medium' => '➡️',
        'high' => '⬆️',
        'critical' => '🔥'
    ];
    return $icons[$priority] ?? '➡️';
}
?>

<style>
.project-card-overdue {
    transition: all 0.3s ease;
    overflow: hidden;
}

.project-card-overdue:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
}

.priority-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.priority-low { background: var(--gray-100); color: var(--gray-700); }
.priority-medium { background: var(--primary-100); color: var(--primary-700); }
.priority-high { background: var(--warning-100); color: var(--warning-700); }
.priority-critical { background: var(--error-100); color: var(--error-700); }
</style>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
// JavaScript para proyectos vencidos
console.log('Admin Proyectos Vencidos cargado');

// Revisión prioritaria
function priorityReview(projectId) {
    if (confirm('¿Iniciar revisión prioritaria para este proyecto?')) {
        window.location.href = `/admin/projects/${projectId}/review?priority=true`;
    }
}

// Solicitar extensión
function requestExtension(projectId) {
    const days = prompt('¿Cuántos días adicionales solicitas?', '3');
    if (days && !isNaN(days) && parseInt(days) > 0) {
        App.setLoading(event.target, true);
        
        fetch(`/admin/projects/${projectId}/request-extension`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                extension_days: parseInt(days),
                reason: 'Proyecto vencido - extensión solicitada'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showNotification(`Extensión de ${days} días solicitada`, 'success');
            } else {
                App.showNotification('Error al solicitar extensión', 'error');
            }
        })
        .catch(error => {
            App.showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            App.setLoading(event.target, false);
        });
    }
}

// Escalar proyecto
function escalateProject(projectId) {
    if (confirm('¿Escalar este proyecto al supervisor? Se enviará una notificación automática.')) {
        App.setLoading(event.target, true);
        
        fetch(`/admin/projects/${projectId}/escalate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                escalation_reason: 'Proyecto vencido - escalamiento automático',
                notify_supervisor: true
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showNotification('Proyecto escalado al supervisor', 'warning');
            } else {
                App.showNotification('Error al escalar proyecto', 'error');
            }
        })
        .catch(error => {
            App.showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            App.setLoading(event.target, false);
        });
    }
}

// Notificar solicitantes de proyectos vencidos
function notifyOverdueProjects() {
    if (confirm('¿Enviar notificaciones por email a todos los solicitantes de proyectos vencidos?')) {
        App.setLoading(event.target, true);
        
        fetch('/admin/projects/notify-overdue', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showNotification(`Notificaciones enviadas a ${data.count} solicitantes`, 'success');
            } else {
                App.showNotification('Error al enviar notificaciones', 'error');
            }
        })
        .catch(error => {
            App.showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            App.setLoading(event.target, false);
        });
    }
}

// Ordenar por días vencidos
const projectCards = document.querySelectorAll('.project-card-overdue');
Array.from(projectCards).sort((a, b) => {
    const daysA = parseInt(a.dataset.daysOverdue);
    const daysB = parseInt(b.dataset.daysOverdue);
    return daysB - daysA; // Orden descendente (más vencidos primero)
}).forEach(card => {
    card.parentNode.appendChild(card);
});

// Highlight proyectos críticos
projectCards.forEach(card => {
    const daysOverdue = parseInt(card.dataset.daysOverdue);
    if (daysOverdue > 5) {
        card.style.borderLeft = '4px solid #ef4444';
        card.style.background = 'linear-gradient(90deg, #fef2f2 0%, #ffffff 10%)';
    } else if (daysOverdue > 2) {
        card.style.borderLeft = '4px solid #f59e0b';
        card.style.background = 'linear-gradient(90deg, #fffbeb 0%, #ffffff 10%)';
    }
});

// Auto-refresh cada 5 minutos para proyectos vencidos
setInterval(() => {
    console.log('Verificando nuevos proyectos vencidos...');
    // En una implementación real, hacer una llamada AJAX para verificar cambios
}, 300000);

console.log('Vista de proyectos vencidos inicializada');
<?php $this->endSection(); ?>