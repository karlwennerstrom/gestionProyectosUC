<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Áreas
 * Sistema Multi-Área Universidad Católica
 */
class AreaModel extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'name',
        'description',
        'color',
        'icon',
        'order_position',
        'is_active',
        'requires_documents',
        'estimated_days',
        'notification_email'
    ];

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = null;

    // Validación
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[areas.name,id,{id}]',
        'description' => 'required|min_length[10]|max_length[255]',
        'color' => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
        'order_position' => 'required|integer|greater_than[0]',
        'is_active' => 'required|in_list[0,1]',
        'estimated_days' => 'permit_empty|integer|greater_than[0]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'El nombre del área es obligatorio',
            'is_unique' => 'Ya existe un área con este nombre'
        ],
        'color' => [
            'regex_match' => 'El color debe ser un código hexadecimal válido (#RRGGBB)'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Obtener áreas activas ordenadas por posición
     */
    public function getActiveAreas(): array
    {
        return $this->where('is_active', 1)
                   ->orderBy('order_position', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener área por nombre
     */
    public function getByName(string $name): ?array
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Obtener siguiente área en el flujo
     */
    public function getNextArea(int $currentAreaId): ?array
    {
        $currentArea = $this->find($currentAreaId);
        if (!$currentArea) {
            return null;
        }

        return $this->where('is_active', 1)
                   ->where('order_position >', $currentArea['order_position'])
                   ->orderBy('order_position', 'ASC')
                   ->first();
    }

    /**
     * Obtener área anterior en el flujo
     */
    public function getPreviousArea(int $currentAreaId): ?array
    {
        $currentArea = $this->find($currentAreaId);
        if (!$currentArea) {
            return null;
        }

        return $this->where('is_active', 1)
                   ->where('order_position <', $currentArea['order_position'])
                   ->orderBy('order_position', 'DESC')
                   ->first();
    }

    /**
     * Reordenar áreas
     */
    public function reorderAreas(array $areaIds): bool
    {
        $this->db->transStart();

        foreach ($areaIds as $position => $areaId) {
            $this->update($areaId, ['order_position' => $position + 1]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Activar/desactivar área
     */
    public function toggleStatus(int $areaId): bool
    {
        $area = $this->find($areaId);
        if (!$area) {
            return false;
        }

        $newStatus = $area['is_active'] ? 0 : 1;
        return $this->update($areaId, ['is_active' => $newStatus]);
    }

    /**
     * Obtener estadísticas de área
     */
    public function getAreaStats(int $areaId): array
    {
        // Cargar modelo de fases para obtener estadísticas
        $phaseModel = new \App\Models\ProjectPhaseModel();
        
        return [
            'total_projects' => $phaseModel->where('area_id', $areaId)->countAllResults(),
            'pending_projects' => $phaseModel->where('area_id', $areaId)
                                            ->where('status', 'pending')
                                            ->countAllResults(),
            'in_progress_projects' => $phaseModel->where('area_id', $areaId)
                                                ->where('status', 'in_progress')
                                                ->countAllResults(),
            'completed_projects' => $phaseModel->where('area_id', $areaId)
                                              ->where('status', 'completed')
                                              ->countAllResults(),
            'avg_completion_days' => $this->getAverageCompletionDays($areaId)
        ];
    }

    /**
     * Obtener promedio de días de completación para un área
     */
    public function getAverageCompletionDays(int $areaId): float
    {
        $phaseModel = new \App\Models\ProjectPhaseModel();
        
        $builder = $phaseModel->builder();
        $builder->select('AVG(DATEDIFF(completed_at, started_at)) as avg_days')
               ->where('area_id', $areaId)
               ->where('status', 'completed')
               ->where('started_at IS NOT NULL')
               ->where('completed_at IS NOT NULL');
               
        $result = $builder->get()->getRow();
        return $result ? (float)$result->avg_days : 0.0;
    }

    /**
     * Buscar áreas con filtros
     */
    public function searchAreas(array $filters = []): array
    {
        $builder = $this->builder();

        // Filtro por texto
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                   ->like('name', $search)
                   ->orLike('description', $search)
                   ->groupEnd();
        }

        // Filtro por estado
        if (isset($filters['is_active'])) {
            $builder->where('is_active', $filters['is_active']);
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'order_position';
        $orderDir = $filters['order_dir'] ?? 'ASC';
        $builder->orderBy($orderBy, $orderDir);

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener áreas con información de administradores
     */
    public function getAreasWithAdmins(): array
    {
        $areas = $this->getActiveAreas();
        $areaAdminModel = new \App\Models\AreaAdminModel();
        
        foreach ($areas as &$area) {
            $area['admins'] = $areaAdminModel->getAreaAdmins($area['id']);
            $area['admin_count'] = count($area['admins']);
        }
        
        return $areas;
    }

    /**
     * Validar si se puede eliminar un área
     */
    public function canDelete(int $areaId): array
    {
        $issues = [];
        
        // Verificar si tiene proyectos asignados
        $phaseModel = new \App\Models\ProjectPhaseModel();
        $projectCount = $phaseModel->where('area_id', $areaId)->countAllResults();
        
        if ($projectCount > 0) {
            $issues[] = "El área tiene {$projectCount} proyectos asignados";
        }
        
        // Verificar si tiene administradores asignados
        $areaAdminModel = new \App\Models\AreaAdminModel();
        $adminCount = $areaAdminModel->where('area_id', $areaId)->countAllResults();
        
        if ($adminCount > 0) {
            $issues[] = "El área tiene {$adminCount} administradores asignados";
        }

        return [
            'can_delete' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Crear área por defecto del sistema
     */
    public function createDefaultAreas(): bool
    {
        $defaultAreas = [
            [
                'name' => 'Formalización',
                'description' => 'Revisión y aprobación de la documentación inicial del proyecto',
                'color' => '#3B82F6',
                'icon' => 'document-text',
                'order_position' => 1,
                'is_active' => 1,
                'requires_documents' => 1,
                'estimated_days' => 3,
                'notification_email' => 'formalizacion@uc.cl'
            ],
            [
                'name' => 'Arquitectura',
                'description' => 'Diseño de la arquitectura técnica y revisión de especificaciones',
                'color' => '#10B981',
                'icon' => 'cube',
                'order_position' => 2,
                'is_active' => 1,
                'requires_documents' => 1,
                'estimated_days' => 7,
                'notification_email' => 'arquitectura@uc.cl'
            ],
            [
                'name' => 'Infraestructura',
                'description' => 'Provisión y configuración de recursos de infraestructura',
                'color' => '#F59E0B',
                'icon' => 'server',
                'order_position' => 3,
                'is_active' => 1,
                'requires_documents' => 0,
                'estimated_days' => 5,
                'notification_email' => 'infraestructura@uc.cl'
            ],
            [
                'name' => 'Seguridad',
                'description' => 'Análisis de seguridad y cumplimiento de políticas',
                'color' => '#EF4444',
                'icon' => 'shield-check',
                'order_position' => 4,
                'is_active' => 1,
                'requires_documents' => 1,
                'estimated_days' => 4,
                'notification_email' => 'seguridad@uc.cl'
            ],
            [
                'name' => 'Base de Datos',
                'description' => 'Diseño, creación y configuración de bases de datos',
                'color' => '#8B5CF6',
                'icon' => 'database',
                'order_position' => 5,
                'is_active' => 1,
                'requires_documents' => 1,
                'estimated_days' => 6,
                'notification_email' => 'bd@uc.cl'
            ],
            [
                'name' => 'Integraciones',
                'description' => 'Desarrollo de integraciones con sistemas externos',
                'color' => '#06B6D4',
                'icon' => 'link',
                'order_position' => 6,
                'is_active' => 1,
                'requires_documents' => 0,
                'estimated_days' => 8,
                'notification_email' => 'integraciones@uc.cl'
            ],
            [
                'name' => 'Ambientes',
                'description' => 'Configuración de ambientes de desarrollo, testing y producción',
                'color' => '#84CC16',
                'icon' => 'cog',
                'order_position' => 7,
                'is_active' => 1,
                'requires_documents' => 0,
                'estimated_days' => 3,
                'notification_email' => 'ambientes@uc.cl'
            ],
            [
                'name' => 'JCPS',
                'description' => 'Revisión de Jefatura de Carrera de Pregrado y Postgrado',
                'color' => '#F97316',
                'icon' => 'academic-cap',
                'order_position' => 8,
                'is_active' => 1,
                'requires_documents' => 1,
                'estimated_days' => 5,
                'notification_email' => 'jcps@uc.cl'
            ],
            [
                'name' => 'Monitoreo',
                'description' => 'Implementación de monitoreo y alertas del sistema',
                'color' => '#EC4899',
                'icon' => 'chart-bar',
                'order_position' => 9,
                'is_active' => 1,
                'requires_documents' => 0,
                'estimated_days' => 2,
                'notification_email' => 'monitoreo@uc.cl'
            ]
        ];

        return $this->insertBatch($defaultAreas) !== false;
    }
}