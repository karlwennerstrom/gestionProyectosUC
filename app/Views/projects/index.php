<?php
// app/Views/projects/index.php
$this->extend('layouts/main');
$this->section('content');
?>

<div class="container">
    <!-- Header -->
    <section class="flex items-center justify-between mb-2xl">
        <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-sm">Mis Proyectos</h1>
            <p class="text-gray-500">Gestiona todos tus proyectos en un solo lugar</p>
        </div>
        <a href="/projects/create" class="btn btn-primary btn-lg">
            ➕ Crear Proyecto
        </a>
    </section>

    <!-- Stats Cards -->
    <section class="grid grid-cols-4 gap-lg mb-2xl">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
            <div class="stat-label">Total de Proyectos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⚡</div>
            <div class="stat-number"><?= $stats['active'] ?? 0 ?></div>
            <div class="stat-label">Proyectos Activos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-number"><?= $stats['completed'] ?? 0 ?></div>
            <div class="stat-label">Completados</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-number"><?= $stats['pending'] ?? 0 ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
    </section>

    <!-- Filters and Search -->
    <section class="card mb-xl">
        <div class="flex items-center justify-between gap-lg">
            <div class="flex items-center gap-md">
                <div class="form-group mb-0">
                    <input type="text" id="search-projects" class="form-input" 
                           placeholder="Buscar proyectos..." style="min-width: 300px;">
                </div>
                <div class="form-group mb-0">
                    <select id="filter-status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="draft">Borrador</option>
                        <option value="submitted">Enviado</option>
                        <option value="in_progress">En Progreso</option>
                        <option value="on_hold">En Pausa</option>
                        <option value="completed">Completado</option>
                        <option value="rejected">Rechazado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <select id="filter-priority" class="form-select">
                        <option value="">Todas las prioridades</option>
                        <option value="low">Baja</option>
                        <option value="medium">Media</option>
                        <option value="high">Alta</option>
                        <option value="critical">Crítica</option>
                    </select>
                </div>
            </div>
            <button id="clear-filters" class="btn btn-secondary">
                🗑️ Limpiar Filtros
            </button>
        </div>
    </section>

    <!-- Projects Table -->
    <?php if (!empty($projects)): ?>
    <section class="card">
        <div class="table-container">
            <table class="table data-table" id="projects-table">
                <thead>
                    <tr>
                        <th data-sortable>Código</th>
                        <th data-sortable>Título</th>
                        <th data-sortable>Estado</th>
                        <th data-sortable>Prioridad</th>
                        <th data-sortable>Área Actual</th>
                        <th data-sortable>Progreso</th>
                        <th data-sortable>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <tr data-project-id="<?= $project['id'] ?>" 
                        data-status="<?= $project['status'] ?>"
                        data-priority="<?= $project['priority'] ?>">
                        
                        <!-- Código -->
                        <td class="font-medium">
                            <a href="/projects/<?= $project['id'] ?>" 
                               class="text-primary hover:underline">
                                <?= esc($project['code']) ?>
                            </a>
                        </td>
                        
                        <!-- Título -->
                        <td>
                            <div class="font-medium"><?= esc($project['title']) ?></div>
                            <div class="text-sm text-gray-500">
                                <?= esc(substr($project['description'], 0, 60)) ?>...
                            </div>
                        </td>
                        
                        <!-- Estado -->
                        <td>
                            <?php
                            $statusColors = [
                                'draft' => 'bg-gray-100 text-gray-700',
                                'submitted' => 'bg-blue-100 text-blue-700',
                                'in_progress' => 'bg-yellow-100 text-yellow-700',
                                'on_hold' => 'bg-orange-100 text-orange-700',
                                'completed' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                'cancelled' => 'bg-gray-100 text-gray-700'
                            ];
                            
                            $statusLabels = [
                                'draft' => 'Borrador',
                                'submitted' => 'Enviado',
                                'in_progress' => 'En Progreso',
                                'on_hold' => 'En Pausa',
                                'completed' => 'Completado',
                                'rejected' => 'Rechazado',
                                'cancelled' => 'Cancelado'
                            ];
                            
                            $statusClass = $statusColors[$project['status']] ?? 'bg-gray-100 text-gray-700';
                            $statusLabel = $statusLabels[$project['status']] ?? ucfirst($project['status']);
                            ?>
                            <span class="inline-block px-sm py-xs rounded-full text-xs font-medium <?= $statusClass ?>">
                                <?= $statusLabel ?>
                            </span>
                        </td>
                        
                        <!-- Prioridad -->
                        <td>
                            <?php
                            $priorityColors = [
                                'low' => 'text-gray-500',
                                'medium' => 'text-blue-500',
                                'high' => 'text-orange-500',
                                'critical' => 'text-red-500'
                            ];
                            
                            $priorityIcons = [
                                'low' => '⬇️',
                                'medium' => '➡️',
                                'high' => '⬆️',
                                'critical' => '🔥'
                            ];
                            
                            $priorityClass = $priorityColors[$project['priority']] ?? 'text-gray-500';
                            $priorityIcon = $priorityIcons[$project['priority']] ?? '➡️';
                            ?>
                            <span class="flex items-center gap-xs <?= $priorityClass ?>">
                                <?= $priorityIcon ?>
                                <?= ucfirst($project['priority']) ?>
                            </span>
                        </td>
                        
                        <!-- Área Actual -->
                        <td>
                            <?php if (!empty($project['current_area_name'])): ?>
                                <span class="inline-block px-sm py-xs rounded text-xs font-medium"
                                      style="background: <?= $project['current_area_color'] ?? '#e5e7eb' ?>20; 
                                             color: <?= $project['current_area_color'] ?? '#6b7280' ?>;">
                                    <?= esc($project['current_area_name']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400">Finalizado</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Progreso -->
                        <td>
                            <div class="flex items-center gap-sm">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary-500 h-2 rounded-full transition-all duration-300"
                                         style="width: <?= $project['completion_percentage'] ?? 0 ?>%"></div>
                                </div>
                                <span class="text-sm text-gray-600 whitespace-nowrap">
                                    <?= number_format($project['completion_percentage'] ?? 0, 0) ?>%
                                </span>
                            </div>
                        </td>
                        
                        <!-- Fecha Creación -->
                        <td class="text-sm text-gray-600">
                            <?= date('d/m/Y', strtotime($project['created_at'])) ?>
                        </td>
                        
                        <!-- Acciones -->
                        <td>
                            <div class="flex items-center gap-xs">
                                <a href="/projects/<?= $project['id'] ?>" 
                                   class="btn btn-sm btn-secondary" 
                                   data-tooltip="Ver detalles">
                                    👁️
                                </a>
                                
                                <?php if (in_array($project['status'], ['draft', 'rejected'])): ?>
                                <a href="/projects/<?= $project['id'] ?>/edit" 
                                   class="btn btn-sm btn-secondary"
                                   data-tooltip="Editar proyecto">
                                    ✏️
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($project['status'] === 'draft'): ?>
                                <form method="POST" action="/projects/<?= $project['id'] ?>/submit" 
                                      class="inline ajax-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-success"
                                            data-tooltip="Enviar para revisión">
                                        📤
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <?php if (in_array($project['status'], ['draft', 'rejected'])): ?>
                                <button class="btn btn-sm btn-error confirm-delete"
                                        data-url="/projects/<?= $project['id'] ?>/delete"
                                        data-confirm-message="¿Estás seguro de eliminar el proyecto '<?= esc($project['title']) ?>'?"
                                        data-tooltip="Eliminar proyecto">
                                    🗑️
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    
    <?php else: ?>
    <!-- Empty State -->
    <section class="card">
        <div class="empty-state">
            <div class="empty-state-icon">📁</div>
            <div class="empty-state-text">No tienes proyectos aún</div>
            <div class="empty-state-subtext mb-lg">
                Crea tu primer proyecto para comenzar a usar el sistema
            </div>
            <a href="/projects/create" class="btn btn-primary btn-lg">
                ➕ Crear Mi Primer Proyecto
            </a>
        </div>
    </section>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if (isset($pagination) && $pagination): ?>
    <section class="flex justify-center mt-xl">
        <div class="flex items-center gap-sm">
            <?php if ($pagination['current_page'] > 1): ?>
                <a href="?page=<?= $pagination['current_page'] - 1 ?>" 
                   class="btn btn-secondary">← Anterior</a>
            <?php endif; ?>
            
            <span class="text-gray-600 mx-md">
                Página <?= $pagination['current_page'] ?> de <?= $pagination['total_pages'] ?>
            </span>
            
            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?>" 
                   class="btn btn-secondary">Siguiente →</a>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
// JavaScript específico para la lista de proyectos
console.log('Lista de Proyectos cargada correctamente');

// Funcionalidad de filtros y búsqueda
const searchInput = document.getElementById('search-projects');
const statusFilter = document.getElementById('filter-status');
const priorityFilter = document.getElementById('filter-priority');
const clearFiltersBtn = document.getElementById('clear-filters');
const tableRows = document.querySelectorAll('#projects-table tbody tr');

// Función de filtrado
function filterProjects() {
    const searchTerm = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value;
    const priorityValue = priorityFilter.value;
    
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const status = row.dataset.status;
        const priority = row.dataset.priority;
        
        const matchesSearch = !searchTerm || text.includes(searchTerm);
        const matchesStatus = !statusValue || status === statusValue;
        const matchesPriority = !priorityValue || priority === priorityValue;
        
        if (matchesSearch && matchesStatus && matchesPriority) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Mostrar mensaje si no hay resultados
    updateEmptyState(visibleCount === 0);
}

// Actualizar estado vacío
function updateEmptyState(isEmpty) {
    let emptyMessage = document.querySelector('.no-results-message');
    
    if (isEmpty && !emptyMessage) {
        emptyMessage = document.createElement('tr');
        emptyMessage.className = 'no-results-message';
        emptyMessage.innerHTML = `
            <td colspan="8" class="text-center py-xl">
                <div class="text-gray-500">
                    <div class="text-2xl mb-md">🔍</div>
                    <div>No se encontraron proyectos que coincidan con los filtros</div>
                </div>
            </td>
        `;
        document.querySelector('#projects-table tbody').appendChild(emptyMessage);
    } else if (!isEmpty && emptyMessage) {
        emptyMessage.remove();
    }
}

// Event listeners para filtros
searchInput.addEventListener('input', App.utils.debounce(filterProjects, 300));
statusFilter.addEventListener('change', filterProjects);
priorityFilter.addEventListener('change', filterProjects);

// Limpiar filtros
clearFiltersBtn.addEventListener('click', () => {
    searchInput.value = '';
    statusFilter.value = '';
    priorityFilter.value = '';
    filterProjects();
});

// Confirmación de eliminación personalizada
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('confirm-delete')) {
        e.preventDefault();
        
        const url = e.target.dataset.url;
        const message = e.target.dataset.confirmMessage;
        
        if (confirm(message)) {
            // Crear y enviar formulario de eliminación
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            
            // Agregar token CSRF
            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = App.config.csrf_name;
            csrfField.value = App.config.csrf_token;
            form.appendChild(csrfField);
            
            // Agregar método DELETE
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
});

// Actualizar progreso en tiempo real
function updateProgress() {
    const progressBars = document.querySelectorAll('[style*="width:"]');
    progressBars.forEach(bar => {
        const width = parseFloat(bar.style.width);
        if (width > 0) {
            bar.style.background = `linear-gradient(90deg, 
                var(--primary-500) 0%, 
                var(--primary-600) ${width}%)`;
        }
    });
}

// Ejecutar al cargar la página
updateProgress();

// Auto-refresh cada 30 segundos para proyectos activos
if (document.querySelectorAll('[data-status="in_progress"]').length > 0) {
    setInterval(() => {
        console.log('Actualizando estado de proyectos...');
        // Aquí se podría hacer una llamada AJAX para actualizar el estado
    }, 30000);
}

// Tooltips para iconos de acción
document.querySelectorAll('[data-tooltip]').forEach(element => {
    element.addEventListener('mouseenter', function() {
        const tooltip = this.dataset.tooltip;
        this.title = tooltip;
    });
});
<?php $this->endSection(); ?>