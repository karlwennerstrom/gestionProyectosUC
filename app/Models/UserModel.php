<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Usuarios
 * Sistema Multi-Área Universidad Católica
 */
class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'email',
        'full_name',
        'user_type',
        'status',
        'last_login',
        'login_attempts'
    ];

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validación básica
    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'full_name' => 'required|min_length[3]|max_length[255]',
        'user_type' => 'required|in_list[super_admin,admin,user]',
        'status' => 'required|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'email' => [
            'required' => 'El email es obligatorio',
            'valid_email' => 'Debe ser un email válido',
            'is_unique' => 'Este email ya está registrado'
        ],
        'full_name' => [
            'required' => 'El nombre completo es obligatorio',
            'min_length' => 'El nombre debe tener al menos 3 caracteres'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Buscar usuario por email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Obtener usuarios activos
     */
    public function getActiveUsers(): array
    {
        return $this->where('status', 'active')->findAll();
    }

    /**
     * Obtener usuarios por tipo
     */
    public function getUsersByType(string $type): array
    {
        return $this->where('user_type', $type)
                   ->where('status', 'active')
                   ->findAll();
    }

    /**
     * Obtener super administradores
     */
    public function getSuperAdmins(): array
    {
        return $this->getUsersByType('super_admin');
    }

    /**
     * Obtener administradores de área
     */
    public function getAreaAdmins(): array
    {
        return $this->getUsersByType('admin');
    }

    /**
     * Obtener usuarios normales
     */
    public function getNormalUsers(): array
    {
        return $this->getUsersByType('user');
    }

    /**
     * Actualizar último login
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s'),
            'login_attempts' => 0
        ]);
    }

    /**
     * Incrementar intentos de login
     */
    public function incrementLoginAttempts(int $userId): bool
    {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }

        return $this->update($userId, [
            'login_attempts' => $user['login_attempts'] + 1
        ]);
    }

    /**
     * Verificar si el usuario está bloqueado por intentos
     */
    public function isLockedOut(int $userId): bool
    {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }

        return $user['login_attempts'] >= 5;
    }

    /**
     * Activar/desactivar usuario
     */
    public function toggleStatus(int $userId): bool
    {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }

        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        return $this->update($userId, ['status' => $newStatus]);
    }

    /**
     * Buscar usuarios con filtros
     */
    public function searchUsers(array $filters = []): array
    {
        $builder = $this->builder();

        // Filtro por texto (busca en nombre y email)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                   ->like('full_name', $search)
                   ->orLike('email', $search)
                   ->groupEnd();
        }

        // Filtro por tipo de usuario
        if (!empty($filters['user_type'])) {
            $builder->where('user_type', $filters['user_type']);
        }

        // Filtro por estado
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'full_name';
        $orderDir = $filters['order_dir'] ?? 'ASC';
        $builder->orderBy($orderBy, $orderDir);

        // Límite
        if (!empty($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function getUserStats(): array
    {
        return [
            'total' => $this->countAll(),
            'active' => $this->where('status', 'active')->countAllResults(),
            'inactive' => $this->where('status', 'inactive')->countAllResults(),
            'super_admins' => $this->where('user_type', 'super_admin')->countAllResults(),
            'admins' => $this->where('user_type', 'admin')->countAllResults(),
            'users' => $this->where('user_type', 'user')->countAllResults(),
            'recent_logins' => $this->where('last_login >', date('Y-m-d H:i:s', strtotime('-24 hours')))
                                   ->countAllResults()
        ];
    }

    /**
     * Obtener usuarios recientes
     */
    public function getRecentUsers(int $limit = 10): array
    {
        return $this->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Validar si un email está autorizado (dominio UC)
     */
    public function isAuthorizedEmail(string $email): bool
    {
        // Por ahora solo emails @uc.cl
        return str_ends_with(strtolower($email), '@uc.cl');
    }

    /**
     * Crear usuario desde CAS
     */
    public function createFromCAS(array $casData): ?int
    {
        // Validar email autorizado
        if (!$this->isAuthorizedEmail($casData['email'])) {
            return null;
        }

        $userData = [
            'email' => strtolower($casData['email']),
            'full_name' => $casData['full_name'] ?? 'Usuario CAS',
            'user_type' => 'user', // Por defecto
            'status' => 'active',
            'last_login' => date('Y-m-d H:i:s'),
            'login_attempts' => 0
        ];

        if ($this->insert($userData)) {
            return $this->getInsertID();
        }

        return null;
    }

    /**
     * Obtener información completa del usuario con áreas asignadas
     */
    public function getUserWithAreas(int $userId): ?array
    {
        $user = $this->find($userId);
        if (!$user) {
            return null;
        }

        // Si es admin, obtener áreas asignadas
        if ($user['user_type'] === 'admin') {
            $areaAdminModel = new \App\Models\AreaAdminModel();
            $user['assigned_areas'] = $areaAdminModel->getUserAreas($userId);
        } else {
            $user['assigned_areas'] = [];
        }

        return $user;
    }
}