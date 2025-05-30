<nav class="navbar" style="background: linear-gradient(135deg, #7c2d12 0%, #dc2626 100%);">
    <div class="navbar-content">
        <div class="navbar-brand">
            🏛️ Sistema Multi-Área UC - Super Admin
        </div>
        <div class="navbar-user">
            <div class="user-badge">
                <span class="status-indicator"></span>
                <?= esc($user['full_name'] ?? 'Super Administrador') ?>
            </div>
            <a href="/logout" class="btn btn-secondary btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>