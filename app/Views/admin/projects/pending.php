<?php
// app/Views/admin/projects/pending.php
$this->extend('layouts/main');
$this->section('content');
?>

<div class="container">
    <!-- Header -->
    <section class="flex items-center justify-between mb-2xl">
        <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-sm">📋 Proyectos Pendientes</h1>
            <p class="text-gray-500">Revisar y gestionar proyectos asignados a tu área</p>
        </div>
        <div class="flex items-center gap-md">
            <button onclick="App.openModal('bulk-action-modal')" class="btn btn-secondary">
                ⚡ Acciones Masivas
            </button>
            <a href="/admin/dashboard" class="btn btn-outline">
                ← Volver al Dashboard
            </a>
        </div>
    </section>

    <!-- Stats Cards -->
    <section class="grid grid-cols-4 gap-lg mb-2xl">
        <div class="stat-card" style="border-left: 4px solid #f59e0b;">
            <div class="stat-icon">⏳</div>
            <div class="stat-number"><?= count($pending_projects ?? []) ?></div>
            <div class="stat-label">Pendientes Total</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #ef4444;">
            <div class="stat-icon">🔥</div>
            <div class="stat-number">
                <?= count(array_filter($pending_projects ?? [], function($p) { return $p['priority'] === 'high' || $p['priority'] === 'critical'; })) ?>
            </div>
            <div class="stat-label">Alta Prioridad</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-icon">📅</div>
            <div class="stat-number">
                <?= count(array_filter($pending_projects ?? [], function($p) { return isset($p['due_date']) && strtotime($p['due_date']) < strtotime('+3 days'); })) ?>
            </div>
            <div class="stat-label">Vencen Pronto</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-icon">⚡</div>
            <div class="stat-number">Normal</div>
            <div class="stat-label">Carga de Trabajo</div>
        </div>
    </section>

    <!-- Filters -->
    <section class="card mb-xl">
        <div class="flex items-center justify-between gap-lg">
            <div class="flex items-center gap-md">
                <div class="form-group mb-0">
                    <input type="text" id="search-projects" class="form-input" 
                           placeholder="Buscar proyectos..." 
                           value="<?= esc($filters['search'] ?? '') ?>"
                           style="min-width: 300px;">
                </div>
                <div class="form-group mb-0">
                    <select id="filter-priority" class="form-select">
                        <option value="">Todas las prioridades</option>
                        <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Baja</option>
                        <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Media</option>
                        <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Alta</option>
                        <option value="critical" <?= ($filters['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>Crítica</option>
                    </select>
                </div>
                <?php if (!empty($user_areas)): ?>
                <div class="form-group mb-0">
                    <select id="filter-area" class="form-select">
                        <option value="">Todas mis áreas</option>
                        <?php foreach ($user_areas as $area): ?>
                            <option value="<?= $area['area_id'] ?>" 
                                    <?= ($filters['area_id'] ?? '') == $area['area_id'] ? 'selected' : '' ?>>
                                <?= esc($area['area_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-sm">
                <button id="apply-filters" class="btn btn-primary">🔍 Filtrar</button>
                <button id="clear-filters" class="btn btn-secondary">🗑️ Limpiar</button>
                <button onclick="refreshProjects()" class="btn btn-outline">🔄</button>
            </div>
        </div>
    </section>

    <!-- Projects List -->
    <?php if (!empty($pending_projects)): ?>
    <section class="space-y-lg">
        <?php foreach ($pending_projects as $project): ?>
            <div class="card project-card" data-project-id="<?= $project['id'] ?>" 
                 data-priority="<?= $project['priority'] ?>">
                
                <!-- Project Header -->
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
                            <?php if (isset($project['due_date']) && strtotime($project['due_date']) < time()): ?>
                                <span class="inline-block px-sm py-xs bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                    ⚠️ Vencido
                                </span>
                            <?php elseif (isset($project['due_date']) && strtotime($project['due_date']) < strtotime('+3 days')): ?>
                                <span class="inline-block px-sm py-xs bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
                                    ⏰ Vence Pronto
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="text-lg font-medium text-gray-800 mb-sm">
                            <?= esc($project['title']) ?>
                        </h3>
                        
                        <div class="grid grid-cols-3 gap-lg text-sm text-gray-600">
                            <div>
                                <span class="font-medium">👤 Solicitante:</span><br>
                                <?= esc($project['requester_name']) ?>
                            </div>
                            <div>
                                <span class="font-medium">📅 Creado:</span><br>
                                <?= date('d/m/Y H:i', strtotime($project['created_at'])) ?>
                            </div>
                            <?php if (isset($project['due_date'])): ?>
                            <div>
                                <span class="font-medium">⏰ Vencimiento:</span><br>
                                <span class="<?= strtotime($project['due_date']) < time() ? 'text-red-600 font-semibold' : '' ?>">
                                    <?= date('d/m/Y', strtotime($project['due_date'])) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="flex items-center gap-sm">
                        <button onclick="quickApprove(<?= $project['id'] ?>)" 
                                class="btn btn-sm btn-success" 
                                data-tooltip="Aprobación rápida">
                            ✅
                        </button>
                        <button onclick="quickReject(<?= $project['id'] ?>)" 
                                class="btn btn-sm btn-error" 
                                data-tooltip="Rechazo rápido">
                            ❌
                        </button>
                        <a href="/admin/projects/<?= $project['id'] ?>/review" 
                           class="btn btn-sm btn-primary" 
                           data-tooltip="Revisar completo">
                            👁️ Revisar
                        </a>
                    </div>
                </div>
                
                <!-- Project Progress (if available) -->
                <?php if (isset($project['completion_percentage'])): ?>
                <div class="mb-md">
                    <div class="flex justify-between text-sm mb-xs">
                        <span class="text-gray-600">Progreso General</span>
                        <span class="font-semibold"><?= number_format($project['completion_percentage'], 0) ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-primary-500 h-2 rounded-full transition-all duration-300"
                             style="width: <?= $project['completion_percentage'] ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Time Tracking -->
                <div class="flex items-center justify-between text-xs text-gray-500 pt-md border-t border-gray-100">
                    <span>
                        Asignado hace <?= timeAgo($project['created_at']) ?>
                    </span>
                    <?php if (isset($project['due_date'])): ?>
                        <?php 
                        $daysLeft = ceil((strtotime($project['due_date']) - time()) / 86400);
                        ?>
                        <span class="<?= $daysLeft < 0 ? 'text-red-600' : ($daysLeft <= 3 ? 'text-yellow-600' : '') ?>">
                            <?php if ($daysLeft < 0): ?>
                                Vencido hace <?= abs($daysLeft) ?> día(s)
                            <?php elseif ($daysLeft == 0): ?>
                                Vence hoy
                            <?php else: ?>
                                <?= $daysLeft ?> día(s) restantes
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
    
    <?php else: ?>
    <!-- Empty State -->
    <section class="card">
        <div class="empty-state py-2xl">
            <div class="empty-state-icon text-4xl">✅</div>
            <div class="empty-state-text">¡No hay proyectos pendientes!</div>
            <div class="empty-state-subtext mb-lg">
                Todos los proyectos de tu área están al día
            </div>
            <a href="/admin/dashboard" class="btn btn-primary">
                Volver al Dashboard
            </a>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- Modal de Acciones Masivas -->
<div id="bulk-action-modal" class="modal" style="display: none;">
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>⚡ Acciones Masivas</h3>
                <button onclick="App.closeModal('bulk-action-modal')">&times;</button>
            </div>
            <div class="modal-body">
                <p>Selecciona una acción para aplicar a múltiples proyectos:</p>
                <div class="space-y-md mt-lg">
                    <button class="btn btn-success w-full" onclick="bulkApprove()">
                        ✅ Aprobar Seleccionados
                    </button>
                    <button class="btn btn-warning w-full" onclick="bulkAssign()">
                        👥 Asignar a Revisor
                    </button>
                    <button class="btn btn-secondary w-full" onclick="bulkExport()">
                        📊 Exportar Lista
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Funciones auxiliares
function getPriorityIcon($priority) {
    $icons = [
        'low' => '⬇️',
        'medium' => '➡️',
        'high' => '⬆️',
        'critical' => '🔥'
    ];
    return $icons[$priority] ?? '➡️';
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 3600) return floor($time/60) . ' minutos';
    if ($time < 86400) return floor($time/3600) . ' horas';
    if ($time < 2592000) return floor($time/86400) . ' días';
    
    return date('d/m/Y', strtotime($datetime));
}
?>

<style>
.project-card {
    transition: all 0.3s ease;
    border-left: 4px solid var(--gray-300);
}

.project-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
}

.project-card[data-priority="high"] {
    border-left-color: var(--warning-500);
}

.project-card[data-priority="critical"] {
    border-left-color: var(--error-500);
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

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: var(--radius-xl);
    padding: var(--spacing-xl);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.modal-header button {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
// JavaScript para proyectos pendientes
console.log('Admin Proyectos Pendientes cargado');

// Filtros en tiempo real
const searchInput = document.getElementById('search-projects');
const priorityFilter = document.getElementById('filter-priority');
const areaFilter = document.getElementById('filter-area');
const projectCards = document.querySelectorAll('.project-card');

function applyFilters() {
    const searchTerm = searchInput.value.toLowerCase();
    const priority = priorityFilter.value;
    const area = areaFilter ? areaFilter.value : '';
    
    let visibleCount = 0;
    
    projectCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        const cardPriority = card.dataset.priority;
        
        const matchesSearch = !searchTerm || text.includes(searchTerm);
        const matchesPriority = !priority || cardPriority === priority;
        // Note: area filtering would need additional data attributes
        
        if (matchesSearch && matchesPriority) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    console.log(`Mostrando ${visibleCount} proyectos`);
}

// Event listeners
searchInput.addEventListener('input', App.utils.debounce(applyFilters, 300));
priorityFilter.addEventListener('change', applyFilters);
if (areaFilter) areaFilter.addEventListener('change', applyFilters);

document.getElementById('apply-filters').addEventListener('click', applyFilters);
document.getElementById('clear-filters').addEventListener('click', () => {
    searchInput.value = '';
    priorityFilter.value = '';
    if (areaFilter) areaFilter.value = '';
    applyFilters();
});

// Acciones rápidas
function quickApprove(projectId) {
    if (confirm('¿Aprobar este proyecto?')) {
        App.setLoading(event.target, true);
        
        fetch(`/admin/projects/${projectId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'approve',
                comments: 'Aprobación rápida'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showNotification('Proyecto aprobado exitosamente', 'success');
                // Remover el proyecto de la lista
                document.querySelector(`[data-project-id="${projectId}"]`).remove();
            } else {
                App.showNotification(data.message || 'Error al aprobar proyecto', 'error');
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

function quickReject(projectId) {
    const reason = prompt('Motivo del rechazo:');
    if (reason) {
        App.setLoading(event.target, true);
        
        fetch(`/admin/projects/${projectId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'reject',
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showNotification('Proyecto rechazado', 'warning');
                document.querySelector(`[data-project-id="${projectId}"]`).remove();
            } else {
                App.showNotification(data.message || 'Error al rechazar proyecto', 'error');
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

function refreshProjects() {
    window.location.reload();
}

// Acciones masivas
function bulkApprove() {
    App.showNotification('Función en desarrollo', 'info');
    App.closeModal('bulk-action-modal');
}

function bulkAssign() {
    App.showNotification('Función en desarrollo', 'info');
    App.closeModal('bulk-action-modal');
}

function bulkExport() {
    App.showNotification('Exportando lista...', 'info');
    App.closeModal('bulk-action-modal');
}

// Auto-refresh cada 2 minutos
setInterval(() => {
    console.log('Auto-actualizando proyectos pendientes...');
    // Podrías hacer una llamada AJAX aquí para actualizar solo los datos
}, 120000);
<?php $this->endSection(); ?>