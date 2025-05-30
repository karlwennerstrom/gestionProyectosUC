<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema Multi-Área UC' ?></title>
    <meta name="description" content="<?= $description ?? 'Sistema de gestión de proyectos Universidad Católica' ?>">
    
    <!-- CSS Principal -->
    <link rel="stylesheet" href="/css/main.css">
    
    <!-- CSS Adicional específico de la página -->
    <?php if (isset($additional_css) && is_array($additional_css)): ?>
        <?php foreach ($additional_css as $css_file): ?>
            <link rel="stylesheet" href="<?= $css_file ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Meta tags adicionales -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#3b82f6">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Variables PHP para JavaScript -->
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= base_url() ?>',
            environment: '<?= ENVIRONMENT ?>',
            user: <?= isset($user) ? json_encode($user) : 'null' ?>,
            csrf_token: '<?= csrf_hash() ?>',
            csrf_name: '<?= csrf_token() ?>'
        };
    </script>
</head>
<body>
    <!-- Header/Navbar Section -->
    <?php if (!isset($hide_header) || !$hide_header): ?>
        <?php if (isset($navbar_type)): ?>
            <?php switch($navbar_type): 
                case 'dashboard': ?>
                    <?= $this->include('partials/navbar_dashboard') ?>
                    <?php break; ?>
                <?php case 'admin': ?>
                    <?= $this->include('partials/navbar_admin') ?>
                    <?php break; ?>
                <?php case 'super_admin': ?>
                    <?= $this->include('partials/navbar_super_admin') ?>
                    <?php break; ?>
                <?php default: ?>
                    <?= $this->include('partials/navbar_public') ?>
            <?php endswitch; ?>
        <?php else: ?>
            <?= $this->include('partials/navbar_public') ?>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Alerts/Messages Section -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <div class="container">
                <?= session()->getFlashdata('success') ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error">
            <div class="container">
                <?= session()->getFlashdata('error') ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning">
            <div class="container">
                <?= session()->getFlashdata('warning') ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info">
            <div class="container">
                <?= session()->getFlashdata('info') ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer Section -->
    <?php if (!isset($hide_footer) || !$hide_footer): ?>
        <?= $this->include('partials/footer') ?>
    <?php endif; ?>

    <!-- JavaScript Principal -->
    <script src="/js/main.js"></script>
    
    <!-- JavaScript adicional específico de la página -->
    <?php if (isset($additional_js) && is_array($additional_js)): ?>
        <?php foreach ($additional_js as $js_file): ?>
            <script src="<?= $js_file ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- JavaScript inline de la página -->
    <?php if ($this->renderSection('scripts')): ?>
        <script>
            <?= $this->renderSection('scripts') ?>
        </script>
    <?php endif; ?>

    <!-- Auto-hide alerts después de 5 segundos -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);
        });
    </script>
</body>
</html>