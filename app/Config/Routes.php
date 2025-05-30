<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Cargar rutas por defecto del sistema
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false); // Deshabilitar auto-routing por seguridad

/*
 * --------------------------------------------------------------------
 * Rutas Básicas del Sistema Multi-Área UC
 * --------------------------------------------------------------------
 */

// ============================================================================
// RUTAS PÚBLICAS (sin autenticación)
// ============================================================================

// Página principal
$routes->get('/', 'HomeController::index', ['as' => 'home']);
$routes->get('/home', 'HomeController::index');

// Login temporal (mientras no tengamos CAS completo)
$routes->get('/auth/login', 'AuthController::login', ['as' => 'auth.login']);
$routes->post('/auth/process-login', 'AuthController::processLogin', ['as' => 'auth.process']);
$routes->get('/auth/logout', 'AuthController::logout', ['as' => 'auth.logout']);
$routes->get('/auth/check', 'AuthController::checkAuth', ['as' => 'auth.check']);
$routes->get('/auth/access-denied', 'AuthController::accessDenied', ['as' => 'auth.access_denied']);

// Rutas de logout alternativas
$routes->get('/logout', 'AuthController::logout');

// Páginas informativas
$routes->get('/about', 'HomeController::about', ['as' => 'about']);
$routes->get('/help', 'HomeController::help', ['as' => 'help']);
$routes->get('/status', 'HomeController::status', ['as' => 'status']);
$routes->get('/info', 'HomeController::info', ['as' => 'info']);

// ============================================================================
// RUTAS DE USUARIO AUTENTICADO
// ============================================================================

// Dashboard principal
$routes->get('/dashboard', 'HomeController::dashboard', ['as' => 'dashboard', 'filter' => 'auth']);

// Dashboards específicos (temporales - usan HomeController)
$routes->get('/super-admin/dashboard', 'HomeController::superAdminDashboard', ['as' => 'super_admin.dashboard', 'filter' => 'super_admin']);
$routes->get('/admin/dashboard', 'HomeController::adminDashboard', ['as' => 'admin.dashboard', 'filter' => 'admin']);

// ============================================================================
// RUTAS DE DESARROLLO (solo en desarrollo)
// ============================================================================

if (ENVIRONMENT === 'development') {
    $routes->get('/dev/info', 'HomeController::info');
    $routes->get('/dev/status', 'HomeController::status');
}

/*
 * --------------------------------------------------------------------
 * Configuración adicional de rutas
 * --------------------------------------------------------------------
 */

// Rutas de redirección simple
$routes->addRedirect('docs', 'help');
$routes->addRedirect('documentation', 'help');

// ============================================================================
// DEBUG DE RUTAS (solo en desarrollo)
// ============================================================================

if (ENVIRONMENT === 'development') {
    // Mostrar todas las rutas registradas
    $routes->get('routes-debug', function() {
        $collection = \Config\Services::routes();
        $routes = $collection->getRoutes();
        
        echo '<h1>Rutas Registradas</h1>';
        echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
        echo '<tr><th>Método</th><th>Ruta</th><th>Controlador</th></tr>';
        
        foreach ($routes as $route => $handler) {
            echo '<tr>';
            echo '<td>GET</td>';
            echo '<td>' . htmlspecialchars($route) . '</td>';
            echo '<td>' . htmlspecialchars($handler) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        echo '<p><a href="/">← Volver al inicio</a></p>';
    });
}