<!-- app/Views/partials/navbar_public.php -->
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                🏛️ Sistema Multi-Área UC
                <?php if (ENVIRONMENT === 'development'): ?>
                    <span class="user-badge">DEV</span>
                <?php endif; ?>
            </div>
            <nav class="nav-links">
                <span class="status-indicator"></span>
                <a href="/about">Acerca de</a>
                <a href="/help">Ayuda</a>
                <a href="/status">Estado</a>
                <?php if (!session()->has('user_authenticated')): ?>
                    <a href="/auth/login" class="btn btn-primary">Iniciar Sesión</a>
                <?php else: ?>
                    <a href="/dashboard" class="btn btn-primary">Dashboard</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>