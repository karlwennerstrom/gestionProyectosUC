<nav class="navbar" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
    <div class="navbar-content">
        <div class="navbar-brand">
            🏛️ Sistema Multi-Área UC - Administrador
        </div>
        <div class="navbar-user">
            <div class="user-badge">
                <span class="status-indicator"></span>
                <?= esc($user['full_name'] ?? 'Administrador') ?>
            </div>
            <a href="/logout" class="btn btn-secondary btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>