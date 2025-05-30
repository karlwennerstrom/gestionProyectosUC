<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ===================================================================
// RUTAS PÚBLICAS (Sin autenticación)
// ===================================================================

// Página principal
$routes->get('/', 'HomeController::index');

// Información del sistema
$routes->get('/about', 'HomeController::about');
$routes->get('/help', 'HomeController::help');
$routes->get('/status', 'HomeController::status');
$routes->get('/info', 'HomeController::info');

// ===================================================================
// RUTAS DE AUTENTICACIÓN
// ===================================================================

$routes->group('auth', function($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('process-login', 'AuthController::processLogin');
    $routes->get('logout', 'AuthController::logout');
    $routes->post('logout', 'AuthController::logout');
    $routes->get('access-denied', 'AuthController::accessDenied');
    $routes->get('check', 'AuthController::checkAuth');
});

// Rutas de logout directas (sin grupo)
$routes->get('logout', 'AuthController::logout');
$routes->post('logout', 'AuthController::logout');

// ===================================================================
// RUTAS PROTEGIDAS - USUARIOS AUTENTICADOS
// ===================================================================

$routes->group('', ['filter' => 'auth'], function($routes) {
    
    // Dashboard principal (redirecciona según tipo de usuario)
    $routes->get('dashboard', 'HomeController::dashboard');
    
    // ===================================================================
    // RUTAS DE PROYECTOS (Para usuarios normales)
    // ===================================================================
    
    $routes->group('projects', function($routes) {
        $routes->get('/', 'ProjectController::index');
        $routes->get('index', 'ProjectController::index');
        $routes->get('create', 'ProjectController::create');
        $routes->post('create', 'ProjectController::store');
        $routes->get('(:num)', 'ProjectController::show/$1');
        $routes->get('(:num)/edit', 'ProjectController::edit/$1');
        $routes->post('(:num)/edit', 'ProjectController::update/$1');
        $routes->post('(:num)/submit', 'ProjectController::submit/$1');
        $routes->delete('(:num)', 'ProjectController::delete/$1');
        $routes->post('(:num)/delete', 'ProjectController::delete/$1');
    });
    
    // ===================================================================
    // RUTAS DE DOCUMENTOS
    // ===================================================================
    
    $routes->group('documents', function($routes) {
        $routes->get('/', 'DocumentController::index');
        $routes->get('upload', 'DocumentController::upload');
        $routes->post('upload', 'DocumentController::store');
        $routes->get('(:num)', 'DocumentController::show/$1');
        $routes->get('(:num)/download', 'DocumentController::download/$1');
        $routes->delete('(:num)', 'DocumentController::delete/$1');
    });
    
    // ===================================================================
    // RUTAS DE NOTIFICACIONES
    // ===================================================================
    
    $routes->group('notifications', function($routes) {
        $routes->get('/', 'NotificationController::index');
        $routes->post('(:num)/read', 'NotificationController::markAsRead/$1');
        $routes->post('mark-all-read', 'NotificationController::markAllAsRead');
    });
    
    // ===================================================================
    // RUTAS DE REPORTES (Para usuarios normales)
    // ===================================================================
    
    $routes->group('reports', function($routes) {
        $routes->get('/', 'ReportController::index');
        $routes->get('my-projects', 'ReportController::myProjects');
        $routes->post('export', 'ReportController::export');
    });
});

// ===================================================================
// RUTAS DE ADMINISTRADOR DE ÁREA
// ===================================================================

$routes->group('admin', ['filter' => 'admin'], function($routes) {
    
    // Dashboard de administrador
    $routes->get('dashboard', 'AdminController::dashboard');
    $routes->get('', 'AdminController::dashboard'); // Ruta por defecto
    
    // Gestión de proyectos del área
    $routes->group('projects', function($routes) {
        $routes->get('/', 'AdminController::pendingProjects');
        $routes->get('pending', 'AdminController::pendingProjects');
        $routes->get('overdue', 'AdminController::overdueProjects');
        $routes->get('completed', 'AdminController::completedProjects');
        $routes->get('(:num)', 'AdminController::viewProject/$1');
        $routes->get('(:num)/review', 'AdminController::reviewProject/$1');
        $routes->post('(:num)/approve', 'AdminController::approveProject/$1');
        $routes->post('(:num)/reject', 'AdminController::rejectProject/$1');
        $routes->post('(:num)/decision', 'AdminController::saveDecision/$1');
        $routes->post('(:num)/quick-decision', 'AdminController::quickDecision/$1');
        $routes->post('(:num)/save-draft', 'AdminController::saveDraft/$1');
        $routes->post('(:num)/assign', 'AdminController::assignProject/$1');
    });
    
    // Gestión de documentos del área
    $routes->group('documents', function($routes) {
        $routes->get('/', 'AdminController::documents');
        $routes->get('pending', 'AdminController::pendingDocuments');
        $routes->get('(:num)', 'AdminController::viewDocument/$1');
        $routes->get('(:num)/review', 'AdminController::reviewDocument/$1');
        $routes->get('(:num)/preview', 'AdminController::previewDocument/$1');
        $routes->get('(:num)/download', 'AdminController::downloadDocument/$1');
        $routes->post('(:num)/approve', 'AdminController::approveDocument/$1');
        $routes->post('(:num)/reject', 'AdminController::rejectDocument/$1');
        $routes->post('export', 'AdminController::exportDocuments');
    });
    
    // Reportes del área
    $routes->group('reports', function($routes) {
        $routes->get('/', 'AdminController::reports');
        $routes->get('area-summary', 'AdminController::areaSummary');
        $routes->post('export', 'AdminController::exportAreaReport');
        $routes->get('performance', 'AdminController::performanceReport');
    });
    
    // Configuración del área
    $routes->group('settings', function($routes) {
        $routes->get('/', 'AdminController::settings');
        $routes->post('update', 'AdminController::updateSettings');
        $routes->post('notifications', 'AdminController::updateNotificationSettings');
    });
    
    // Gestión de usuarios del área
    $routes->group('users', function($routes) {
        $routes->get('/', 'AdminController::areaUsers');
        $routes->post('assign', 'AdminController::assignUser');
        $routes->post('(:num)/remove', 'AdminController::removeUser/$1');
    });
    
    // APIs para el área
    $routes->group('api', function($routes) {
        $routes->get('stats', 'AdminController::apiStats');
        $routes->get('projects', 'AdminController::apiProjects');
        $routes->get('documents', 'AdminController::apiDocuments');
        $routes->post('bulk-approve', 'AdminController::bulkApprove');
        $routes->post('bulk-reject', 'AdminController::bulkReject');
    });
});

// ===================================================================
// RUTAS DE SUPER ADMINISTRADOR
// ===================================================================

$routes->group('super-admin', ['filter' => 'super_admin'], function($routes) {
    
    // Dashboard de super admin
    $routes->get('dashboard', 'HomeController::superAdminDashboard');
    $routes->get('', 'HomeController::superAdminDashboard'); // Ruta por defecto
    
    // Gestión de usuarios
    $routes->group('users', function($routes) {
        $routes->get('/', 'SuperAdminController::users');
        $routes->get('create', 'SuperAdminController::createUser');
        $routes->post('create', 'SuperAdminController::storeUser');
        $routes->get('(:num)', 'SuperAdminController::viewUser/$1');
        $routes->get('(:num)/edit', 'SuperAdminController::editUser/$1');
        $routes->post('(:num)/edit', 'SuperAdminController::updateUser/$1');
        $routes->post('(:num)/toggle-status', 'SuperAdminController::toggleUserStatus/$1');
        $routes->delete('(:num)', 'SuperAdminController::deleteUser/$1');
        $routes->post('import', 'SuperAdminController::importUsers');
        $routes->post('export', 'SuperAdminController::exportUsers');
    });
    
    // Gestión de áreas
    $routes->group('areas', function($routes) {
        $routes->get('/', 'SuperAdminController::areas');
        $routes->get('create', 'SuperAdminController::createArea');
        $routes->post('create', 'SuperAdminController::storeArea');
        $routes->get('(:num)', 'SuperAdminController::viewArea/$1');
        $routes->get('(:num)/edit', 'SuperAdminController::editArea/$1');
        $routes->post('(:num)/edit', 'SuperAdminController::updateArea/$1');
        $routes->post('(:num)/toggle-status', 'SuperAdminController::toggleAreaStatus/$1');
        $routes->delete('(:num)', 'SuperAdminController::deleteArea/$1');
        $routes->post('reorder', 'SuperAdminController::reorderAreas');
    });
    
    // Gestión de administradores de área
    $routes->group('area-admins', function($routes) {
        $routes->get('/', 'SuperAdminController::areaAdmins');
        $routes->post('assign', 'SuperAdminController::assignAreaAdmin');
        $routes->post('(:num)/remove', 'SuperAdminController::removeAreaAdmin/$1');
        $routes->post('(:num)/update-role', 'SuperAdminController::updateAdminRole/$1');
        $routes->post('transfer', 'SuperAdminController::transferAssignments');
    });
    
    // Auditoría y logs
    $routes->group('audit', function($routes) {
        $routes->get('/', 'SuperAdminController::auditLog');
        $routes->get('export', 'SuperAdminController::exportAuditLog');
        $routes->post('clean', 'SuperAdminController::cleanOldLogs');
        $routes->get('user/(:num)', 'SuperAdminController::userAuditLog/$1');
        $routes->get('project/(:num)', 'SuperAdminController::projectAuditLog/$1');
    });
    
    // Reportes globales
    $routes->group('reports', function($routes) {
        $routes->get('/', 'SuperAdminController::reports');
        $routes->get('system-overview', 'SuperAdminController::systemOverview');
        $routes->get('user-activity', 'SuperAdminController::userActivity');
        $routes->get('project-metrics', 'SuperAdminController::projectMetrics');
        $routes->get('area-performance', 'SuperAdminController::areaPerformance');
        $routes->post('export', 'SuperAdminController::exportSystemReport');
    });
    
    // Configuración global del sistema
    $routes->group('settings', function($routes) {
        $routes->get('/', 'SuperAdminController::systemSettings');
        $routes->post('update', 'SuperAdminController::updateSystemSettings');
        $routes->post('backup', 'SuperAdminController::createBackup');
        $routes->get('backups', 'SuperAdminController::listBackups');
        $routes->post('restore', 'SuperAdminController::restoreBackup');
    });
    
    // Mantenimiento del sistema
    $routes->group('maintenance', function($routes) {
        $routes->get('/', 'SuperAdminController::maintenance');
        $routes->post('clear-cache', 'SuperAdminController::clearCache');
        $routes->post('optimize-database', 'SuperAdminController::optimizeDatabase');
        $routes->post('clean-files', 'SuperAdminController::cleanOrphanedFiles');
        $routes->post('maintenance-mode', 'SuperAdminController::toggleMaintenanceMode');
    });
    
    // Monitoreo del sistema
    $routes->group('monitoring', function($routes) {
        $routes->get('/', 'SuperAdminController::monitoring');
        $routes->get('logs', 'SuperAdminController::systemLogs');
        $routes->get('performance', 'SuperAdminController::performanceMetrics');
        $routes->get('storage', 'SuperAdminController::storageUsage');
        $routes->get('jobs', 'SuperAdminController::jobQueue');
    });
    
    // APIs para super admin
    $routes->group('api', function($routes) {
        $routes->get('metrics', 'SuperAdminController::apiMetrics');
        $routes->get('activity', 'SuperAdminController::apiActivity');
        $routes->get('health-check', 'SuperAdminController::apiHealthCheck');
        $routes->post('health-check', 'SuperAdminController::performHealthCheck');
        $routes->post('clear-cache', 'SuperAdminController::apiClearCache');
        $routes->post('optimize-database', 'SuperAdminController::apiOptimizeDatabase');
        $routes->post('generate-backup', 'SuperAdminController::apiGenerateBackup');
        $routes->post('maintenance-mode', 'SuperAdminController::apiMaintenanceMode');
        $routes->post('system-report', 'SuperAdminController::apiSystemReport');
        $routes->get('critical-alerts', 'SuperAdminController::apiCriticalAlerts');
    });
});

// ===================================================================
// RUTAS DE API PÚBLICAS
// ===================================================================

$routes->group('api', function($routes) {
    
    // Estado del sistema (público)
    $routes->get('status', 'HomeController::status');
    $routes->get('info', 'HomeController::info');
    
    // APIs protegidas (requieren autenticación)
    $routes->group('', ['filter' => 'auth'], function($routes) {
        
        // Notificaciones
        $routes->group('notifications', function($routes) {
            $routes->get('unread-count', 'NotificationController::getUnreadCount');
            $routes->get('recent', 'NotificationController::getRecent');
            $routes->post('(:num)/read', 'NotificationController::markAsRead/$1');
        });
        
        // Proyectos
        $routes->group('projects', function($routes) {
            $routes->get('/', 'ProjectController::apiIndex');
            $routes->get('(:num)', 'ProjectController::apiShow/$1');
            $routes->get('(:num)/progress', 'ProjectController::getProgress/$1');
        });
        
        // Documentos
        $routes->group('documents', function($routes) {
            $routes->get('(:num)', 'DocumentController::apiShow/$1');
            $routes->post('(:num)/approve', 'DocumentController::approve/$1');
            $routes->post('(:num)/reject', 'DocumentController::reject/$1');
        });
    });
});

// ===================================================================
// RUTAS DE DESARROLLO (Solo en entorno de desarrollo)
// ===================================================================

if (ENVIRONMENT === 'development') {
    $routes->group('dev', function($routes) {
        $routes->get('test-email', 'DevController::testEmail');
        $routes->get('test-notifications', 'DevController::testNotifications');
        $routes->get('create-test-data', 'DevController::createTestData');
        $routes->get('clear-test-data', 'DevController::clearTestData');
        $routes->get('phpinfo', 'DevController::phpinfo');
    });
}

// ===================================================================
// RUTAS DE FALLBACK Y MANEJO DE ERRORES
// ===================================================================

// Ruta para páginas no encontradas
$routes->set404Override(function() {
    $request = service('request');
    $uri = $request->getUri()->getPath();
    
    // Log del error 404
    log_message('warning', '404 Page Not Found: ' . $uri);
    
    // Si es una petición AJAX, devolver JSON
    if ($request->isAJAX()) {
        $response = service('response');
        return $response->setStatusCode(404)
                       ->setJSON(['error' => 'Endpoint not found', 'path' => $uri]);
    }
    
    // Mostrar vista de error 404 personalizada
    return view('errors/html/error_404', [
        'title' => 'Página No Encontrada - Sistema Multi-Área UC',
        'message' => 'La página que buscas no existe o ha sido movida.'
    ]);
});

// Configuración adicional de rutas
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('HomeController');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false); // Importante: desactivar auto-rutas por seguridad

// ===================================================================
// DOCUMENTACIÓN DE RUTAS
// ===================================================================

/*
RESUMEN DE RUTAS PRINCIPALES:

PÚBLICAS:
- / (página principal)
- /about (información del sistema)
- /help (centro de ayuda)
- /status (estado del sistema)
- /auth/login (iniciar sesión)

USUARIOS AUTENTICADOS:
- /dashboard (dashboard principal)
- /projects/* (gestión de proyectos)
- /documents/* (gestión de documentos)
- /notifications/* (notificaciones)
- /reports/* (reportes personales)

ADMINISTRADORES DE ÁREA:
- /admin/dashboard (dashboard de admin)
- /admin/projects/* (gestión de proyectos del área)
- /admin/documents/* (gestión de documentos del área)
- /admin/reports/* (reportes del área)
- /admin/settings/* (configuración del área)

SUPER ADMINISTRADORES:
- /super-admin/dashboard (dashboard de super admin)
- /super-admin/users/* (gestión de usuarios)
- /super-admin/areas/* (gestión de áreas)
- /super-admin/audit/* (auditoría y logs)
- /super-admin/reports/* (reportes globales)
- /super-admin/settings/* (configuración global)
- /super-admin/maintenance/* (mantenimiento del sistema)

APIS:
- /api/status (estado público del sistema)
- /api/notifications/* (APIs de notificaciones)
- /api/projects/* (APIs de proyectos)
- /api/documents/* (APIs de documentos)

Todas las rutas están protegidas por filtros de autenticación apropiados.
*/