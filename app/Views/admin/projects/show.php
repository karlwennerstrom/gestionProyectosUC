<?php
// app/Views/projects/show.php
$this->extend('layouts/main');
$this->section('content');
?>

<div class="container">
    <!-- Header -->
    <section class="mb-2xl">
        <div class="flex items-center justify-between mb-lg">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-sm">
                    📋 <?= esc($project['code']) ?>
                </h1>
                <p class="text-xl text-gray-600"><?= esc($project['title']) ?></p>
            </div>
            <div class="flex items-center gap-md">
                <a href="/projects" class="btn btn-secondary">
                    ← Volver a Mis Proyectos
                </a>
                <?php if (in_array($project['status'], ['draft', 'rejected'])): ?>
                    <a href="/projects/<?= $project['id'] ?>/edit" class="btn btn-primary">
                        ✏️ Editar Proyecto
                    </a>
                <?php endif; ?>
                <button onclick="App.openModal('project-actions-modal')" class="btn btn-outline">
                    ⚙️ Acciones
                </button>
            </div>
        </div>

        <!-- Status Card -->
        <div class="card <?= getProjectStatusStyle($project['status']) ?>">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-lg">
                    <div class="text-3xl"><?= getProjectStatusIcon($project['status']) ?></div>
                    <div>
                        <h2 class="text-xl font-semibold mb-sm">
                            Estado: <?= getProjectStatusLabel($project['status']) ?>
                        </h2>
                        <div class="flex items-center gap-lg text-sm">
                            <span>📅 Creado: <?= date('d/m/Y', strtotime($project['created_at'])) ?></span>
                            <?php if ($project['estimated_completion']): ?>
                                <span>🎯 Objetivo: <?= date('d/m/Y', strtotime($project['estimated_completion'])) ?></span>
                            <?php endif; ?>
                            <?php if ($project['current_area_name']): ?>
                                <span class="inline-flex items-center gap-xs">
                                    📍 Área actual: 
                                    <span class="px-sm py-xs rounded text-xs font-medium"
                                          style="background: <?= $project['current_area_color'] ?>20; color: <?= $project['current_area_color'] ?>;">
                                        <?= esc($project['current_area_name']) ?>
                                    </span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600 mb-xs">Progreso General</div>
                    <div class="flex items-center gap-sm">
                        <div class="w-32 bg-gray-200 rounded-full h-3">
                            <div class="bg-primary-500 h-3 rounded-full transition-all duration-500"
                                 style="width: <?= $project['completion_percentage'] ?>%"></div>
                        </div>
                        <span class="text-lg font-bold text-primary-600">
                            <?= number_format($project['completion_percentage'], 0) ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-3 gap-xl">
        
        <!-- Main Content - 2 columns -->
        <div class="col-span-2 space-y-xl">
            
            <!-- Project Details -->
            <section class="card">
                <h3 class="card-title">📋 Detalles del Proyecto</h3>
                
                <div class="space-y-lg">
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-sm">Descripción:</h4>
                        <p class="text-gray-600 leading-relaxed"><?= nl2br(esc($project['description'])) ?></p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-lg">
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-sm">Departamento:</h4>
                            <p class="text-gray-600"><?= esc($project['department']) ?></p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-sm">Prioridad:</h4>
                            <span class="priority-badge priority-<?= $project['priority'] ?>">
                                <?= getPriorityIcon($project['priority']) ?>
                                <?= ucfirst($project['priority']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-lg">
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-sm">Email de Contacto:</h4>
                            <p class="text-gray-600">
                                <a href="mailto:<?= esc($project['contact_email']) ?>" 
                                   class="text-primary hover:underline">
                                    <?= esc($project['contact_email']) ?>
                                </a>
                            </p>
                        </div>
                        <?php if ($project['contact_phone']): ?>
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-sm">Teléfono:</h4>
                            <p class="text-gray-600">
                                <a href="tel:<?= esc($project['contact_phone']) ?>" 
                                   class="text-primary hover:underline">
                                    <?= esc($project['contact_phone']) ?>
                                </a>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($project['budget']): ?>
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-sm">Presupuesto Estimado:</h4>
                        <p class="text-gray-600 text-lg font-medium">
                            $<?= number_format($project['budget'], 0, ',', '.') ?> CLP
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    $additionalInfo = json_decode($project['additional_info'] ?? '{}', true);
                    if (!empty($additionalInfo['special_requirements'])):
                    ?>
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-sm">Requisitos Especiales:</h4>
                        <p class="text-gray-600"><?= nl2br(esc($additionalInfo['special_requirements'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Project Phases -->
            <section class="card">
                <h3 class="card-title">🔄 Fases del Proyecto</h3>
                
                <div class="space-y-md">
                    <?php foreach ($phases as $phase): ?>
                        <div class="phase-item <?= getPhaseStatusClass($phase['status']) ?>">
                            <div class="flex items-center gap-md p-lg rounded-lg border">
                                
                                <div class="phase-indicator">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold"
                                         style="background: <?= $phase['area_color'] ?>;">
                                        <?= $phase['phase_order'] ?>
                                    </div>
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-sm">
                                        <h4 class="font-semibold text-gray-800"><?= esc($phase['area_name']) ?></h4>
                                        <span class="phase-status-badge <?= $phase['status'] ?>">
                                            <?= getPhaseStatusIcon($phase['status']) ?>
                                            <?= getPhaseStatusLabel($phase['status']) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-3 gap-md text-sm text-gray-600">
                                        <div>
                                            <span class="font-medium">Estimado:</span><br>
                                            <?= $phase['estimated_hours'] ?> horas
                                        </div>
                                        <?php if ($phase['started_at']): ?>
                                        <div>
                                            <span class="font-medium">Iniciado:</span><br>
                                            <?= date('d/m/Y', strtotime($phase['started_at'])) ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($phase['completed_at']): ?>
                                        <div>
                                            <span class="font-medium">Completado:</span><br>
                                            <?= date('d/m/Y', strtotime($phase['completed_at'])) ?>
                                            <?php if ($phase['actual_hours']): ?>
                                                <br><small>(<?= $phase['actual_hours'] ?>h reales)</small>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($phase['assigned_to_name']): ?>
                                    <div class="mt-sm">
                                        <span class="text-xs text-gray-500">
                                            👤 Asignado a: <strong><?= esc($phase['assigned_to_name']) ?></strong>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Documents -->
            <section class="card">
                <div class="flex items-center justify-between mb-lg">
                    <h3 class="card-title mb-0">📎 Documentos del Proyecto</h3>
                    <a href="/projects/<?= $project['id'] ?>/upload-document" class="btn btn-primary">
                        📤 Subir Documento
                    </a>
                </div>
                
                <?php if (!empty($documents)): ?>
                    <div class="space-y-sm">
                        <?php foreach ($documents as $doc): ?>
                            <div class="document-item border border-gray-200 rounded-lg p-md hover:border-primary-300 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-md">
                                        <div class="text-2xl">
                                            <?= getDocumentIcon($doc['document_type']) ?>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800 mb-xs">
                                                <?= esc($doc['original_name']) ?>
                                            </h4>
                                            <div class="flex items-center gap-md text-sm text-gray-500">
                                                <span><?= getDocumentTypeName($doc['document_type']) ?></span>
                                                <span>•</span>
                                                <span><?= formatFileSize($doc['file_size']) ?></span>
                                                <span>•</span>
                                                <span>v<?= $doc['version'] ?></span>
                                                <span>•</span>
                                                <span><?= date('d/m/Y H:i', strtotime($doc['uploaded_at'])) ?></span>
                                            </div>
                                            <?php if ($doc['reviewer_comments']): ?>
                                            <div class="mt-xs">
                                                <p class="text-xs text-gray-600">
                                                    💬 <?= esc($doc['reviewer_comments']) ?>
                                                </p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-sm">
                                        <span class="document-status-badge status-<?= $doc['status'] ?>">
                                            <?= getDocumentStatusIcon($doc['status']) ?>
                                            <?= ucfirst($doc['status']) ?>
                                        </span>
                                        <button onclick="downloadDocument(<?= $doc['id'] ?>)" 
                                                class="btn btn-sm btn-outline" 
                                                data-tooltip="Descargar">
                                            📥
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state py-lg">
                        <div class="empty-state-icon text-2xl">📄</div>
                        <div class="empty-state-text">No hay documentos subidos</div>
                        <div class="empty-state-subtext mb-md">
                            Sube los documentos requeridos para tu proyecto
                        </div>
                        <a href="/projects/<?= $project['id'] ?>/upload-document" class="btn btn-primary">
                            📤 Subir Primer Documento
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Activity Log -->
            <section class="card">
                <h3 class="card-title">📝 Historial de Actividad</h3>
                
                <div class="activity-timeline">
                    <?php foreach ($activity_log as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-indicator">
                                <span class="activity-icon">
                                    <?= getActivityIcon($activity['action']) ?>
                                </span>
                            </div>
                            <div class="activity-content">
                                <p class="activity-description">
                                    <strong><?= esc($activity['user_name']) ?></strong>
                                    <?= esc($activity['description']) ?>
                                </p>
                                <span class="activity-time">
                                    <?= timeAgo($activity['created_at']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <!-- Sidebar - 1 column -->
        <div class="space-y-xl">
            
            <!-- Quick Actions -->
            <section class="card">
                <h3 class="card-title">⚡ Acciones Rápidas</h3>
                
                <div class="space-y-sm">
                    <?php if ($project['status'] === 'draft'): ?>
                        <form method="POST" action="/projects/<?= $project['id'] ?>/submit" class="w-full">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success w-full">
                                🚀 Enviar para Revisión
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="/projects/<?= $project['id'] ?>/upload-document" class="btn btn-primary w-full">
                        📤 Subir Documento
                    </a>
                    
                    <?php if (in_array($project['status'], ['draft', 'rejected'])): ?>
                        <a href="/projects/<?= $project['id'] ?>/edit" class="btn btn-secondary w-full">
                            ✏️ Editar Proyecto
                        </a>
                    <?php endif; ?>
                    
                    <button onclick="shareProject()" class="btn btn-outline w-full">
                        📤 Compartir Proyecto
                    </button>
                    
                    <button onclick="exportProjectSummary()" class="btn btn-outline w-full">
                        📄 Exportar Resumen
                    </button>
                </div>
            </section>

            <!-- Project Timeline -->
            <section class="card">
                <h3 class="card-title">📅 Cronograma</h3>
                
                <div class="space-y-md text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Creado:</span>
                        <span class="font-medium"><?= date('d/m/Y', strtotime($project['created_at'])) ?></span>
                    </div>
                    
                    <?php if ($project['estimated_completion']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Fecha objetivo:</span>
                        <span class="font-medium"><?= date('d/m/Y', strtotime($project['estimated_completion'])) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($project['actual_completion']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Completado:</span>
                        <span class="font-medium text-green-600"><?= date('d/m/Y', strtotime($project['actual_completion'])) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tiempo transcurrido:</span>
                        <span class="font-medium"><?= getElapsedTime($project['created_at']) ?></span>
                    </div>
                    
                    <?php if ($project['estimated_completion'] && $project['status'] !== 'completed'): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tiempo restante:</span>
                        <span class="font-medium <?= getRemainingTimeClass($project['estimated_completion']) ?>">
                            <?= getRemainingTime($project['estimated_completion']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Project Statistics -->
            <section class="card">
                <h3 class="card-title">📊 Estadísticas</h3>
                
                <div class="space-y-md">
                    <div class="stat-item">
                        <div class="flex justify-between text-sm mb-xs">
                            <span class="text-gray-600">Documentos</span>
                            <span class="font-semibold"><?= count($documents) ?></span>
                        </div>
                        <div class="flex gap-xs">
                            <?php
                            $approved = count(array_filter($documents, fn($d) => $d['status'] === 'approved'));
                            $pending = count(array_filter($documents, fn($d) => $d['status'] === 'pending'));
                            $total = count($documents);
                            ?>
                            <div class="bg-green-200 h-2 rounded" style="width: <?= $total > 0 ? ($approved / $total) * 100 : 0 ?>%"></div>
                            <div class="bg-yellow-200 h-2 rounded" style="width: <?= $total > 0 ? ($pending / $total) * 100 : 0 ?>%"></div>
                            <div class="bg-gray-200 h-2 rounded flex-1"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-xs">
                            <span><?= $approved ?> aprobados</span>
                            <span><?= $pending ?> pendientes</span>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="flex justify-between text-sm mb-xs">
                            <span class="text-gray-600">Fases completadas</span>
                            <?php
                            $completedPhases = count(array_filter($phases, fn($p) => $p['status'] === 'completed'));
                            $totalPhases = count($phases);
                            ?>
                            <span class="font-semibold"><?= $completedPhases ?>/<?= $totalPhases ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full" 
                                 style="width: <?= $totalPhases > 0 ? ($completedPhases / $totalPhases) * 100 : 0 ?>%"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Info -->
            <section class="card">
                <h3 class="card-title">📞 Información de Contacto</h3>
                
                <div class="space-y-md text-sm">
                    <div>
                        <span class="text-gray-600">Solicitante:</span><br>
                        <span class="font-medium"><?= esc($user['full_name']) ?></span>
                    </div>
                    
                    <div>
                        <span class="text-gray-600">Email:</span><br>
                        <a href="mailto:<?= esc($project['contact_email']) ?>" 
                           class="text-primary hover:underline font-medium">
                            <?= esc($project['contact_email']) ?>
                        </a>
                    </div>
                    
                    <?php if ($project['contact_phone']): ?>
                    <div>
                        <span class="text-gray-600">Teléfono:</span><br>
                        <a href="tel:<?= esc($project['contact_phone']) ?>" 
                           class="text-primary hover:underline font-medium">
                            <?= esc($project['contact_phone']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <span class="text-gray-600">Departamento:</span><br>
                        <span class="font-medium"><?= esc($project['department']) ?></span>
                    </div>
                </div>
            </section>

            <!-- Help & Support -->
            <section class="card">
                <h3 class="card-title">❓ ¿Necesitas Ayuda?</h3>
                
                <div class="space-y-sm text-sm">
                    <p class="text-gray-600">
                        Si tienes dudas sobre el proceso o necesitas hacer cambios, 
                        contacta al equipo de soporte.
                    </p>
                    
                    <div class="space-y-xs">
                        <a href="/help" class="btn btn-outline w-full btn-sm">
                            📖 Centro de Ayuda
                        </a>
                        <a href="mailto:soporte-multiarea@uc.cl" class="btn btn-outline w-full btn-sm">
                            📧 Contactar Soporte
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Project Actions Modal -->
<div id="project-actions-modal" class="modal" style="display: none;">
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>⚙️ Acciones del Proyecto</h3>
                <button onclick="App.closeModal('project-actions-modal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="space-y-md">
                    <?php if ($project['status'] === 'draft'): ?>
                        <button onclick="submitProject()" class="btn btn-success w-full">
                            🚀 Enviar para Revisión
                        </button>
                    <?php endif; ?>
                    
                    <button onclick="duplicateProject()" class="btn btn-secondary w-full">
                        📋 Duplicar Proyecto
                    </button>
                    
                    <button onclick="shareProject()" class="btn btn-secondary w-full">
                        📤 Compartir Enlace
                    </button>
                    
                    <button onclick="exportProjectSummary()" class="btn btn-secondary w-full">
                        📄 Exportar Resumen
                    </button>
                    
                    <?php if (in_array($project['status'], ['draft', 'rejected'])): ?>
                        <hr class="my-md">
                        <button onclick="deleteProject()" class="btn btn-error w-full">
                            🗑️ Eliminar Proyecto
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Funciones auxiliares para el template
function getProjectStatusStyle($status) {
    $styles = [
        'draft' => 'bg-gray-50 border border-gray-200',
        'submitted' => 'bg-blue-50 border border-blue-200',
        'in_progress' => 'bg-yellow-50 border border-yellow-200',
        'on_hold' => 'bg-orange-50 border border-orange-200',
        'completed' => 'bg-green-50 border border-green-200',
        'rejected' => 'bg-red-50 border border-red-200',
        'cancelled' => 'bg-gray-50 border border-gray-200'
    ];
    return $styles[$status] ?? 'bg-gray-50 border border-gray-200';
}

function getProjectStatusIcon($status) {
    $icons = [
        'draft' => '📝',
        'submitted' => '📤',
        'in_progress' => '⚡',
        'on_hold' => '⏸️',
        'completed' => '✅',
        'rejected' => '❌',
        'cancelled' => '🚫'
    ];
    return $icons[$status] ?? '📋';
}

function getProjectStatusLabel($status) {
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

function getPhaseStatusClass($status) {
    $classes = [
        'completed' => 'phase-completed',
        'in_progress' => 'phase-active',
        'pending' => 'phase-pending',
        'rejected' => 'phase-rejected'
    ];
    return $classes[$status] ?? 'phase-pending';
}

function getPhaseStatusIcon($status) {
    $icons = [
        'completed' => '✅',
        'in_progress' => '⚡',
        'pending' => '⏳',
        'rejected' => '❌'
    ];
    return $icons[$status] ?? '⏳';
}

function getPhaseStatusLabel($status) {
    $labels = [
        'completed' => 'Completada',
        'in_progress' => 'En Progreso',
        'pending' => 'Pendiente',
        'rejected' => 'Rechazada'
    ];
    return $labels[$status] ?? ucfirst($status);
}

function getPriorityIcon($priority) {
    $icons = [
        'low' => '⬇️',
        'medium' => '➡️',
        'high' => '⬆️',
        'critical' => '🔥'
    ];
    return $icons[$priority] ?? '➡️';
}

function getDocumentIcon($type) {
    $icons = [
        'ficha_formalizacion' => '📋',
        'especificacion_tecnica' => '📄',
        'diagrama_arquitectura' => '🏗️',
        'manual_usuario' => '📖',
        'documentacion_tecnica' => '📚',
        'plan_pruebas' => '🧪',
        'certificado_seguridad' => '🔒'
    ];
    return $icons[$type] ?? '📄';
}

function getDocumentTypeName($type) {
    $names = [
        'ficha_formalizacion' => 'Ficha de Formalización',
        'especificacion_tecnica' => 'Especificación Técnica',
        'diagrama_arquitectura' => 'Diagrama de Arquitectura',
        'manual_usuario' => 'Manual de Usuario',
        'documentacion_tecnica' => 'Documentación Técnica',
        'plan_pruebas' => 'Plan de Pruebas',
        'certificado_seguridad' => 'Certificado de Seguridad'
    ];
    return $names[$type] ?? 'Documento';
}

function getDocumentStatusIcon($status) {
    $icons = [
        'pending' => '⏳',
        'approved' => '✅',
        'rejected' => '❌'
    ];
    return $icons[$status] ?? '📄';
}

function getActivityIcon($action) {
    $icons = [
        'project_created' => '✨',
        'document_uploaded' => '📤',
        'phase_completed' => '✅',
        'phase_started' => '▶️',
        'project_submitted' => '📋'
    ];
    return $icons[$action] ?? '📝';
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'hace unos segundos';
    if ($time < 3600) return 'hace ' . floor($time/60) . ' minutos';
    if ($time < 86400) return 'hace ' . floor($time/3600) . ' horas';
    if ($time < 2592000) return 'hace ' . floor($time/86400) . ' días';
    
    return date('d/m/Y', strtotime($datetime));
}

function getElapsedTime($startDate) {
    $days = floor((time() - strtotime($startDate)) / 86400);
    return $days . ' días';
}

function getRemainingTime($endDate) {
    $days = floor((strtotime($endDate) - time()) / 86400);
    if ($days < 0) return 'Vencido hace ' . abs($days) . ' días';
    if ($days == 0) return 'Vence hoy';
    return $days . ' días restantes';
}

function getRemainingTimeClass($endDate) {
    $days = floor((strtotime($endDate) - time()) / 86400);
    if ($days < 0) return 'text-red-600';
    if ($days <= 3) return 'text-yellow-600';
    return 'text-green-600';
}
?>

<style>
.priority-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.priority-low { background: var(--gray-100); color: var(--gray-700); }
.priority-medium { background: var(--primary-100); color: var(--primary-700); }
.priority-high { background: var(--warning-100); color: var(--warning-700); }
.priority-critical { background: var(--error-100); color: var(--error-700); }

.phase-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.phase-status-badge.completed { background: var(--success-100); color: var(--success-700); }
.phase-status-badge.in_progress { background: var(--warning-100); color: var(--warning-700); }
.phase-status-badge.pending { background: var(--gray-100); color: var(--gray-700); }
.phase-status-badge.rejected { background: var(--error-100); color: var(--error-700); }

.document-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-pending { background: var(--warning-100); color: var(--warning-700); }
.status-approved { background: var(--success-100); color: var(--success-700); }
.status-rejected { background: var(--error-100); color: var(--error-700); }

.phase-completed { border-color: var(--success-300); background: var(--success-50); }
.phase-active { border-color: var(--warning-300); background: var(--warning-50); }
.phase-pending { border-color: var(--gray-300); background: var(--gray-50); }
.phase-rejected { border-color: var(--error-300); background: var(--error-50); }

.activity-timeline {
    position: relative;
}

.activity-timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--gray-200);
}

.activity-item {
    position: relative;
    display: flex;
    gap: 1rem;
    padding-bottom: 1rem;
}

.activity-indicator {
    position: relative;
    z-index: 1;
}

.activity-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    background: white;
    border: 2px solid var(--primary-200);
    border-radius: 50%;
    font-size: 0.875rem;
}

.activity-content {
    flex: 1;
    padding-top: 0.125rem;
}

.activity-description {
    color: var(--gray-700);
    margin-bottom: 0.25rem;
}

.activity-time {
    font-size: 0.75rem;
    color: var(--gray-500);
}

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
// JavaScript para vista de proyecto
console.log('Vista de proyecto cargada:', <?= json_encode($project) ?>);

// Descargar documento
function downloadDocument(documentId) {
    console.log(`Descargando documento ${documentId}`);
    window.open(`/documents/${documentId}/download`, '_blank');
}

// Compartir proyecto
function shareProject() {
    const projectUrl = window.location.href;
    
    if (navigator.share) {
        navigator.share({
            title: '<?= addslashes($project['title']) ?>',
            text: 'Proyecto: <?= addslashes($project['code']) ?>',
            url: projectUrl
        });
    } else {
        navigator.clipboard.writeText(projectUrl).then(() => {
            App.showNotification('Enlace copiado al portapapeles', 'success');
        });
    }
}

// Exportar resumen del proyecto
function exportProjectSummary() {
    App.setLoading(event.target, true);
    
    fetch(`/projects/<?= $project['id'] ?>/export`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `proyecto_<?= $project['code'] ?>.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
        
        App.showNotification('Resumen exportado exitosamente', 'success');
    })
    .catch(error => {
        App.showNotification('Error al exportar resumen', 'error');
    })
    .finally(() => {
        App.setLoading(event.target, false);
    });
}

// Duplicar proyecto
function duplicateProject() {
    if (confirm('¿Crear una copia de este proyecto?')) {
        window.location.href = `/projects/create?duplicate=<?= $project['id'] ?>`;
    }
}

// Enviar proyecto para revisión
function submitProject() {
    if (confirm('¿Enviar este proyecto para revisión? Una vez enviado no podrás editarlo.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/projects/<?= $project['id'] ?>/submit`;
        
        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = '<?= csrf_token() ?>';
        csrfField.value = '<?= csrf_hash() ?>';
        form.appendChild(csrfField);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Eliminar proyecto
function deleteProject() {
    const confirmation = prompt(
        'Esta acción no se puede deshacer. Escribe "ELIMINAR" para confirmar:'
    );
    
    if (confirmation === 'ELIMINAR') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/projects/<?= $project['id'] ?>/delete`;
        
        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = '<?= csrf_token() ?>';
        csrfField.value = '<?= csrf_hash() ?>';
        form.appendChild(csrfField);
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-refresh del progreso cada 30 segundos para proyectos activos
<?php if (in_array($project['status'], ['submitted', 'in_progress'])): ?>
setInterval(() => {
    fetch(`/projects/<?= $project['id'] ?>/status`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status !== '<?= $project['status'] ?>') {
            location.reload();
        }
    })
    .catch(error => console.log('Error verificando estado:', error));
}, 30000);
<?php endif; ?>

// Animación del progreso al cargar
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.querySelector('.bg-primary-500');
    if (progressBar) {
        progressBar.style.width = '0%';
        setTimeout(() => {
            progressBar.style.width = '<?= $project['completion_percentage'] ?>%';
        }, 500);
    }
});
<?php $this->endSection(); ?>