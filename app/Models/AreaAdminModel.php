<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Administradores de Área
 * Sistema Multi-Área Universidad Católica
 */
class AreaAdminModel extends Model
{
    protected $table = 'area_admins';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'area_id',
        'user_id',
        'role',
        'can_assign',
        'assigned_by',
        'assigned_at',
        'is_active'
    ];

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = null;

    // Validación
    protected $validationRules = [
        'area_id' => 'required|integer|is_not_unique[areas.id]',
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'role' => 'required|in_list[admin,reviewer,viewer]',
        'can_assign' => 'required|in_list[0,1]',
        'is_active' => 'required|in_list[0,1]'
    ];

    protected $validationMessages = [
        'area_id' => [
            'is_not_unique' => 'El área especificada no existe'
        ],
        'user_id' => [
            'is_not_unique' => 'El usuario especificado no existe'
        ],
        'role' => [
            'in_list' => 'El rol debe ser: admin, reviewer o viewer'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Asignar usuario a área
     */
    public function assignUserToArea(int $userId, int $areaId, string $role = 'admin', bool $canAssign = false, ?int $assignedBy = null): bool
    {
        // Verificar si ya existe la asignación
        $existing = $this->where('user_id', $userId)
                        ->where('area_id', $areaId)
                        ->first();

        if ($existing) {
            // Actualizar asignación existente
            return $this->update($existing['id'], [
                'role' => $role,
                'can_assign' => $canAssign ? 1 : 0,
                'assigned_by' => $assignedBy,
                'assigned_at' => date('Y-m-d H:i:s'),
                'is_active' => 1
            ]);
        }

        // Crear nueva asignación
        return $this->insert([
            'area_id' => $areaId,
            'user_id' => $userId,
            'role' => $role,
            'can_assign' => $canAssign ? 1 : 0,
            'assigned_by' => $assignedBy,
            'assigned_at' => date('Y-m-d H:i:s'),
            'is_active' => 1
        ]) !== false;
    }

    /**
     * Remover usuario de área
     */
    public function removeUserFromArea(int $userId, int $areaId): bool
    {
        return $this->where('user_id', $userId)
                   ->where('area_id', $areaId)
                   ->delete();
    }

    /**
     * Desactivar asignación (sin eliminar)
     */
    public function deactivateAssignment(int $userId, int $areaId): bool
    {
        return $this->where('user_id', $userId)
                   ->where('area_id', $areaId)
                   ->set(['is_active' => 0])
                   ->update();
    }

    /**
     * Obtener áreas asignadas a un usuario
     */
    public function getUserAreas(int $userId): array
    {
        return $this->select('area_admins.*, areas.name as area_name, areas.description as area_description, areas.color as area_color, areas.icon as area_icon')
                   ->join('areas', 'areas.id = area_admins.area_id')
                   ->where('area_admins.user_id', $userId)
                   ->where('area_admins.is_active', 1)
                   ->where('areas.is_active', 1)
                   ->orderBy('areas.order_position', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener administradores de un área
     */
    public function getAreaAdmins(int $areaId): array
    {
        return $this->select('area_admins.*, users.full_name, users.email, users.user_type')
                   ->join('users', 'users.id = area_admins.user_id')
                   ->where('area_admins.area_id', $areaId)
                   ->where('area_admins.is_active', 1)
                   ->where('users.status', 'active')
                   ->orderBy('area_admins.role', 'ASC')
                   ->orderBy('users.full_name', 'ASC')
                   ->findAll();
    }

    /**
     * Verificar si un usuario es admin de un área
     */
    public function isUserAreaAdmin(int $userId, int $areaId): bool
    {
        $assignment = $this->where('user_id', $userId)
                          ->where('area_id', $areaId)
                          ->where('role', 'admin')
                          ->where('is_active', 1)
                          ->first();

        return !empty($assignment);
    }

    /**
     * Verificar si un usuario puede asignar en un área
     */
    public function canUserAssign(int $userId, int $areaId): bool
    {
        $assignment = $this->where('user_id', $userId)
                          ->where('area_id', $areaId)
                          ->where('can_assign', 1)
                          ->where('is_active', 1)
                          ->first();

        return !empty($assignment);
    }

    /**
     * Obtener todas las asignaciones con información completa
     */
    public function getAllAssignments(array $filters = []): array
    {
        $builder = $this->select('area_admins.*, users.full_name, users.email, areas.name as area_name, areas.color as area_color')
                       ->join('users', 'users.id = area_admins.user_id')
                       ->join('areas', 'areas.id = area_admins.area_id');

        // Filtros
        if (!empty($filters['area_id'])) {
            $builder->where('area_admins.area_id', $filters['area_id']);
        }

        if (!empty($filters['user_id'])) {
            $builder->where('area_admins.user_id', $filters['user_id']);
        }

        if (!empty($filters['role'])) {
            $builder->where('area_admins.role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $builder->where('area_admins.is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                   ->like('users.full_name', $search)
                   ->orLike('users.email', $search)
                   ->orLike('areas.name', $search)
                   ->groupEnd();
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'areas.order_position';
        $orderDir = $filters['order_dir'] ?? 'ASC';
        $builder->orderBy($orderBy, $orderDir);

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener estadísticas de asignaciones
     */
    public function getAssignmentStats(): array
    {
        return [
            'total_assignments' => $this->where('is_active', 1)->countAllResults(),
            'total_admins' => $this->where('role', 'admin')->where('is_active', 1)->countAllResults(),
            'total_reviewers' => $this->where('role', 'reviewer')->where('is_active', 1)->countAllResults(),
            'users_with_assignments' => $this->select('COUNT(DISTINCT user_id) as count')
                                           ->where('is_active', 1)
                                           ->get()
                                           ->getRow()
                                           ->count ?? 0,
            'areas_with_admins' => $this->select('COUNT(DISTINCT area_id) as count')
                                       ->where('is_active', 1)
                                       ->get()
                                       ->getRow()
                                       ->count ?? 0,
            'users_can_assign' => $this->where('can_assign', 1)->where('is_active', 1)->countAllResults()
        ];
    }

    /**
     * Transferir todas las asignaciones de un usuario a otro
     */
    public function transferUserAssignments(int $fromUserId, int $toUserId, ?int $transferredBy = null): bool
    {
        $this->db->transStart();

        // Obtener asignaciones del usuario original
        $assignments = $this->where('user_id', $fromUserId)
                           ->where('is_active', 1)
                           ->findAll();

        foreach ($assignments as $assignment) {
            // Desactivar asignación original
            $this->update($assignment['id'], ['is_active' => 0]);

            // Crear nueva asignación para el usuario destino
            $this->assignUserToArea(
                $toUserId,
                $assignment['area_id'],
                $assignment['role'],
                $assignment['can_assign'],
                $transferredBy
            );
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Obtener áreas sin administradores asignados
     */
    public function getAreasWithoutAdmins(): array
    {
        $areaModel = new \App\Models\AreaModel();
        
        return $areaModel->select('areas.*')
                        ->join('area_admins', 'area_admins.area_id = areas.id AND area_admins.is_active = 1', 'left')
                        ->where('areas.is_active', 1)
                        ->where('area_admins.id IS NULL')
                        ->orderBy('areas.order_position', 'ASC')
                        ->findAll();
    }

    /**
     * Obtener usuarios elegibles para ser administradores de área
     */
    public function getEligibleUsers(): array
    {
        $userModel = new \App\Models\UserModel();
        
        return $userModel->whereIn('user_type', ['admin', 'super_admin'])
                        ->where('status', 'active')
                        ->orderBy('full_name', 'ASC')
                        ->findAll();
    }

    /**
     * Clonar asignaciones de un área a otra
     */
    public function cloneAreaAssignments(int $fromAreaId, int $toAreaId, ?int $clonedBy = null): bool
    {
        $assignments = $this->where('area_id', $fromAreaId)
                           ->where('is_active', 1)
                           ->findAll();

        $this->db->transStart();

        foreach ($assignments as $assignment) {
            $this->assignUserToArea(
                $assignment['user_id'],
                $toAreaId,
                $assignment['role'],
                $assignment['can_assign'],
                $clonedBy
            );
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Actualizar rol de usuario en área
     */
    public function updateUserRole(int $userId, int $areaId, string $newRole, bool $canAssign = false): bool
    {
        return $this->where('user_id', $userId)
                   ->where('area_id', $areaId)
                   ->set([
                       'role' => $newRole,
                       'can_assign' => $canAssign ? 1 : 0
                   ])
                   ->update();
    }

    /**
     * Obtener historial de asignaciones de un usuario
     */
    public function getUserAssignmentHistory(int $userId): array
    {
        return $this->select('area_admins.*, areas.name as area_name, assigned_by_user.full_name as assigned_by_name')
                   ->join('areas', 'areas.id = area_admins.area_id')
                   ->join('users as assigned_by_user', 'assigned_by_user.id = area_admins.assigned_by', 'left')
                   ->where('area_admins.user_id', $userId)
                   ->orderBy('area_admins.assigned_at', 'DESC')
                   ->findAll();
    }

    /**
     * Validar si se puede eliminar una asignación
     */
    public function canRemoveAssignment(int $userId, int $areaId): array
    {
        $issues = [];
        
        // Verificar si es el único admin del área
        $adminCount = $this->where('area_id', $areaId)
                          ->where('role', 'admin')
                          ->where('is_active', 1)
                          ->countAllResults();

        if ($adminCount <= 1) {
            $issues[] = 'No se puede eliminar el único administrador del área';
        }

        // Verificar si tiene proyectos asignados actualmente
        $phaseModel = new \App\Models\ProjectPhaseModel();
        $activeProjects = $phaseModel->where('area_id', $areaId)
                                   ->where('assigned_to', $userId)
                                   ->whereIn('status', ['pending', 'in_progress'])
                                   ->countAllResults();

        if ($activeProjects > 0) {
            $issues[] = "El usuario tiene {$activeProjects} proyectos activos asignados";
        }

        return [
            'can_remove' => empty($issues),
            'issues' => $issues
        ];
    }
}