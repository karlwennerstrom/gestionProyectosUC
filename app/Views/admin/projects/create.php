<?php
// app/Views/projects/create.php
$this->extend('layouts/main');
$this->section('content');
?>

<div class="container">
    <!-- Header -->
    <section class="mb-2xl">
        <div class="flex items-center justify-between mb-lg">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-sm">➕ Crear Nuevo Proyecto</h1>
                <p class="text-gray-500">Completa la información para iniciar tu proyecto</p>
            </div>
            <div class="flex items-center gap-md">
                <a href="/projects" class="btn btn-secondary">
                    ← Volver a Mis Proyectos
                </a>
                <button onclick="saveDraft()" class="btn btn-outline" id="save-draft-btn">
                    💾 Guardar Borrador
                </button>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="card bg-blue-50 border border-blue-200">
            <div class="flex items-center gap-lg">
                <div class="text-2xl">📋</div>
                <div class="flex-1">
                    <h3 class="font-semibold text-blue-800 mb-sm">Paso 1: Información Básica del Proyecto</h3>
                    <p class="text-blue-600 text-sm">
                        Proporciona los detalles principales de tu proyecto. Podrás agregar documentos después de crearlo.
                    </p>
                </div>
                <div class="text-right text-blue-600">
                    <div class="text-sm font-medium">Progreso</div>
                    <div class="text-2xl font-bold">1/3</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Form -->
    <div class="grid grid-cols-3 gap-xl">
        <!-- Main Form - 2 columns -->
        <div class="col-span-2">
            <form id="project-form" method="POST" action="/projects/store" data-validate>
                <?= csrf_field() ?>
                
                <!-- Basic Information -->
                <section class="card mb-xl">
                    <h2 class="card-title">📋 Información Básica</h2>
                    
                    <div class="space-y-lg">
                        <div class="form-group">
                            <label class="form-label" for="title">
                                Título del Proyecto *
                                <span class="text-sm text-gray-500 font-normal ml-sm">
                                    (Describe brevemente qué quieres desarrollar)
                                </span>
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   class="form-input" 
                                   placeholder="Ej: Sistema de Gestión de Estudiantes"
                                   value="<?= old('title') ?>"
                                   required
                                   minlength="5"
                                   maxlength="255">
                            <div class="text-xs text-gray-500 mt-xs">
                                Mínimo 5 caracteres, máximo 255
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="description">
                                Descripción Detallada *
                                <span class="text-sm text-gray-500 font-normal ml-sm">
                                    (Explica qué funcionalidades necesitas, quiénes lo usarán, etc.)
                                </span>
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-textarea" 
                                      rows="6"
                                      placeholder="Describe en detalle tu proyecto: objetivos, funcionalidades principales, usuarios objetivo, integración con sistemas existentes, etc."
                                      required
                                      minlength="20"><?= old('description') ?></textarea>
                            <div class="text-xs text-gray-500 mt-xs">
                                Mínimo 20 caracteres. Sé específico para acelerar el proceso de revisión.
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-lg">
                            <div class="form-group">
                                <label class="form-label" for="project_type">Tipo de Proyecto *</label>
                                <select id="project_type" name="project_type" class="form-select" required>
                                    <option value="">Selecciona el tipo</option>
                                    <?php foreach ($project_types as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= old('project_type') === $value ? 'selected' : '' ?>>
                                            <?= esc($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="priority">Prioridad *</label>
                                <select id="priority" name="priority" class="form-select" required>
                                    <option value="">Selecciona la prioridad</option>
                                    <?php foreach ($priorities as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= old('priority') === $value ? 'selected' : '' ?>>
                                            <?= esc($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="department">Departamento/Facultad *</label>
                            <select id="department" name="department" class="form-select" required>
                                <option value="">Selecciona tu departamento</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= esc($dept) ?>" <?= old('department') === $dept ? 'selected' : '' ?>>
                                        <?= esc($dept) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- Contact Information -->
                <section class="card mb-xl">
                    <h2 class="card-title">📞 Información de Contacto</h2>
                    
                    <div class="space-y-lg">
                        <div class="alert alert-info">
                            <div class="flex items-start gap-sm">
                                <span class="text-lg">💡</span>
                                <div>
                                    <strong>Tip:</strong> Esta información se usará para coordinar contigo durante el desarrollo del proyecto.
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-lg">
                            <div class="form-group">
                                <label class="form-label" for="contact_email">Email de Contacto *</label>
                                <input type="email" 
                                       id="contact_email" 
                                       name="contact_email" 
                                       class="form-input"
                                       placeholder="tu.email@uc.cl"
                                       value="<?= old('contact_email', $user['email'] ?? '') ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="contact_phone">Teléfono (Opcional)</label>
                                <input type="tel" 
                                       id="contact_phone" 
                                       name="contact_phone" 
                                       class="form-input"
                                       placeholder="+56 9 1234 5678"
                                       value="<?= old('contact_phone') ?>">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Timeline and Budget -->
                <section class="card mb-xl">
                    <h2 class="card-title">⏰ Cronograma y Presupuesto</h2>
                    
                    <div class="space-y-lg">
                        <div class="grid grid-cols-2 gap-lg">
                            <div class="form-group">
                                <label class="form-label" for="estimated_completion">
                                    Fecha Objetivo de Completación
                                    <span class="text-sm text-gray-500 font-normal ml-sm">(Opcional)</span>
                                </label>
                                <input type="date" 
                                       id="estimated_completion" 
                                       name="estimated_completion" 
                                       class="form-input"
                                       value="<?= old('estimated_completion') ?>"
                                       min="<?= date('Y-m-d') ?>">
                                <div class="text-xs text-gray-500 mt-xs">
                                    Si tienes una fecha límite específica
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="budget">
                                    Presupuesto Estimado (CLP)
                                    <span class="text-sm text-gray-500 font-normal ml-sm">(Opcional)</span>
                                </label>
                                <input type="number" 
                                       id="budget" 
                                       name="budget" 
                                       class="form-input"
                                       placeholder="5000000"
                                       value="<?= old('budget') ?>"
                                       min="0"
                                       step="100000">
                                <div class="text-xs text-gray-500 mt-xs">
                                    Incluye desarrollo, infraestructura, etc.
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Special Requirements -->
                <section class="card mb-xl">
                    <h2 class="card-title">⚙️ Requisitos Especiales</h2>
                    
                    <div class="form-group">
                        <label class="form-label" for="special_requirements">
                            Requisitos Adicionales
                            <span class="text-sm text-gray-500 font-normal ml-sm">(Opcional)</span>
                        </label>
                        <textarea id="special_requirements" 
                                  name="special_requirements" 
                                  class="form-textarea" 
                                  rows="4"
                                  placeholder="Ej: Integración con sistema SAP, cumplimiento GDPR, acceso móvil, etc."><?= old('special_requirements') ?></textarea>
                        <div class="text-xs text-gray-500 mt-xs">
                            Menciona integraciones, estándares, restricciones técnicas, etc.
                        </div>
                    </div>
                </section>

                <!-- Form Actions -->
                <section class="card">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <span class="flex items-center gap-xs">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                Los campos marcados con * son obligatorios
                            </span>
                        </div>
                        <div class="flex items-center gap-md">
                            <button type="button" 
                                    onclick="resetForm()" 
                                    class="btn btn-secondary">
                                🗑️ Limpiar Formulario
                            </button>
                            <button type="submit" 
                                    class="btn btn-primary btn-lg">
                                🚀 Crear Proyecto
                            </button>
                        </div>
                    </div>
                </section>
            </form>
        </div>

        <!-- Sidebar - 1 column -->
        <div class="space-y-xl">
            
            <!-- Help Card -->
            <section class="card">
                <h3 class="card-title">❓ ¿Necesitas Ayuda?</h3>
                
                <div class="space-y-md text-sm">
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-xs">📋 Título del Proyecto</h4>
                        <p class="text-gray-600">Usa un nombre descriptivo y claro. Ej: "Portal de Notas para Estudiantes"</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-xs">📝 Descripción</h4>
                        <p class="text-gray-600">Incluye objetivos, funcionalidades principales y quiénes lo usarán.</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-xs">⏰ Prioridad</h4>
                        <ul class="text-gray-600 ml-md">
                            <li><strong>Crítica:</strong> Falla de sistema crítico</li>
                            <li><strong>Alta:</strong> Impacto significativo</li>
                            <li><strong>Media:</strong> Mejora importante</li>
                            <li><strong>Baja:</strong> Mejora menor</li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-lg pt-lg border-t border-gray-200">
                    <a href="/help" class="btn btn-outline w-full">
                        📖 Ver Guía Completa
                    </a>
                </div>
            </section>

            <!-- Process Info -->
            <section class="card">
                <h3 class="card-title">🔄 Proceso de Aprobación</h3>
                
                <div class="space-y-sm">
                    <div class="flex items-center gap-sm p-sm bg-blue-50 rounded">
                        <span class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                        <div>
                            <div class="font-medium text-blue-800">Formalización</div>
                            <div class="text-xs text-blue-600">Revisión inicial (3 días)</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-sm p-sm bg-gray-50 rounded">
                        <span class="w-6 h-6 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                        <div>
                            <div class="font-medium text-gray-700">Arquitectura</div>
                            <div class="text-xs text-gray-500">Diseño técnico (7 días)</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-sm p-sm bg-gray-50 rounded">
                        <span class="w-6 h-6 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                        <div>
                            <div class="font-medium text-gray-700">Y 7 áreas más...</div>
                            <div class="text-xs text-gray-500">Proceso completo</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-md p-md bg-green-50 rounded border border-green-200">
                    <div class="text-sm text-green-700">
                        <strong>Tiempo estimado total:</strong> 30-45 días hábiles
                    </div>
                </div>
            </section>

            <!-- Recent Projects -->
            <section class="card">
                <h3 class="card-title">📂 Tus Proyectos Recientes</h3>
                
                <div class="space-y-sm text-sm">
                    <div class="p-sm bg-gray-50 rounded">
                        <div class="font-medium text-gray-800">PROJ-2025-001</div>
                        <div class="text-gray-600">Sistema de Gestión</div>
                        <div class="text-xs text-green-600 mt-xs">✅ En progreso</div>
                    </div>
                    
                    <div class="p-sm bg-gray-50 rounded">
                        <div class="font-medium text-gray-800">PROJ-2025-002</div>
                        <div class="text-gray-600">Portal de Estudiantes</div>
                        <div class="text-xs text-blue-600 mt-xs">🔄 En revisión</div>
                    </div>
                </div>
                
                <div class="mt-md">
                    <a href="/projects" class="btn btn-outline w-full btn-sm">
                        Ver Todos los Proyectos
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
// JavaScript para crear proyecto
console.log('Crear Proyecto cargado correctamente');

// Auto-save draft functionality
let autoSaveTimer;
let formChanged = false;

function startAutoSave() {
    // Detectar cambios en el formulario
    const form = document.getElementById('project-form');
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            formChanged = true;
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(autoSaveDraft, 30000); // Auto-save cada 30 segundos
        });
    });
}

function autoSaveDraft() {
    if (formChanged) {
        saveDraft(true); // true = silencioso
        formChanged = false;
    }
}

function saveDraft(silent = false) {
    const form = document.getElementById('project-form');
    const formData = new FormData(form);
    
    // Agregar flag de borrador
    formData.append('is_draft', '1');
    
    if (!silent) {
        App.setLoading(document.getElementById('save-draft-btn'), true);
    }
    
    fetch('/projects/save-draft', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (!silent) {
                App.showNotification('Borrador guardado', 'success');
            }
            
            // Actualizar localStorage para persistencia
            localStorage.setItem('project_draft', JSON.stringify(Object.fromEntries(formData)));
        } else {
            if (!silent) {
                App.showNotification('Error al guardar borrador', 'error');
            }
        }
    })
    .catch(error => {
        if (!silent) {
            App.showNotification('Error de conexión', 'error');
        }
    })
    .finally(() => {
        if (!silent) {
            App.setLoading(document.getElementById('save-draft-btn'), false);
        }
    });
}

function resetForm() {
    if (confirm('¿Estás seguro de limpiar todo el formulario?')) {
        document.getElementById('project-form').reset();
        localStorage.removeItem('project_draft');
        App.showNotification('Formulario limpiado', 'info');
        formChanged = false;
    }
}

// Cargar borrador desde localStorage si existe
function loadDraft() {
    const savedDraft = localStorage.getItem('project_draft');
    if (savedDraft) {
        try {
            const draftData = JSON.parse(savedDraft);
            const form = document.getElementById('project-form');
            
            // Confirmar si quiere cargar el borrador
            if (confirm('Se encontró un borrador guardado. ¿Quieres cargarlo?')) {
                Object.keys(draftData).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field && draftData[key]) {
                        field.value = draftData[key];
                    }
                });
                
                App.showNotification('Borrador cargado', 'info');
            }
        } catch (e) {
            console.log('Error cargando borrador:', e);
            localStorage.removeItem('project_draft');
        }
    }
}

// Validación del formulario en tiempo real
function setupFormValidation() {
    const form = document.getElementById('project-form');
    
    // Validación del título
    const titleField = document.getElementById('title');
    titleField.addEventListener('input', function() {
        const value = this.value.trim();
        const group = this.closest('.form-group');
        let errorElement = group.querySelector('.form-error');
        
        // Remover error previo
        if (errorElement) {
            errorElement.remove();
        }
        this.classList.remove('error');
        
        if (value.length > 0 && value.length < 5) {
            this.classList.add('error');
            const error = document.createElement('div');
            error.className = 'form-error';
            error.textContent = 'El título debe tener al menos 5 caracteres';
            group.appendChild(error);
        }
    });
    
    // Validación de la descripción
    const descField = document.getElementById('description');
    descField.addEventListener('input', function() {
        const value = this.value.trim();
        const group = this.closest('.form-group');
        let errorElement = group.querySelector('.form-error');
        
        if (errorElement) {
            errorElement.remove();
        }
        this.classList.remove('error');
        
        if (value.length > 0 && value.length < 20) {
            this.classList.add('error');
            const error = document.createElement('div');
            error.className = 'form-error';
            error.textContent = 'La descripción debe tener al menos 20 caracteres';
            group.appendChild(error);
        }
    });
    
    // Contador de caracteres para descripción
    const charCounter = document.createElement('div');
    charCounter.className = 'text-xs text-gray-500 mt-xs';
    charCounter.textContent = '0 caracteres';
    descField.parentNode.appendChild(charCounter);
    
    descField.addEventListener('input', function() {
        const length = this.value.length;
        charCounter.textContent = `${length} caracteres`;
        
        if (length >= 20) {
            charCounter.classList.remove('text-red-500');
            charCounter.classList.add('text-green-600');
        } else {
            charCounter.classList.remove('text-green-600');
            charCounter.classList.add('text-red-500');
        }
    });
}

// Mejorar UX del campo de presupuesto
function setupBudgetField() {
    const budgetField = document.getElementById('budget');
    
    budgetField.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, ''); // Solo números
        if (value) {
            // Formatear con puntos de miles
            this.value = parseInt(value).toLocaleString('es-CL');
        }
    });
    
    budgetField.addEventListener('blur', function() {
        let value = this.value.replace(/\D/g, '');
        if (value) {
            this.value = value; // Guardar valor sin formato para envío
        }
    });
}

// Warning si el usuario intenta salir sin guardar
function setupUnloadWarning() {
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '¿Estás seguro de salir? Los cambios no guardados se perderán.';
        }
    });
}

// Envío del formulario
document.getElementById('project-form').addEventListener('submit', function(e) {
    formChanged = false; // Evitar warning de salida
    
    const submitBtn = this.querySelector('button[type="submit"]');
    App.setLoading(submitBtn, true);
    
    // Limpiar borrador después del envío exitoso
    setTimeout(() => {
        localStorage.removeItem('project_draft');
    }, 1000);
});

// Inicializar todo
document.addEventListener('DOMContentLoaded', function() {
    loadDraft();
    startAutoSave();
    setupFormValidation();
    setupBudgetField();
    setupUnloadWarning();
    
    // Focus en el primer campo
    document.getElementById('title').focus();
});

// Shortcuts de teclado
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 's': // Ctrl+S = Guardar borrador
                e.preventDefault();
                saveDraft();
                break;
            case 'Enter': // Ctrl+Enter = Enviar formulario
                e.preventDefault();
                document.getElementById('project-form').submit();
                break;
        }
    }
});

console.log('Shortcuts disponibles:');
console.log('Ctrl+S: Guardar borrador');
console.log('Ctrl+Enter: Enviar formulario');
<?php $this->endSection(); ?>