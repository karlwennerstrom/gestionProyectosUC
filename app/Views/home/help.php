<?php
// app/Views/home/help.php
$this->extend('layouts/main');
$this->section('content');
?>

<div class="container">
    <!-- Hero Section -->
    <section class="text-center mb-2xl">
        <h1 class="text-4xl font-bold text-gray-800 mb-md">Centro de Ayuda</h1>
        <p class="text-xl text-gray-500">Encuentra respuestas a tus preguntas sobre el Sistema Multi-Área UC</p>
    </section>

    <!-- Search Bar -->
    <section class="card mb-2xl">
        <div class="text-center">
            <h2 class="text-2xl font-semibold mb-lg">¿En qué podemos ayudarte?</h2>
            <div class="form-group">
                <input type="text" id="help-search" class="form-input" 
                       placeholder="Buscar en preguntas frecuentes..." 
                       style="max-width: 600px; margin: 0 auto;">
            </div>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="mb-2xl">
        <h2 class="text-2xl font-semibold text-center mb-xl">Acceso Rápido</h2>
        <div class="grid grid-cols-4 gap-lg">
            <a href="#getting-started" class="action-card">
                <div class="action-icon">🚀</div>
                <div class="action-title">Primeros Pasos</div>
                <div class="action-description">Cómo empezar a usar el sistema</div>
            </a>
            <a href="#projects" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-title">Gestión de Proyectos</div>
                <div class="action-description">Crear y administrar proyectos</div>
            </a>
            <a href="#documents" class="action-card">
                <div class="action-icon">📎</div>
                <div class="action-title">Documentos</div>
                <div class="action-description">Subir y gestionar archivos</div>
            </a>
            <a href="#troubleshooting" class="action-card">
                <div class="action-icon">🔧</div>
                <div class="action-title">Solución de Problemas</div>
                <div class="action-description">Resolver errores comunes</div>
            </a>
        </div>
    </section>

    <!-- FAQ Sections -->
    <div class="grid grid-cols-1 gap-xl">
        
        <!-- Getting Started -->
        <section id="getting-started" class="card">
            <h2 class="card-title">🚀 Primeros Pasos</h2>
            
            <div class="faq-item mb-lg">
                <h3 class="text-lg font-semibold mb-md text-primary">¿Cómo accedo al sistema?</h3>
                <p class="text-gray-600 mb-md">
                    El sistema utiliza autenticación centralizada de la Universidad Católica. 
                    Simplemente haz clic en "Iniciar Sesión" y usa tus credenciales institucionales.
                </p>
                <div class="alert alert-info">
                    <strong>Nota:</strong> Solo usuarios con email @uc.cl pueden acceder al sistema.
                </div>
            </div>

            <div class="faq-item mb-lg">
                <h3 class="text-lg font-semibold mb-md text-primary">¿Qué tipos de usuario existen?</h3>
                <ul class="text-gray-600 ml-lg">
                    <li><strong>Usuario:</strong> Puede crear y gestionar sus propios proyectos</li>
                    <li><strong>Administrador de Área:</strong> Puede revisar proyectos asignados a su área</li>
                    <li><strong>Super Administrador:</strong> Acceso completo al sistema</li>
                </ul>
            </div>

            <div class="faq-item">
                <h3 class="text-lg font-semibold mb-md text-primary">¿Dónde veo mis proyectos?</h3>
                <p class="text-gray-600">
                    Una vez autenticado, serás redirigido a tu dashboard personal donde podrás ver 
                    todos tus proyectos, su estado actual y acciones disponibles.
                </p>
            </div>
        </section>

        <!-- Project Management -->
        <section id="projects" class="card">
            <h2 class="card-title">📋 Gestión de Proyectos</h2>
            
            <?php foreach ($faqs as $faq): ?>
            <div class="faq-item mb-lg">
                <h3 class="text-lg font-semibold mb-md text-primary"><?= esc($faq['question']) ?></h3>
                <p class="text-gray-600"><?= esc($faq['answer']) ?></p>
            </div>
            <?php endforeach; ?>

            <div class="faq-item mb-lg">
                <h3 class="text-lg font-semibold mb-md text-primary">¿Cuáles son los estados de un proyecto?</h3>
                <div class="grid grid-cols-2 gap-md">
                    <div>
                        <ul class="text-gray-600">
                            <li><span class="text-gray-500">•</span> <strong>Borrador:</strong> Proyecto en creación</li>
                            <li><span class="text-success">•</span> <strong>Enviado:</strong> Esperando revisión</li>
                            <li><span class="text-warning">•</span> <strong>En Progreso:</strong> Siendo procesado</li>
                            <li><span class="text-info">•</span> <strong>En Pausa:</strong> Temporalmente detenido</li>
                        </ul>
                    </div>
                    <div>
                        <ul class="text-gray-600">
                            <li><span class="text-success">•</span> <strong>Completado:</strong> Finalizado exitosamente</li>
                            <li><span class="text-error">•</span> <strong>Rechazado:</strong> No aprobado</li>
                            <li><span class="text-gray-500">•</span> <strong>Cancelado:</strong> Cancelado por el usuario</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <h3 class="text-lg font-semibold mb-md text-primary">¿Cuánto tiempo toma cada área?</h3>
                <p class="text-gray-600 mb-md">Los tiempos estimados por área son:</p>
                <div class="grid grid-cols-3 gap-sm text-sm">
                    <div>Formalización: 3 días</div>
                    <div>Arquitectura: 7 días</div>
                    <div>Infraestructura: 5 días</div>
                    <div>Seguridad: 4 días</div>
                    <div>Base de Datos: 6 días</div>
                    <div>Integraciones: 8 días</div>
                    <div>Ambientes: 3 días</div>
                    <div>JCPS: 5 días</div>
                    <div>Monitoreo: 2 días</div>
                </div>
            </div>
        </section>

        <!-- Documents -->
        <section id="documents" class="card">
            <h2 class="card-title">📎 Gestión de Documentos</h2>
            
            <div class="faq-item mb-lg">
                <h3 class="text-lg font-semibold mb-md text-primary">¿Qué tipos de documentos puedo subir?</h3>
                <ul class="text-gray-600 ml-lg">
                    <li>Ficha de Formalización (obligatorio)</li>
                    <li>Especificación Técnica</li>
                    <li>Diagrama de Arquitectura</li>
                    <li>Manual de Usuario</li>
                    <li>Documentación Técnica</li>
                    <li>Plan de Pruebas</li>
                    <li>Certificado de Seguridad</li>
                </ul>
            </div>

            <div class="faq-item mb-lg">
                <h3 class="text-lg font-semibold mb-md text-primary">¿Qué formatos de archivo están permitidos?</h3>
                <p class="text-gray-600">
                    Se aceptan archivos PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT y ZIP 
                    con un tamaño máximo de 50MB por archivo.
                </p>
            </div>

            <div class="faq-item">
                <h3 class="text-lg font-semibold mb-md text-primary">¿Puedo reemplazar un documento?</h3>
                <p class="text-gray-600">
                    Sí, puedes subir una nueva versión del documento. El sistema mantendrá 
                    un historial de versiones y marcará la más reciente como la versión actual.
                </p>
            </div>
        </section>

        <!-- Troubleshooting -->
        <section id="troubleshooting" class="card">
            <h2 class="card-title">🔧 Solución de Problemas</h2>
            
            <div class="faq-item mb-lg">
                <h3 class="text-lg font-semibold mb-md text-primary">No puedo iniciar sesión</h3>
                <ol class="text-gray-600 ml-lg">
                    <li>Verifica que estés usando tu email institucional (@uc.cl)</li>
                    <li>Asegúrate de que tu contraseña sea correcta</li>
                    <li>Limpia la caché y cookies de tu navegador</li>
                    <li>Si el problema persiste, contacta al soporte técnico</li>
                </ol>
            </div>

            <div class="faq-item mb-lg">
                <h3 class="text-lg font-semibold mb-md text-primary">Error al subir archivos</h3>
                <ul class="text-gray-600 ml-lg">
                    <li>Verifica que el archivo no exceda los 50MB</li>
                    <li>Asegúrate de que el formato sea compatible</li>
                    <li>Intenta con una conexión a internet más estable</li>
                    <li>Si usas VPN, intenta desconectarla temporalmente</li>
                </ul>
            </div>

            <div class="faq-item">
                <h3 class="text-lg font-semibold mb-md text-primary">Mi proyecto no avanza</h3>
                <p class="text-gray-600 mb-md">Si tu proyecto está detenido:</p>
                <ul class="text-gray-600 ml-lg">
                    <li>Revisa si tienes documentos pendientes por subir</li>
                    <li>Verifica que no haya observaciones sin resolver</li>
                    <li>Contacta al administrador del área correspondiente</li>
                    <li>Si es urgente, escríbenos a soporte-multiarea@uc.cl</li>
                </ul>
            </div>
        </section>
    </div>

    <!-- Contact Information -->
    <section class="card mt-2xl text-center">
        <h2 class="card-title">📞 ¿Necesitas más ayuda?</h2>
        <p class="text-gray-600 mb-lg">
            Si no encontraste la respuesta que buscabas, no dudes en contactarnos
        </p>
        
        <div class="grid grid-cols-3 gap-lg">
            <div>
                <div class="text-2xl mb-md">📧</div>
                <h3 class="font-semibold mb-sm">Email</h3>
                <p class="text-gray-600"><?= esc($contact_info['email']) ?></p>
            </div>
            <div>
                <div class="text-2xl mb-md">📞</div>
                <h3 class="font-semibold mb-sm">Teléfono</h3>
                <p class="text-gray-600"><?= esc($contact_info['phone']) ?></p>
            </div>
            <div>
                <div class="text-2xl mb-md">🕐</div>
                <h3 class="font-semibold mb-sm">Horario</h3>
                <p class="text-gray-600"><?= esc($contact_info['hours']) ?></p>
            </div>
        </div>

        <div class="mt-xl">
            <?php if ($user): ?>
                <a href="/dashboard" class="btn btn-primary btn-lg">Volver al Dashboard</a>
            <?php else: ?>
                <a href="/" class="btn btn-primary btn-lg">Ir al Inicio</a>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
// JavaScript específico para la página de ayuda
console.log('Página de Ayuda cargada correctamente');

// Funcionalidad de búsqueda en FAQ
const searchInput = document.getElementById('help-search');
const faqItems = document.querySelectorAll('.faq-item');

searchInput.addEventListener('input', App.utils.debounce(function(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    faqItems.forEach(item => {
        const question = item.querySelector('h3').textContent.toLowerCase();
        const answer = item.querySelector('p, ul, ol').textContent.toLowerCase();
        
        if (question.includes(searchTerm) || answer.includes(searchTerm)) {
            item.style.display = 'block';
            item.parentElement.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
    
    // Ocultar secciones que no tienen items visibles
    document.querySelectorAll('section').forEach(section => {
        const visibleItems = section.querySelectorAll('.faq-item[style*="block"], .faq-item:not([style])');
        if (visibleItems.length === 0 && section.querySelector('.faq-item')) {
            section.style.display = 'none';
        } else {
            section.style.display = 'block';
        }
    });
}, 300));

// Smooth scroll para enlaces internos
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Highlight de términos de búsqueda
function highlightSearchTerms(searchTerm) {
    if (!searchTerm) return;
    
    faqItems.forEach(item => {
        const elements = item.querySelectorAll('h3, p, li');
        elements.forEach(el => {
            const text = el.textContent;
            const highlightedText = text.replace(
                new RegExp(searchTerm, 'gi'),
                `<mark style="background: #fef08a; padding: 1px 2px; border-radius: 2px;">$&</mark>`
            );
            if (text !== highlightedText) {
                el.innerHTML = highlightedText;
            }
        });
    });
}

// Limpiar highlights cuando se borra la búsqueda
searchInput.addEventListener('input', function(e) {
    if (!e.target.value) {
        faqItems.forEach(item => {
            const marks = item.querySelectorAll('mark');
            marks.forEach(mark => {
                mark.outerHTML = mark.textContent;
            });
            item.style.display = 'block';
        });
        document.querySelectorAll('section').forEach(section => {
            section.style.display = 'block';
        });
    }
});
<?php $this->endSection(); ?>