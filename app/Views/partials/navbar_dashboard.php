<nav class="navbar">
    <div class="navbar-content">
        <div class="navbar-brand">
            🏛️ Sistema Multi-Área UC
        </div>
        <div class="navbar-user">
            <div class="user-badge">
                <span class="status-indicator"></span>
                <?= esc($user['full_name'] ?? 'Usuario') ?>
            </div>
            <a href="/logout" class="btn btn-secondary btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>