<?php
// app/Views/admin/projects/review.php
$this->extend('layouts/main');
$this->section('content');
?>

<div class="container">
    <!-- Header -->
    <section class="mb-2xl">
        <div class="flex items-center justify-between mb-lg">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-sm">
                    👁️ Revisar Proyecto: <?= esc($project['code']) ?>
                </h1>
                <p class="text-gray-500">Revisión detallada y decisión sobre el proyecto</p>
            </div>
            <div class="flex items-center gap-md">
                <a href="/admin/projects/pending" class="btn btn-secondary">
                    ← Volver a Pendientes
                </a>
                <button onclick="App.openModal('quick-decision-modal')" class="btn btn-primary">
                    ⚡ Decisión Rápida
                </button>
            </div>
        </div>
        
        <!-- Project Status Bar -->
        <div class="card bg-blue-50 border border-blue-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-lg">
                    <div class="text-2xl">📋</div>
                    <div>
                        <h2 class="text-xl font-semibold text-blue-800">
                            <?= esc($project['title']) ?>
                        </h2>
                        <p class="text-blue-600">
                            Solicitado por: <?= esc($project['requester_name']) ?> • 
                            Creado: <?= date('d/m/Y H:i', strtotime($project['created_at'])) ?>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block px-md py-sm rounded-lg text-sm font-medium bg-blue-100 text-blue-700">
                        Estado: <?= ucfirst($project['status']) ?>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-3 gap-xl">
        <!-- Main Content - 2 columns -->
        <div class="col-span-2 space-y-xl">
            
            <!-- Project Details -->
            <section class="card">
                <h3 class="card-title">📋 Detalles del Proyecto</h3>
                
                <div class="space-y-lg">
                    <div>
                        <label class="font-semibold text-gray-700 block mb-sm">Descripción:</label>
                        <p class="text-gray-600 leading-relaxed">
                            <?= esc($project['description']) ?>
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-lg">
                        <div>
                            <label class="font-semibold text-gray-700 block mb-sm">Departamento:</label>
                            <p class="text-gray-600"><?= esc($project['department'] ?? 'No especificado') ?></p>
                        </div>
                        <div>
                            <label class="font-semibold text-gray-700 block mb-sm">Prioridad:</label>
                            <span class="priority-badge priority-<?= $project['priority'] ?? 'medium' ?>">
                                <?= ucfirst($project['priority'] ?? 'medium') ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-lg">
                        <div>
                            <label class="font-semibold text-gray-700 block mb-sm">Email de Contacto:</label>
                            <p class="text-gray-600">
                                <a href="mailto:<?= esc($project['contact_email'] ?? '') ?>" 
                                   class="text-primary hover:underline">
                                    <?= esc($project['contact_email'] ?? 'No especificado') ?>
                                </a>
                            </p>
                        </div>
                        <div>
                            <label class="font-semibold text-gray-700 block mb-sm">Teléfono:</label>
                            <p class="text-gray-600"><?= esc($project['contact_phone'] ?? 'No especificado') ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($project['budget'])): ?>
                    <div>
                        <label class="font-semibold text-gray-700 block mb-sm">Presupuesto Estimado:</label>
                        <p class="text-gray-600 text-lg font-medium">
                            $<?= number_format($project['budget'], 0, ',', '.') ?> CLP
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Project Phases -->
            <section class="card">
                <h3 class="card-title">🔄 Fases del Proyecto</h3>
                
                <div class="space-y-md">
                    <?php 
                    $phases = $project['phases'] ?? [
                        ['area_name' => 'Formalización', 'status' => 'completed', 'area_color' => '#3B82F6'],
                        ['area_name' => 'Arquitectura', 'status' => 'in_progress', 'area_color' => '#10B981'],
                        ['area_name' => 'Infraestructura', 'status' => 'pending', 'area_color' => '#F59E0B'],
                        ['area_name' => 'Seguridad', 'status' => 'pending', 'area_color' => '#EF4444'],
                    ];
                    ?>
                    
                    <?php foreach ($phases as $index => $phase): ?>
                        <div class="flex items-center gap-md p-md rounded-lg border
                            <?= $phase['status'] === 'completed' ? 'bg-green-50 border-green-200' : 
                                ($phase['status'] === 'in_progress' ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200') ?>">
                            
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-semibold text-sm"
                                 style="background: <?= $phase['area_color'] ?>;">
                                <?= $index + 1 ?>
                            </div>
                            
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-800"><?= esc($phase['area_name']) ?></h4>
                                <p class="text-sm text-gray-500">
                                    <?php if ($phase['status'] === 'completed'): ?>
                                        ✅ Completada
                                    <?php elseif ($phase['status'] === 'in_progress'): ?>
                                        ⚡ En progreso (tu revisión)
                                    <?php else: ?>
                                        ⏳ Pendiente
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <?php if ($phase['status'] === 'in_progress'): ?>
                                <span class="px-sm py-xs bg-blue-100 text-blue-700 rounded text-xs font-medium">
                                    Tu turno
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <!-- Documents -->
            <section class="card">
                <div class="flex items-center justify-between mb-lg">
                    <h3 class="card-title mb-0">📎 Documentos del Proyecto</h3>
                    <button onclick="App.openModal('upload-document-modal')" class="btn btn-sm btn-secondary">
                        📤 Subir Documento
                    </button>
                </div>
                
                <?php 
                $documents = $project['documents'] ?? [
                    ['id' => 1, 'original_name' => 'Ficha_Formalizacion.pdf', 'document_type' => 'ficha_formalizacion', 'status' => 'approved', 'file_size' => 2048000],
                    ['id' => 2, 'original_name' => 'Especificacion_Tecnica.docx', 'document_type' => 'especificacion_tecnica', 'status' => 'pending', 'file_size' => 1024000],
                ];
                ?>
                
                <?php if (!empty($documents)): ?>
                    <div class="space-y-sm">
                        <?php foreach ($documents as $doc): ?>
                            <div class="flex items-center justify-between p-md bg-gray-50 rounded-lg border">
                                <div class="flex items-center gap-md">
                                    <div class="text-2xl">
                                        <?= getDocumentIcon($doc['document_type']) ?>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">
                                            <?= esc($doc['original_name']) ?>
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            <?= getDocumentTypeName($doc['document_type']) ?> • 
                                            <?= formatFileSize($doc['file_size']) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-sm">
                                    <span class="status-badge status-<?= $doc['status'] ?>">
                                        <?= ucfirst($doc['status']) ?>
                                    </span>
                                    <button onclick="downloadDocument(<?= $doc['id'] ?>)" 
                                            class="btn btn-sm btn-outline" 
                                            data-tooltip="Descargar">
                                        📥
                                    </button>
                                    <?php if ($doc['status'] === 'pending'): ?>
                                        <button onclick="reviewDocument(<?= $doc['id'] ?>)" 
                                                class="btn btn-sm btn-primary" 
                                                data-tooltip="Revisar">
                                            👁️
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-lg text-gray-500">
                        <div class="text-2xl mb-md">📄</div>
                        <p>No hay documentos subidos aún</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
        
        <!-- Sidebar - 1 column -->
        <div class="space-y-xl">
            
            <!-- Decision Panel -->
            <section class="card">
                <h3 class="card-title">⚖️ Panel de Decisión</h3>
                
                <form id="decision-form" class="space-y-lg">
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Decisión:</label>
                        <div class="space-y-sm">
                            <label class="flex items-center gap-sm">
                                <input type="radio" name="decision" value="approve" class="form-radio">
                                <span class="text-green-600 font-medium">✅ Aprobar</span>
                            </label>
                            <label class="flex items-center gap-sm">
                                <input type="radio" name="decision" value="reject" class="form-radio">
                                <span class="text-red-600 font-medium">❌ Rechazar</span>
                            </label>
                            <label class="flex items-center gap-sm">
                                <input type="radio" name="decision" value="request_changes" class="form-radio">
                                <span class="text-yellow-600 font-medium">🔄 Solicitar Cambios</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Comentarios:</label>
                        <textarea name="comments" class="form-textarea" rows="4" 
                                  placeholder="Escribe tus comentarios sobre el proyecto..."></textarea>
                    </div>
                    
                    <div class="form-group" id="rejection-reason-group" style="display: none;">
                        <label class="form-label">Motivo del Rechazo:</label>
                        <select name="rejection_reason" class="form-select">
                            <option value="">Selecciona un motivo</option>
                            <option value="incomplete_documentation">Documentación incompleta</option>
                            <option value="technical_issues">Problemas técnicos</option>
                            <option value="security_concerns">Preocupaciones de seguridad</option>
                            <option value="budget_constraints">Restricciones presupuestarias</option>
                            <option value="other">Otro motivo</option>
                        </select>
                    </div>
                    
                    <div class="space-y-sm">
                        <button type="submit" class="btn btn-primary w-full">
                            💾 Guardar Decisión
                        </button>
                        <button type="button" onclick="saveDraft()" class="btn btn-secondary w-full">
                            📝 Guardar Borrador
                        </button>
                    </div>
                </form>
            </section>
            
            <!-- Project Info -->
            <section class="card">
                <h3 class="card-title">ℹ️ Información Adicional</h3>
                
                <div class="space-y-md text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tiempo estimado:</span>
                        <span class="font-medium">5-7 días</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Complejidad:</span>
                        <span class="font-medium">Media</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Área actual:</span>
                        <span class="font-medium">Arquitectura</span>
                    </div>
                    <?php if (isset($project['estimated_completion'])): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Fecha objetivo:</span>
                        <span class="font-medium">
                            <?= date('d/m/Y', strtotime($project['estimated_completion'])) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-lg pt-lg border-t border-gray-200">
                    <h4 class="font-medium text-gray-800 mb-sm">Acciones Rápidas:</h4>
                    <div class="space-y-xs">
                        <button onclick="contactRequester()" class="btn btn-sm btn-outline w-full">
                            📧 Contactar Solicitante
                        </button>
                        <button onclick="assignToReviewer()" class="btn btn-sm btn-outline w-full">
                            👥 Asignar a Revisor
                        </button>
                        <button onclick="requestMoreInfo()" class="btn btn-sm btn-outline w-full">
                            ❓ Solicitar Más Info
                        </button>
                    </div>
                </div>
            </section>
            
            <!-- Review History -->
            <section class="card">
                <h3 class="card-title">📝 Historial de Revisiones</h3>
                
                <div class="space-y-sm text-sm">
                    <div class="p-sm bg-green-50 rounded border-l-4 border-green-400">
                        <div class="font-medium text-green-800">✅ Aprobado - Formalización</div>
                        <div class="text-green-600">Carlos Mendoza • hace 2 días</div>
                        <div class="text-green-700 mt-xs">"Documentación completa y correcta"</div>
                    </div>
                    
                    <div class="p-sm bg-blue-50 rounded border-l-4 border-blue-400">
                        <div class="font-medium text-blue-800">🔄 En Revisión - Arquitectura</div>
                        <div class="text-blue-600">Ahora • Esperando tu decisión</div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Modal de Decisión Rápida -->
<div id="quick-decision-modal" class="modal" style="display: none;">
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>⚡ Decisión Rápida</h3>
                <button onclick="App.closeModal('quick-decision-modal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="space-y-md">
                    <button onclick="quickDecision('approve')" class="btn btn-success btn-lg w-full">
                        ✅ Aprobar Proyecto
                    </button>
                    <button onclick="quickDecision('reject')" class="btn btn-error btn-lg w-full">
                        ❌ Rechazar Proyecto
                    </button>
                    <button onclick="quickDecision('request_changes')" class="btn btn-warning btn-lg w-full">
                        🔄 Solicitar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Funciones auxiliares
function getDocumentIcon($type) {
    $icons = [
        'ficha_formalizacion' => '📋',
        'especificacion_tecnica' => '📄',
        'diagrama_arquitectura' => '🏗️',
        'manual_usuario' => '📖',
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
        'plan_pruebas' => 'Plan de Pruebas',
        'certificado_seguridad' => 'Certificado de Seguridad'
    ];
    return $names[$type] ?? 'Documento';
}

function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>

<style>
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

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-pending { background: var(--warning-100); color: var(--warning-700); }
.status-approved { background: var(--success-100); color: var(--success-700); }
.status-rejected { background: var(--error-100); color: var(--error-700); }

.form-radio {
    width: 1rem;
    height: 1rem;
    color: var(--primary-500);
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
// JavaScript para revisión de proyecto
console.log('Admin Review Project cargado');

// Manejar cambios en decisión
document.querySelectorAll('input[name="decision"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const rejectionGroup = document.getElementById('rejection-reason-group');
        if (this.value === 'reject') {
            rejectionGroup.style.display = 'block';
            rejectionGroup.querySelector('select').setAttribute('required', '');
        } else {
            rejectionGroup.style.display = 'none';
            rejectionGroup.querySelector('select').removeAttribute('required');
        }
    });
});

// Enviar formulario de decisión
document.getElementById('decision-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const decision = formData.get('decision');
    
    if (!decision) {
        App.showNotification('Selecciona una decisión', 'warning');
        return;
    }
    
    if (decision === 'reject' && !formData.get('rejection_reason')) {
        App.showNotification('Selecciona un motivo de rechazo', 'warning');
        return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    App.setLoading(submitBtn, true);
    
    fetch(`/admin/projects/${formData.get('project_id')}/decision`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            App.showNotification('Decisión guardada exitosamente', 'success');
            setTimeout(() => {
                window.location.href = '/admin/projects/pending';
            }, 2000);
        } else {
            App.showNotification(data.message || 'Error al guardar decisión', 'error');
        }
    })
    .catch(error => {
        App.showNotification('Error de conexión', 'error');
    })
    .finally(() => {
        App.setLoading(submitBtn, false);
    });
});

// Decisiones rápidas
function quickDecision(decision) {
    let message = '';
    switch(decision) {
        case 'approve':
            message = '¿Aprobar este proyecto?';
            break;
        case 'reject':
            message = '¿Rechazar este proyecto?';
            break;
        case 'request_changes':
            message = '¿Solicitar cambios en este proyecto?';
            break;
    }
    
    if (confirm(message)) {
        const projectId = document.querySelector('input[name="project_id"]').value;
        
        fetch(`/admin/projects/${projectId}/quick-decision`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                decision: decision,
                comments: `Decisión rápida: ${decision}`
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showNotification('Decisión procesada', 'success');
                App.closeModal('quick-decision-modal');
                setTimeout(() => {
                    window.location.href = '/admin/projects/pending';
                }, 1500);
            } else {
                App.showNotification(data.message || 'Error al procesar decisión', 'error');
            }
        })
        .catch(error => {
            App.showNotification('Error de conexión', 'error');
        });
    }
}

// Guardar borrador
function saveDraft() {
    const formData = new FormData(document.getElementById('decision-form'));
    
    fetch(`/admin/projects/${formData.get('project_id')}/save-draft`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            App.showNotification('Borrador guardado', 'info');
        } else {
            App.showNotification('Error al guardar borrador', 'error');
        }
    });
}

// Acciones adicionales
function downloadDocument(docId) {
    window.open(`/admin/documents/${docId}/download`, '_blank');
}

function reviewDocument(docId) {
    window.location.href = `/admin/documents/${docId}/review`;
}

function contactRequester() {
    const email = '<?= esc($project['contact_email'] ?? '') ?>';
    const subject = `Consulta sobre proyecto ${<?= json_encode($project['code']) ?>}`;
    window.open(`mailto:${email}?subject=${encodeURIComponent(subject)}`);
}

function assignToReviewer() {
    App.showNotification('Función de asignación en desarrollo', 'info');
}

function requestMoreInfo() {
    App.showNotification('Función de solicitud de información en desarrollo', 'info');
}

// Atajos de teclado
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'a': // Ctrl+A = Aprobar
                e.preventDefault();
                document.querySelector('input[value="approve"]').checked = true;
                break;
            case 'r': // Ctrl+R = Rechazar
                e.preventDefault();
                document.querySelector('input[value="reject"]').checked = true;
                break;
            case 's': // Ctrl+S = Guardar
                e.preventDefault();
                document.getElementById('decision-form').dispatchEvent(new Event('submit'));
                break;
        }
    }
});

console.log('Atajos disponibles:');
console.log('Ctrl+A: Seleccionar aprobar');
console.log('Ctrl+R: Seleccionar rechazar');
console.log('Ctrl+S: Guardar decisión');
<?php $this->endSection(); ?>