<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Log de Auditoría
 * Sistema Multi-Área Universidad Católica
 */
class AuditLogModel extends Model
{
    protected $table = 'audit_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'user_id',
        'project_id',
        'area_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'session_id',
        'risk_level',
        'success',
        'error_message',
        'execution_time'
    ];

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null; // Solo created_at
    protected $deletedField = null;

    // Validación básica
    protected $validationRules = [
        'action' => 'required|max_length[100]',
        'description' => 'required',
        'risk_level' => 'required|in_list[low,medium,high,critical]',
        'success' => 'required|in_list[0,1]'
    ];

    protected $validationMessages = [
        'action' => [
            'required' => 'La acción es obligatoria'
        ],
        'description' => [
            'required' => 'La descripción es obligatoria'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Registrar evento de auditoría
     */
    public function logEvent(array $eventData): bool
    {
        // Datos básicos del evento
        $logData = [
            'action' => $eventData['action'],
            'description' => $eventData['description'],
            'ip_address' => $this->getClientIP(),
            'user_agent' => $this->getUserAgent(),
            'session_id' => session_id(),
            'risk_level' => $eventData['risk_level'] ?? 'low',
            'success' => $eventData['success'] ?? true,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Campos opcionales
        $optionalFields = [
            'user_id', 'project_id', 'area_id', 'entity_type', 
            'entity_id', 'old_values', 'new_values', 'error_message', 
            'execution_time'
        ];

        foreach ($optionalFields as $field) {
            if (isset($eventData[$field])) {
                $logData[$field] = $eventData[$field];
            }
        }

        return $this->insert($logData) !== false;
    }

    /**
     * Obtener logs por usuario
     */
    public function getLogsByUser(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Obtener logs por proyecto
     */
    public function getLogsByProject(int $projectId, int $limit = 50): array
    {
        return $this->where('project_id', $projectId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Obtener logs por tipo de acción
     */
    public function getLogsByAction(string $action, int $limit = 100): array
    {
        return $this->where('action', $action)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Obtener logs con riesgo alto
     */
    public function getHighRiskLogs(int $limit = 100): array
    {
        return $this->whereIn('risk_level', ['high', 'critical'])
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Obtener logs fallidos
     */
    public function getFailedLogs(int $limit = 100): array
    {
        return $this->where('success', false)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Buscar logs con filtros
     */
    public function searchLogs(array $filters = []): array
    {
        $builder = $this->builder();

        // Filtro por usuario
        if (!empty($filters['user_id'])) {
            $builder->where('user_id', $filters['user_id']);
        }

        // Filtro por proyecto
        if (!empty($filters['project_id'])) {
            $builder->where('project_id', $filters['project_id']);
        }

        // Filtro por acción
        if (!empty($filters['action'])) {
            $builder->where('action', $filters['action']);
        }

        // Filtro por nivel de riesgo
        if (!empty($filters['risk_level'])) {
            $builder->where('risk_level', $filters['risk_level']);
        }

        // Filtro por éxito/fallo
        if (isset($filters['success'])) {
            $builder->where('success', $filters['success']);
        }

        // Filtro por rango de fechas
        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to']);
        }

        // Filtro por búsqueda de texto
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                   ->like('description', $search)
                   ->orLike('action', $search)
                   ->groupEnd();
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';
        $builder->orderBy($orderBy, $orderDir);

        // Límite
        $limit = $filters['limit'] ?? 100;
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener estadísticas de auditoría
     */
    public function getAuditStats(int $days = 30): array
    {
        $dateFrom = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return [
            'total_events' => $this->where('created_at >=', $dateFrom)->countAllResults(),
            'successful_events' => $this->where('created_at >=', $dateFrom)
                                       ->where('success', true)
                                       ->countAllResults(),
            'failed_events' => $this->where('created_at >=', $dateFrom)
                                   ->where('success', false)
                                   ->countAllResults(),
            'high_risk_events' => $this->where('created_at >=', $dateFrom)
                                      ->whereIn('risk_level', ['high', 'critical'])
                                      ->countAllResults(),
            'unique_users' => $this->select('COUNT(DISTINCT user_id) as count')
                                  ->where('created_at >=', $dateFrom)
                                  ->where('user_id IS NOT NULL')
                                  ->get()
                                  ->getRow()
                                  ->count ?? 0,
            'most_common_actions' => $this->getMostCommonActions($dateFrom)
        ];
    }

    /**
     * Obtener acciones más comunes
     */
    private function getMostCommonActions(string $dateFrom, int $limit = 10): array
    {
        return $this->select('action, COUNT(*) as count')
                   ->where('created_at >=', $dateFrom)
                   ->groupBy('action')
                   ->orderBy('count', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Limpiar logs antiguos
     */
    public function cleanOldLogs(int $daysToKeep = 365): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        
        // Solo eliminar logs de bajo riesgo
        $builder = $this->builder();
        $builder->where('created_at <', $cutoffDate)
               ->where('risk_level', 'low');
               
        return $builder->delete();
    }

    /**
     * Obtener IP del cliente
     */
    private function getClientIP(): string
    {
        $request = service('request');
        
        // Verificar diferentes headers para obtener la IP real
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        
        return $request->getIPAddress();
    }

    /**
     * Obtener User Agent
     */
    private function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    /**
     * Registrar login exitoso
     */
    public function logSuccessfulLogin(int $userId, string $email): bool
    {
        return $this->logEvent([
            'user_id' => $userId,
            'action' => 'login_success',
            'description' => "Usuario {$email} inició sesión exitosamente",
            'risk_level' => 'low',
            'success' => true
        ]);
    }

    /**
     * Registrar login fallido
     */
    public function logFailedLogin(string $email, string $reason = ''): bool
    {
        return $this->logEvent([
            'action' => 'login_failed',
            'description' => "Intento de login fallido para {$email}. Motivo: {$reason}",
            'risk_level' => 'medium',
            'success' => false,
            'error_message' => $reason
        ]);
    }

    /**
     * Registrar logout
     */
    public function logLogout(int $userId, string $email): bool
    {
        return $this->logEvent([
            'user_id' => $userId,
            'action' => 'logout',
            'description' => "Usuario {$email} cerró sesión",
            'risk_level' => 'low',
            'success' => true
        ]);
    }
}