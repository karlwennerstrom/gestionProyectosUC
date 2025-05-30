<?php
// app/Views/home/about.php
$this->extend('layouts/main');
$this->section('content');
?>

<div class="container">
    <!-- Hero Section -->
    <section class="text-center mb-2xl">
        <h1 class="text-4xl font-bold text-gray-800 mb-md">Acerca del Sistema Multi-Área UC</h1>
        <p class="text-xl text-gray-500 mb-sm"><?= esc($description) ?></p>
        <div class="version-badge">Versión <?= esc($version) ?></div>
    </section>

    <!-- Content Grid -->
    <div class="grid grid-cols-2 gap-xl mb-2xl">
        <!-- Descripción del Sistema -->
        <div class="card">
            <h2 class="card-title">📋 ¿Qué es el Sistema Multi-Área?</h2>
            <p class="text-gray-600 mb-lg"><?= esc($description) ?></p>
            
            <h3 class="text-xl font-semibold mb-md">Objetivo Principal</h3>
            <p class="text-gray-600">
                Centralizar y automatizar el proceso de gestión de proyectos de desarrollo tecnológico, 
                facilitando la coordinación entre las diferentes áreas especializadas de la Universidad Católica.
            </p>
        </div>

        <!-- Características Principales -->
        <div class="card">
            <h2 class="card-title">✨ Características Principales</h2>
            <ul class="features-list">
                <?php foreach ($features as $feature): ?>
                    <li><?= esc($feature) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Estadísticas del Sistema -->
    <section class="card mb-2xl">
        <h2 class="card-title text-center mb-xl">📊 Estadísticas del Sistema</h2>
        <div class="grid grid-cols-4 gap-lg text-center">
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-number">9</div>
                <div class="stat-label">Áreas Especializadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🕒</div>
                <div class="stat-number">24/7</div>
                <div class="stat-label">Disponibilidad</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔒</div>
                <div class="stat-number">100%</div>
                <div class="stat-label">Seguro</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🚀</div>
                <div class="stat-number"><?= esc($version) ?></div>
                <div class="stat-label">Versión Actual</div>
            </div>
        </div>
    </section>

    <!-- Información del Usuario -->
    <?php if ($user): ?>
    <section class="card mb-2xl">
        <h2 class="card-title">👤 Tu Información</h2>
        <div class="flex items-center gap-lg">
            <div class="user-badge text-lg">
                <span class="status-indicator"></span>
                <?= esc($user['full_name']) ?>
            </div>
            <div class="text-gray-600">
                <p><strong>Tipo de Usuario:</strong> <?= esc($user['user_type']) ?></p>
                <p><strong>Acceso:</strong> Autorizado</p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Áreas del Sistema -->
    <section class="card mb-2xl">
        <h2 class="card-title">🏛️ Áreas Especializadas</h2>
        <div class="grid grid-cols-3 gap-md">
            <?php 
            $areas = [
                ['name' => 'Formalización', 'color' => '#3B82F6', 'icon' => '📋'],
                ['name' => 'Arquitectura', 'color' => '#10B981', 'icon' => '🏗️'],
                ['name' => 'Infraestructura', 'color' => '#F59E0B', 'icon' => '🖥️'],
                ['name' => 'Seguridad', 'color' => '#EF4444', 'icon' => '🔒'],
                ['name' => 'Base de Datos', 'color' => '#8B5CF6', 'icon' => '🗄️'],
                ['name' => 'Integraciones', 'color' => '#06B6D4', 'icon' => '🔗'],
                ['name' => 'Ambientes', 'color' => '#84CC16', 'icon' => '⚙️'],
                ['name' => 'JCPS', 'color' => '#F97316', 'icon' => '🎓'],
                ['name' => 'Monitoreo', 'color' => '#EC4899', 'icon' => '📊']
            ];
            ?>
            <?php foreach ($areas as $area): ?>
                <div class="action-card" style="border-left: 4px solid <?= $area['color'] ?>">
                    <div class="action-icon"><?= $area['icon'] ?></div>
                    <div class="action-title"><?= $area['name'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Tecnologías Utilizadas -->
    <section class="card mb-2xl">
        <h2 class="card-title">💻 Tecnologías Utilizadas</h2>
        <div class="grid grid-cols-2 gap-lg">
            <div>
                <h3 class="text-lg font-semibold mb-md">Backend</h3>
                <ul class="text-gray-600">
                    <li>• CodeIgniter 4</li>
                    <li>• PHP 8.1+</li>
                    <li>• MySQL/MariaDB</li>
                    <li>• RESTful APIs</li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-md">Frontend</h3>
                <ul class="text-gray-600">
                    <li>• HTML5 + CSS3</li>
                    <li>• JavaScript ES6+</li>
                    <li>• Responsive Design</li>
                    <li>• Progressive Enhancement</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Enlaces de Acción -->
    <section class="text-center">
        <div class="flex justify-center gap-lg">
            <?php if (!$user): ?>
                <a href="/auth/login" class="btn btn-primary btn-lg">
                    🔐 Iniciar Sesión
                </a>
            <?php else: ?>
                <a href="/dashboard" class="btn btn-primary btn-lg">
                    🏠 Ir al Dashboard
                </a>
            <?php endif; ?>
            <a href="/help" class="btn btn-secondary btn-lg">
                ❓ Centro de Ayuda
            </a>
            <a href="/status" class="btn btn-outline btn-lg">
                📊 Estado del Sistema
            </a>
        </div>
    </section>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
// JavaScript específico para la página About
console.log('Página About cargada correctamente');

// Animar estadísticas al hacer scroll
const observerOptions = {
    threshold: 0.5
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observar elementos para animación
document.querySelectorAll('.stat-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'all 0.6s ease';
    observer.observe(card);
});

// Animación de contadores
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace(/\D/g, ''));
        const duration = 2000;
        const start = performance.now();
        
        function updateCounter(currentTime) {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            const current = Math.floor(progress * target);
            
            if (counter.textContent.includes('%')) {
                counter.textContent = current + '%';
            } else if (counter.textContent.includes('/')) {
                counter.textContent = '24/7';
            } else {
                counter.textContent = current;
            }
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        }
        
        requestAnimationFrame(updateCounter);
    });
}

// Activar animaciones cuando la sección sea visible
const statsSection = document.querySelector('.grid.grid-cols-4');
if (statsSection) {
    observer.observe(statsSection);
    statsSection.addEventListener('intersect', animateCounters, { once: true });
}
<?php $this->endSection(); ?>