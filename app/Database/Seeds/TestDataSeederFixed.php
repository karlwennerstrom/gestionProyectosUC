<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder para datos de prueba del sistema - CORREGIDO
 * Sistema Multi-Área Universidad Católica
 */
class TestDataSeederFixed extends Seeder
{
    public function run()
    {
        echo "Iniciando seeder de datos de prueba corregido...\n";

        // // Crear usuarios de prueba
        // $this->createTestUsers();
        
        // // Asignar administradores a áreas
        // $this->assignAreaAdmins();
        
        // Crear proyectos de prueba
        $this->createTestProjects();
        
        // Crear fases de proyecto
        $this->createProjectPhases();
        
        // Crear documentos de ejemplo
        $this->createTestDocuments();
        
        // Crear notificaciones de prueba
        $this->createTestNotifications();
        
        // Crear logs de auditoría de ejemplo
        $this->createTestAuditLogs();

        echo "Seeder de datos de prueba completado!\n";
    }

    /**
     * Crear usuarios de prueba
     */
    private function createTestUsers()
    {
        echo "Creando usuarios de prueba...\n";

        $users = [
            // Super Administrador adicional
            [
                'email'     => 'superadmin@uc.cl',
                'full_name' => 'Ana María González',
                'user_type' => 'super_admin',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Administradores de Área
            [
                'email'     => 'carlos.mendoza@uc.cl',
                'full_name' => 'Dr. Carlos Mendoza',
                'user_type' => 'admin',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'ana.torres@uc.cl',
                'full_name' => 'Ing. Ana Torres',
                'user_type' => 'admin',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-3 hours')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'luis.garcia@uc.cl',
                'full_name' => 'Ing. Luis García',
                'user_type' => 'admin',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'carmen.lopez@uc.cl',
                'full_name' => 'DBA Carmen López',
                'user_type' => 'admin',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-4 hours')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'mario.vargas@uc.cl',
                'full_name' => 'Ing. Mario Vargas',
                'user_type' => 'admin',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-6 hours')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'david.chen@uc.cl',
                'full_name' => 'Ing. David Chen',
                'user_type' => 'admin',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'patricia.soto@uc.cl',
                'full_name' => 'Ing. Patricia Soto',
                'user_type' => 'admin',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'ricardo.flores@uc.cl',
                'full_name' => 'Ing. Ricardo Flores',
                'user_type' => 'admin',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-3 hours')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

            // Usuarios Normales
            [
                'email'     => 'juan.perez@uc.cl',
                'full_name' => 'Juan Pérez Rodríguez',
                'user_type' => 'user',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'maria.gonzalez@uc.cl',
                'full_name' => 'María González Silva',
                'user_type' => 'user',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'carlos.silva@uc.cl',
                'full_name' => 'Carlos Silva Morales',
                'user_type' => 'user',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'elena.martinez@uc.cl',
                'full_name' => 'Elena Martínez Vega',
                'user_type' => 'user',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-4 hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'roberto.sanchez@uc.cl',
                'full_name' => 'Roberto Sánchez López',
                'user_type' => 'user',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-6 hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email'     => 'sofia.herrera@uc.cl',
                'full_name' => 'Sofía Herrera Rojas',
                'user_type' => 'user',
                'status'    => 'active',
                'last_login' => date('Y-m-d H:i:s', strtotime('-12 hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($users);
        echo "✓ Usuarios creados: " . count($users) . "\n";
    }

    /**
     * Asignar administradores a áreas
     */
    private function assignAreaAdmins()
    {
        echo "Asignando administradores a áreas...\n";

        $assignments = [
            // Área de Arquitectura - Carlos Mendoza
            ['area_id' => 2, 'user_id' => 3, 'role' => 'admin', 'can_assign' => true],
            
            // Área de Infraestructura - Ana Torres  
            ['area_id' => 3, 'user_id' => 4, 'role' => 'admin', 'can_assign' => true],
            
            // Área de Seguridad - Luis García
            ['area_id' => 4, 'user_id' => 5, 'role' => 'admin', 'can_assign' => true],
            
            // Área de Base de Datos - Carmen López
            ['area_id' => 5, 'user_id' => 6, 'role' => 'admin', 'can_assign' => true],
            
            // Área de Integraciones - Mario Vargas
            ['area_id' => 6, 'user_id' => 7, 'role' => 'admin', 'can_assign' => true],
            
            // Área de Ambientes - David Chen
            ['area_id' => 7, 'user_id' => 8, 'role' => 'admin', 'can_assign' => true],
            
            // Área de JCPS - Patricia Soto
            ['area_id' => 8, 'user_id' => 9, 'role' => 'admin', 'can_assign' => true],
            
            // Área de Monitoreo - Ricardo Flores
            ['area_id' => 9, 'user_id' => 10, 'role' => 'admin', 'can_assign' => true],
        ];

        // Agregar timestamps
        foreach ($assignments as &$assignment) {
            $assignment['created_at'] = date('Y-m-d H:i:s');
            $assignment['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->db->table('area_admins')->insertBatch($assignments);
        echo "✓ Administradores asignados: " . count($assignments) . "\n";
    }

    /**
     * Crear proyectos de prueba - CORREGIDO
     */
    private function createTestProjects()
    {
        echo "Creando proyectos de prueba...\n";

        $projects = [
            [
                'code' => 'PROJ-2025-001',
                'title' => 'Sistema ERP Académico Integrado',
                'description' => 'Desarrollo de sistema integral para la gestión académica que incluye matrícula, notas, horarios y reportes administrativos.',
                'requester_id' => 11, // Juan Pérez
                'current_area_id' => 2, // Arquitectura
                'priority' => 'high',
                'status' => 'in_progress',
                'completion_percentage' => 35.50,
                'estimated_completion' => date('Y-m-d', strtotime('+60 days')),
                'actual_completion' => null,
                'budget' => 150000.00,
                'department' => 'Facultad de Ingeniería',
                'contact_email' => 'juan.perez@uc.cl',
                'contact_phone' => '+56 9 8765 4321',
                'additional_info' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-13 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ],
            [
                'code' => 'PROJ-2025-002',
                'title' => 'Portal de Estudiantes v2.0',
                'description' => 'Actualización completa del portal de estudiantes con nueva interfaz y funcionalidades móviles.',
                'requester_id' => 12, // María González
                'current_area_id' => 2, // Arquitectura
                'priority' => 'medium',
                'status' => 'in_progress',
                'completion_percentage' => 15.00,
                'estimated_completion' => date('Y-m-d', strtotime('+45 days')),
                'actual_completion' => null,
                'budget' => 80000.00,
                'department' => 'Dirección de Asuntos Estudiantiles',
                'contact_email' => 'maria.gonzalez@uc.cl',
                'contact_phone' => '+56 9 7654 3210',
                'additional_info' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            ],
            [
                'code' => 'PROJ-2025-003',
                'title' => 'API Gestión de Bibliotecas',
                'description' => 'Desarrollo de API REST para integración del sistema de bibliotecas con plataformas externas.',
                'requester_id' => 13, // Carlos Silva
                'current_area_id' => null, // Completado
                'priority' => 'low',
                'status' => 'completed',
                'completion_percentage' => 100.00,
                'estimated_completion' => date('Y-m-d', strtotime('-5 days')),
                'actual_completion' => date('Y-m-d', strtotime('-3 days')),
                'budget' => 45000.00,
                'department' => 'Sistema de Bibliotecas',
                'contact_email' => 'carlos.silva@uc.cl',
                'contact_phone' => '+56 9 6543 2109',
                'additional_info' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            [
                'code' => 'PROJ-2025-004',
                'title' => 'Sistema de Evaluación Docente',
                'description' => 'Plataforma web para evaluación de desempeño docente por parte de estudiantes y pares académicos.',
                'requester_id' => 14, // Elena Martínez
                'current_area_id' => 4, // Seguridad
                'priority' => 'high',
                'status' => 'in_progress',
                'completion_percentage' => 60.75,
                'estimated_completion' => date('Y-m-d', strtotime('+30 days')),
                'actual_completion' => null,
                'budget' => 95000.00,
                'department' => 'Vicerrectoría Académica',
                'contact_email' => 'elena.martinez@uc.cl',
                'contact_phone' => '+56 9 5432 1098',
                'additional_info' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-25 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-4 hours')),
            ],
            [
                'code' => 'PROJ-2025-005',
                'title' => 'App Móvil Campus Virtual',
                'description' => 'Aplicación móvil nativa para acceso al campus virtual con notificaciones push y sincronización offline.',
                'requester_id' => 15, // Roberto Sánchez
                'current_area_id' => 6, // Integraciones
                'priority' => 'medium',
                'status' => 'in_progress',
                'completion_percentage' => 45.25,
                'estimated_completion' => date('Y-m-d', strtotime('+75 days')),
                'actual_completion' => null,
                'budget' => 120000.00,
                'department' => 'Dirección de Tecnologías',
                'contact_email' => 'roberto.sanchez@uc.cl',
                'contact_phone' => '+56 9 4321 0987',
                'additional_info' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-18 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-6 hours')),
            ],
            [
                'code' => 'PROJ-2025-006',
                'title' => 'Dashboard de Métricas Institucionales',
                'description' => 'Tablero de control ejecutivo con métricas en tiempo real para la toma de decisiones estratégicas.',
                'requester_id' => 16, // Sofía Herrera
                'current_area_id' => 1, // Formalización
                'priority' => 'medium',
                'status' => 'submitted',
                'completion_percentage' => 5.00,
                'estimated_completion' => date('Y-m-d', strtotime('+90 days')),
                'actual_completion' => null,
                'budget' => 200000.00,
                'department' => 'Rectoría',
                'contact_email' => 'sofia.herrera@uc.cl',
                'contact_phone' => '+56 9 3210 9876',
                'additional_info' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
        ];

        $this->db->table('projects')->insertBatch($projects);
        echo "✓ Proyectos creados: " . count($projects) . "\n";
    }

    /**
     * Crear fases de proyecto - SIMPLIFICADO
     */
    private function createProjectPhases()
    {
        echo "Creando fases de proyecto...\n";

        // Crear solo algunas fases básicas para evitar errores
        $phases = [
            // Proyecto 1 - ERP Académico
            [
                'project_id' => 1,
                'area_id' => 1, // Formalización
                'phase_order' => 1,
                'status' => 'completed',
                'assigned_to' => null,
                'estimated_hours' => 16.00,
                'actual_hours' => 14.50,
                'started_at' => date('Y-m-d H:i:s', strtotime('-12 days')),
                'completed_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'due_date' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-13 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'project_id' => 1,
                'area_id' => 2, // Arquitectura
                'phase_order' => 2,
                'status' => 'in_progress',
                'assigned_to' => 3, // Carlos Mendoza
                'estimated_hours' => 24.00,
                'actual_hours' => null,
                'started_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'completed_at' => null,
                'due_date' => date('Y-m-d H:i:s', strtotime('+14 days')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-13 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Proyecto 3 - API Bibliotecas (completado)
            [
                'project_id' => 3,
                'area_id' => 9, // Monitoreo (última fase)
                'phase_order' => 9,
                'status' => 'completed',
                'assigned_to' => 10, // Ricardo Flores
                'estimated_hours' => 8.00,
                'actual_hours' => 6.50,
                'started_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'completed_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'due_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('project_phases')->insertBatch($phases);
        echo "✓ Fases creadas: " . count($phases) . "\n";
    }

    /**
     * Crear documentos de ejemplo - SIMPLIFICADO
     */
    private function createTestDocuments()
    {
        echo "Creando documentos de ejemplo...\n";

        $documents = [
            [
                'project_id' => 1,
                'phase_id' => 1,
                'user_id' => 11, // Juan Pérez
                'document_type' => 'ficha_formalizacion',
                'original_name' => 'Ficha_Formalizacion_ERP.pdf',
                'file_name' => 'doc_' . uniqid() . '.pdf',
                'file_path' => 'uploads/documents/2025/05/',
                'file_size' => 2048576,
                'mime_type' => 'application/pdf',
                'file_hash' => hash('sha256', 'sample_content_1'),
                'version' => 1,
                'status' => 'approved',
                'reviewed_by' => 3, // Carlos Mendoza
                'reviewed_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'rejection_reason' => null,
                'reviewer_comments' => 'Documento aprobado. Cumple con todos los requisitos de formalización.',
                'is_required' => true,
                'is_latest' => true,
                'download_count' => 5,
                'uploaded_at' => date('Y-m-d H:i:s', strtotime('-12 days')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-12 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            ],
        ];

        $this->db->table('documents')->insertBatch($documents);
        echo "✓ Documentos creados: " . count($documents) . "\n";
    }

    /**
     * Crear notificaciones de prueba - SIMPLIFICADO
     */
    private function createTestNotifications()
    {
        echo "Creando notificaciones de prueba...\n";

        $notifications = [
            [
                'user_id' => 3, // Carlos Mendoza
                'project_id' => 1,
                'area_id' => 2,
                'type' => 'document_uploaded',
                'title' => 'Nuevo documento subido - PROJ-2025-001',
                'message' => 'Juan Pérez ha subido la especificación técnica del Sistema ERP Académico para revisión.',
                'action_url' => '/admin/projects/1/review',
                'action_text' => 'Revisar Documento',
                'priority' => 'high',
                'read_status' => false,
                'read_at' => null,
                'email_sent' => true,
                'email_sent_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'email_error' => null,
                'data' => null,
                'expires_at' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
        ];

        $this->db->table('notifications')->insertBatch($notifications);
        echo "✓ Notificaciones creadas: " . count($notifications) . "\n";
    }

    /**
     * Crear logs de auditoría de ejemplo - SIMPLIFICADO
     */
    private function createTestAuditLogs()
    {
        echo "Creando logs de auditoría...\n";

        $logs = [
            [
                'user_id' => 11,
                'project_id' => 1,
                'area_id' => null,
                'action' => 'project_created',
                'entity_type' => 'project',
                'entity_id' => 1,
                'description' => 'Usuario creó nuevo proyecto: Sistema ERP Académico Integrado',
                'old_values' => null,
                'new_values' => json_encode(['code' => 'PROJ-2025-001', 'title' => 'Sistema ERP Académico Integrado']),
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'session_id' => session_id(),
                'risk_level' => 'low',
                'success' => true,
                'error_message' => null,
                'execution_time' => 0.245,
                'created_at' => date('Y-m-d H:i:s', strtotime('-13 days')),
            ],
        ];

        $this->db->table('audit_log')->insertBatch($logs);
        echo "✓ Logs de auditoría creados: " . count($logs) . "\n";
    }
}