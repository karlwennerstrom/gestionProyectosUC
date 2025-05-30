<?php
// app/Views/admin/documents/index.php
$this->extend('layouts/main');
$this->section('content');
?>

<div class="container">
    <!-- Header -->
    <section class="flex items-center justify-between mb-2xl">
        <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-sm">📎 Gestión de Documentos</h1>
            <p class="text-gray-500">Revisar y aprobar documentos de proyectos</p>
        </div>
        <div class="flex items-center gap-md">
            <button onclick="App.openModal('document-filters-modal')" class="btn btn-secondary">
                🔍 Filtros Avanzados
            </button>
            <a href="/admin/dashboard" class="btn btn-outline">
                ← Volver al Dashboard
            </a>
        </div>
    </section>

    <!-- Stats Cards -->
    <section class="grid grid-cols-4 gap-lg mb-2xl">
        <div class="stat-card" style="border-left: 4px solid #f59e0b;">
            <div class="stat-icon">📋</div>
            <div class="stat-number">12</div>
            <div class="stat-label">Pendientes de Revisión</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-icon">✅</div>
            <div class="stat-number">85</div>
            <div class="stat-label">Aprobados</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #ef4444;">
            <div class="stat-icon">❌</div>
            <div class="stat-number">8</div>
            <div class="stat-label">Rechazados</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-icon">📊</div>
            <div class="stat-number">2.3 GB</div>
            <div class="stat-label">Almacenamiento</div>
        </div>
    </section>

    <!-- Quick Filters -->
    <section class="card mb-xl">
        <div class="flex items-center justify-between gap-lg">
            <div class="flex items-center gap-md">
                <div class="form-group mb-0">
                    <input type="text" id="search-documents" class="form-input" 
                           placeholder="Buscar documentos o proyectos..." 
                           style="min-width: 300px;">
                </div>
                <div class="form-group mb-0">
                    <select id="filter-status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendientes</option>
                        <option value="approved">Aprobados</option>
                        <option value="rejected">Rechazados</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <select id="filter-type" class="form-select">
                        <option value="">Todos los tipos</option>
                        <option value="ficha_formalizacion">Ficha de Formalización</option>
                        <option value="especificacion_tecnica">Especificación Técnica</option>
                        <option value="diagrama_arquitectura">Diagrama de Arquitectura</option>
                        <option value="manual_usuario">Manual de Usuario</option>
                        <option value="plan_pruebas">Plan de Pruebas</option>
                        <option value="certificado_seguridad">Certificado de Seguridad</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-sm">
                <button onclick="toggleView()" class="btn btn-secondary" id="view-toggle">
                    📊 Vista Lista
                </button>
                <button onclick="exportDocuments()" class="btn btn-secondary">
                    📥 Exportar
                </button>
            </div>
        </div>
    </section>

    <!-- Documents Grid/List -->
    <section id="documents-container">
        <div id="grid-view" class="grid grid-cols-1 gap-lg">
            
            <!-- Document Card 1 -->
            <div class="card document-card" data-status="pending" data-type="ficha_formalizacion">
                <div class="flex items-start gap-lg">
                    <!-- Document Preview -->
                    <div class="flex-shrink-0">
                        <div class="w-16 h-20 bg-red-100 rounded-lg flex items-center justify-center text-2xl">
                            📋
                        </div>
                    </div>
                    
                    <!-- Document Info -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-sm">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-xs">
                                    Ficha_Formalizacion_v2.pdf
                                </h3>
                                <p class="text-primary font-medium">PROJ-2025-001 - Sistema de Gestión Académica</p>
                            </div>
                            <span class="status-badge status-pending">Pendiente</span>
                        </div>
                        
                        <div class="grid grid-cols-4 gap-lg text-sm text-gray-600 mb-md">
                            <div>
                                <span class="font-medium">Tipo:</span><br>
                                Ficha de Formalización
                            </div>
                            <div>
                                <span class="font-medium">Subido por:</span><br>
                                Juan Pérez
                            </div>
                            <div>
                                <span class="font-medium">Fecha:</span><br>
                                <?= date('d/m/Y H:i', strtotime('-2 hours')) ?>
                            </div>
                            <div>
                                <span class="font-medium">Tamaño:</span><br>
                                2.4 MB
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="mb-md">
                            <div class="flex justify-between text-xs mb-xs">
                                <span class="text-gray-600">Estado de Revisión</span>
                                <span class="font-semibold">25%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: 25%"></div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-sm">
                                <button onclick="previewDocument(1)" class="btn btn-sm btn-secondary">
                                    👁️ Vista Previa
                                </button>
                                <button onclick="downloadDocument(1)" class="btn btn-sm btn-outline">
                                    📥 Descargar
                                </button>
                                <button onclick="shareDocument(1)" class="btn btn-sm btn-outline">
                                    📤 Compartir
                                </button>
                            </div>
                            <div class="flex items-center gap-xs">
                                <button onclick="approveDocument(1)" class="btn btn-sm btn-success">
                                    ✅ Aprobar
                                </button>
                                <button onclick="rejectDocument(1)" class="btn btn-sm btn-error">
                                    ❌ Rechazar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Document Card 2 -->
            <div class="card document-card" data-status="approved" data-type="especificacion_tecnica">
                <div class="flex items-start gap-lg">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-20 bg-blue-100 rounded-lg flex items-center justify-center text-2xl">
                            📄
                        </div>
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-sm">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-xs">
                                    Especificacion_Tecnica.docx
                                </h3>
                                <p class="text-primary font-medium">PROJ-2025-002 - Portal de Estudiantes</p>
                            </div>
                            <span class="status-badge status-approved">Aprobado</span>
                        </div>
                        
                        <div class="grid grid-cols-4 gap-lg text-sm text-gray-600 mb-md">
                            <div>
                                <span class="font-medium">Tipo:</span><br>
                                Especificación Técnica
                            </div>
                            <div>
                                <span class="font-medium">Subido por:</span><br>
                                María González
                            </div>
                            <div>
                                <span class="font-medium">Aprobado:</span><br>
                                <?= date('d/m/Y H:i', strtotime('-1 day')) ?>
                            </div>
                            <div>
                                <span class="font-medium">Tamaño:</span><br>
                                1.8 MB
                            </div>
                        </div>
                        
                        <div class="mb-md">
                            <div class="flex justify-between text-xs mb-xs">
                                <span class="text-gray-600">Estado de Revisión</span>
                                <span class="font-semibold text-green-600">100% ✅</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-sm">
                                <button onclick="previewDocument(2)" class="btn btn-sm btn-secondary">
                                    👁️ Vista Previa
                                </button>
                                <button onclick="downloadDocument(2)" class="btn btn-sm btn-outline">
                                    📥 Descargar
                                </button>
                                <button onclick="shareDocument(2)" class="btn btn-sm btn-outline">
                                    📤 Compartir
                                </button>
                            </div>
                            <div class="flex items-center gap-xs">
                                <span class="text-sm text-green-600 font-medium">
                                    ✅ Aprobado por: Carlos Mendoza
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Card 3 -->
            <div class="card document-card" data-status="rejected" data-type="diagrama_arquitectura">
                <div class="flex items-start gap-lg">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-20 bg-purple-100 rounded-lg flex items-center justify-center text-2xl">
                            🏗️
                        </div>
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-sm">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-xs">
                                    Diagrama_Arquitectura_v1.png
                                </h3>
                                <p class="text-primary font-medium">PROJ-2025-003 - API de Integración</p>
                            </div>
                            <span class="status-badge status-rejected">Rechazado</span>
                        </div>
                        
                        <div class="grid grid-cols-4 gap-lg text-sm text-gray-600 mb-md">
                            <div>
                                <span class="font-medium">Tipo:</span><br>
                                Diagrama de Arquitectura
                            </div>
                            <div>
                                <span class="font-medium">Subido por:</span><br>
                                Luis Torres
                            </div>
                            <div>
                                <span class="font-medium">Rechazado:</span><br>
                                <?= date('d/m/Y H:i', strtotime('-3 hours')) ?>
                            </div>
                            <div>
                                <span class="font-medium">Tamaño:</span><br>
                                3.2 MB
                            </div>
                        </div>

                        <!-- Rejection Reason -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-md mb-md">
                            <div class="flex items-start gap-sm">
                                <span class="text-red-500 text-lg">⚠️</span>
                                <div>
                                    <h4 class="font-semibold text-red-800 text-sm">Motivo del Rechazo:</h4>
                                    <p class="text-red-700 text-sm">
                                        El diagrama no incluye los componentes de seguridad requeridos. 
                                        Favor incluir firewall, autenticación y cifrado de datos.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-sm">
                                <button onclick="previewDocument(3)" class="btn btn-sm btn-secondary">
                                    👁️ Vista Previa
                                </button>
                                <button onclick="downloadDocument(3)" class="btn btn-sm btn-outline">
                                    📥 Descargar
                                </button>
                                <button onclick="sendFeedback(3)" class="btn btn-sm btn-warning">
                                    💬 Enviar Feedback
                                </button>
                            </div>
                            <div class="flex items-center gap-xs">
                                <button onclick="requestRevision(3)" class="btn btn-sm btn-secondary">
                                    🔄 Solicitar Revisión
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Table View (Initially Hidden) -->
        <div id="table-view" class="card" style="display: none;">
            <div class="table-container">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Proyecto</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Subido por</th>
                            <th>Fecha</th>
                            <th>Tamaño</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="flex items-center gap-sm">
                                    <span class="text-lg">📋</span>
                                    <span class="font-medium">Ficha_Formalizacion_v2.pdf</span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="font-medium text-primary">PROJ-2025-001</div>
                                    <div class="text-sm text-gray-500">Sistema de Gestión Académica</div>
                                </div>
                            </td>
                            <td><span class="text-sm">Ficha de Formalización</span></td>
                            <td><span class="status-badge status-pending">Pendiente</span></td>
                            <td>Juan Pérez</td>
                            <td><?= date('d/m/Y H:i', strtotime('-2 hours')) ?></td>
                            <td>2.4 MB</td>
                            <td>
                                <div class="flex items-center gap-xs">
                                    <button class="btn btn-sm btn-secondary" data-tooltip="Vista previa">👁️</button>
                                    <button class="btn btn-sm btn-success" data-tooltip="Aprobar">✅</button>
                                    <button class="btn btn-sm btn-error" data-tooltip="Rechazar">❌</button>
                                </div>
                            </td>
                        </tr>
                        <!-- More rows would be dynamically populated -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Bulk Actions -->
    <section class="card">
        <h3 class="text-lg font-semibold mb-md">🎯 Acciones Masivas</h3>
        <div class="flex items-center gap-md">
            <button onclick="selectAllDocuments()" class="btn btn-secondary">
                ☑️ Seleccionar Todos
            </button>
            <button onclick="bulkApprove()" class="btn btn-success">
                ✅ Aprobar Seleccionados
            </button>
            <button onclick="bulkReject()" class="btn btn-error">
                ❌ Rechazar Seleccionados
            </button>
            <button onclick="bulkDownload()" class="btn btn-outline">
                📥 Descargar Seleccionados
            </button>
            <button onclick="generateReport()" class="btn btn-secondary">
                📊 Generar Reporte
            </button>
        </div>
    </section>
</div>

<!-- Document Preview Modal -->
<div id="document-preview-modal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="App.closeModal(document.getElementById('document-preview-modal'))"></div>
    <div class="modal-container" style="max-width: 90vw; max-height: 90vh;">
        <div class="modal-header">
            <h3 class="modal-title">Vista Previa del Documento</h3>
            <button onclick="App.closeModal(document.getElementById('document-preview-modal'))" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <iframe id="document-iframe" src="" style="width: 100%; height: 70vh; border: none;"></iframe>
        </div>
        <div class="modal-footer">
            <button onclick="App.closeModal(document.getElementById('document-preview-modal'))" class="btn btn-secondary">Cerrar</button>
            <button onclick="downloadCurrentDocument()" class="btn btn-primary">📥 Descargar</button>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejection-modal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="App.closeModal(document.getElementById('rejection-modal'))"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Rechazar Documento</h3>
            <button onclick="App.closeModal(document.getElementById('rejection-modal'))" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <form id="rejection-form">
                <div class="form-group">
                    <label class="form-label">Motivo del Rechazo *</label>
                    <select class="form-select" name="rejection_reason" required>
                        <option value="">Seleccionar motivo...</option>
                        <option value="incomplete">Documentación incompleta</option>
                        <option value="format">Formato no válido</option>
                        <option value="content">Contenido insuficiente</option>
                        <option value="requirements">No cumple con los requisitos</option>
                        <option value="security">Problemas de seguridad</option>
                        <option value="other">Otro motivo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Comentarios Adicionales</label>
                    <textarea class="form-textarea" name="comments" rows="4" 
                              placeholder="Describe en detalle los problemas encontrados y las acciones requeridas..."></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="notify_user" checked>
                        Notificar al usuario por email
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button onclick="App.closeModal(document.getElementById('rejection-modal'))" class="btn btn-secondary">Cancelar</button>
            <button onclick="submitRejection()" class="btn btn-error">❌ Confirmar Rechazo</button>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
// JavaScript para gestión de documentos
console.log('Vista de gestión de documentos cargada');

let currentView = 'grid';
let selectedDocuments = [];

// Toggle between grid and table view
function toggleView() {
    const gridView = document.getElementById('grid-view');
    const tableView = document.getElementById('table-view');
    const toggleBtn = document.getElementById('view-toggle');
    
    if (currentView === 'grid') {
        gridView.style.display = 'none';
        tableView.style.display = 'block';
        toggleBtn.innerHTML = '📊 Vista Tarjetas';
        currentView = 'table';
    } else {
        gridView.style.display = 'block';
        tableView.style.display = 'none';
        toggleBtn.innerHTML = '📋 Vista Lista';
        currentView = 'grid';
    }
}

// Document actions
function previewDocument(documentId) {
    const modal = document.getElementById('document-preview-modal');
    const iframe = document.getElementById('document-iframe');
    
    // In a real implementation, this would load the actual document
    iframe.src = `/admin/documents/${documentId}/preview`;
    
    modal.style.display = 'flex';
}

function downloadDocument(documentId) {
    console.log(`Downloading document ${documentId}`);
    window.open(`/admin/documents/${documentId}/download`, '_blank');
}

function shareDocument(documentId) {
    if (navigator.share) {
        navigator.share({
            title: 'Documento del Sistema Multi-Área UC',
            url: window.location.origin + `/documents/${documentId}`
        });
    } else {
        navigator.clipboard.writeText(window.location.origin + `/documents/${documentId}`);
        App.showNotification('Enlace copiado al portapapeles', 'success');
    }
}

function approveDocument(documentId) {
    if (confirm('¿Estás seguro de aprobar este documento?')) {
        App.setLoading(event.target, true);
        
        fetch(`/admin/documents/${documentId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                approved_by: <?= $user['id'] ?>,
                comments: 'Documento aprobado'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                App.showNotification('Documento aprobado exitosamente', 'success');
                location.reload();
            } else {
                App.showNotification('Error al aprobar documento', 'error');
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

function rejectDocument(documentId) {
    // Store document ID for the modal
    document.getElementById('rejection-form').dataset.documentId = documentId;
    document.getElementById('rejection-modal').style.display = 'flex';
}

function submitRejection() {
    const form = document.getElementById('rejection-form');
    const documentId = form.dataset.documentId;
    const formData = new FormData(form);
    
    App.setLoading(event.target, true);
    
    fetch(`/admin/documents/${documentId}/reject`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            App.showNotification('Documento rechazado', 'success');
            App.closeModal(document.getElementById('rejection-modal'));
            location.reload();
        } else {
            App.showNotification('Error al rechazar documento', 'error');
        }
    })
    .catch(error => {
        App.showNotification('Error de conexión', 'error');
    })
    .finally(() => {
        App.setLoading(event.target, false);
    });
}

// Filtering
const searchInput = document.getElementById('search-documents');
const statusFilter = document.getElementById('filter-status');
const typeFilter = document.getElementById('filter-type');

function filterDocuments() {
    const searchTerm = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value;
    const typeValue = typeFilter.value;
    
    const documentCards = document.querySelectorAll('.document-card');
    
    documentCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        const status = card.dataset.status;
        const type = card.dataset.type;
        
        const matchesSearch = !searchTerm || text.includes(searchTerm);
        const matchesStatus = !statusValue || status === statusValue;
        const matchesType = !typeValue || type === typeValue;
        
        if (matchesSearch && matchesStatus && matchesType) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

searchInput.addEventListener('input', App.utils.debounce(filterDocuments, 300));
statusFilter.addEventListener('change', filterDocuments);
typeFilter.addEventListener('change', filterDocuments);

// Bulk actions
function selectAllDocuments() {
    const checkboxes = document.querySelectorAll('.document-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
    });
    
    updateSelectedDocuments();
}

function updateSelectedDocuments() {
    selectedDocuments = Array.from(document.querySelectorAll('.document-checkbox:checked'))
                            .map(cb => cb.value);
    
    // Update UI to show selected count
    const bulkActions = document.querySelector('.bulk-actions');
    if (selectedDocuments.length > 0) {
        bulkActions.style.display = 'block';
    } else {
        bulkActions.style.display = 'none';
    }
}

function exportDocuments() {
    App.setLoading(event.target, true);
    
    fetch('/admin/documents/export', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            filters: {
                status: statusFilter.value,
                type: typeFilter.value,
                search: searchInput.value
            }
        })
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `documentos_${new Date().toISOString().split('T')[0]}.xlsx`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
        
        App.showNotification('Reporte exportado exitosamente', 'success');
    })
    .catch(error => {
        App.showNotification('Error al exportar', 'error');
    })
    .finally(() => {
        App.setLoading(event.target, false);
    });
}

// Add status badge styles
const style = document.createElement('style');
style.textContent = `
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .status-rejected {
        background-color: #fee2e2;
        color: #991b1b;
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
    }
    
    .modal-container {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        position: relative;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6b7280;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }
`;
document.head.appendChild(style);

console.log('Gestión de documentos inicializada correctamente');
<?php $this->endSection(); ?>