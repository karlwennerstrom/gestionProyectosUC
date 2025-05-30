<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Notificaciones
 * Sistema Multi-Área Universidad Católica
 */
class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'user_id',
        'project_id',
        'area_id',
        'type',
        'title',
        'message',
        'action_url',
        'action_text',
        'priority',
        'read_status',
        'read_at',
        'email_sent',
        'email_sent_at',
        'email_error',
        'data',
        'expires_at'
    ];

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = null;

    // Validación
    protected $validationRules = [
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'type' => 'required|in_list[info,success,warning,error,project_created,project_updated,project_completed,project_rejected,document_uploaded,document_approved,document_rejected,phase_assigned,phase_completed,deadline_reminder,system_maintenance]',
        'title' => 'required|min_length[3]|max_length[255]',
        'message' => 'required|min_length[5]',
        'priority' => 'required|in_list[low,normal,high,urgent]',
        'read_status' => 'required|in_list[0,1]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'is_not_unique' => 'El usuario especificado no existe'
        ],
        'type' => [
            'in_list' => 'Tipo de notificación no válido'
        ],
        'priority' => [
            'in_list' => 'Prioridad debe ser: low, normal, high, urgent'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert = ['sendEmailNotification'];

    /**
     * Enviar email después de insertar notificación
     */
    protected function sendEmailNotification(array $data): array
    {
        if (isset($data['id'])) {
            $notification = $this->find($data['id']);
            if ($notification && in_array($notification['priority'], ['high', 'urgent'])) {
                $this->queueEmailNotification($notification['id']);
            }
        }
        return $data;
    }

    /**
     * Crear notificación
     */
    public function createNotification(array $notificationData): ?int
    {
        // Preparar datos por defecto
        $data = array_merge([
            'priority' => 'normal',
            'read_status' => false,
            'email_sent' => false
        ], $notificationData);

        // Validar fecha de expiración
        if (!empty($data['expires_at']) && strtotime($data['expires_at']) <= time()) {
            return null; // No crear notificaciones ya expiradas
        }

        return $this->insert($data) ? $this->getInsertID() : null;
    }

    /**
     * Marcar notificación como leída
     */
    public function markAsRead(int $notificationId, int $userId = null): bool
    {
        $builder = $this->builder();
        $builder->where('id', $notificationId);
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }

        return $builder->set([
            'read_status' => true,
            'read_at' => date('Y-m-d H:i:s')
        ])->update();
    }

    /**
     * Marcar múltiples notificaciones como leídas
     */
    public function markMultipleAsRead(array $notificationIds, int $userId): bool
    {
        if (empty($notificationIds)) {
            return false;
        }

        return $this->whereIn('id', $notificationIds)
                   ->where('user_id', $userId)
                   ->set([
                       'read_status' => true,
                       'read_at' => date('Y-m-d H:i:s')
                   ])
                   ->update();
    }

    /**
     * Marcar todas las notificaciones como leídas para un usuario
     */
    public function markAllAsRead(int $userId): bool
    {
        return $this->where('user_id', $userId)
                   ->where('read_status', false)
                   ->set([
                       'read_status' => true,
                       'read_at' => date('Y-m-d H:i:s')
                   ])
                   ->update();
    }

    /**
     * Obtener notificaciones de un usuario
     */
    public function getUserNotifications(int $userId, bool $unreadOnly = false, int $limit = 50): array
    {
        $builder = $this->where('user_id', $userId);

        if ($unreadOnly) {
            $builder->where('read_status', false);
        }

        // Excluir notificaciones expiradas
        $builder->groupStart()
               ->where('expires_at IS NULL')
               ->orWhere('expires_at >', date('Y-m-d H:i:s'))
               ->groupEnd();

        return $builder->orderBy('created_at', 'DESC')
                      ->limit($limit)
                      ->findAll();
    }

    /**
     * Obtener notificaciones no leídas de un usuario
     */
    public function getUnreadNotifications(int $userId): array
    {
        return $this->getUserNotifications($userId, true);
    }

    /**
     * Contar notificaciones no leídas
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)
                   ->where('read_status', false)
                   ->groupStart()
                   ->where('expires_at IS NULL')
                   ->orWhere('expires_at >', date('Y-m-d H:i:s'))
                   ->groupEnd()
                   ->countAllResults();
    }

    /**
     * Obtener notificaciones por proyecto
     */
    public function getProjectNotifications(int $projectId): array
    {
        return $this->select('notifications.*, users.full_name, users.email')
                   ->join('users', 'users.id = notifications.user_id')
                   ->where('notifications.project_id', $projectId)
                   ->orderBy('notifications.created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Buscar notificaciones con filtros
     */
    public function searchNotifications(array $filters = []): array
    {
        $builder = $this->select('notifications.*, users.full_name, users.email, projects.code as project_code')
                       ->join('users', 'users.id = notifications.user_id')
                       ->join('projects', 'projects.id = notifications.project_id', 'left');

        // Filtros
        if (!empty($filters['user_id'])) {
            $builder->where('notifications.user_id', $filters['user_id']);
        }

        if (!empty($filters['project_id'])) {
            $builder->where('notifications.project_id', $filters['project_id']);
        }

        if (!empty($filters['type'])) {
            $builder->where('notifications.type', $filters['type']);
        }

        if (!empty($filters['priority'])) {
            $builder->where('notifications.priority', $filters['priority']);
        }

        if (isset($filters['read_status'])) {
            $builder->where('notifications.read_status', $filters['read_status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('notifications.created_at >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('notifications.created_at <=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                   ->like('notifications.title', $search)
                   ->orLike('notifications.message', $search)
                   ->groupEnd();
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'notifications.created_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';
        $builder->orderBy($orderBy, $orderDir);

        return $builder->get()->getResultArray();
    }

    /**
     * Crear notificación de proyecto creado
     */
    public function notifyProjectCreated(int $projectId, int $userId): ?int
    {
        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);

        if (!$project) {
            return null;
        }

        return $this->createNotification([
            'user_id' => $userId,
            'project_id' => $projectId,
            'type' => 'project_created',
            'title' => 'Proyecto creado exitosamente',
            'message' => "Tu proyecto '{$project['title']}' ha sido creado con el código {$project['code']}.",
            'action_url' => "/projects/{$projectId}",
            'action_text' => 'Ver Proyecto',
            'priority' => 'normal'
        ]);
    }

    /**
     * Crear notificación de documento subido
     */
    public function notifyDocumentUploaded(int $projectId, int $documentId, int $uploaderId): array
    {
        $notificationIds = [];
        
        // Notificar al área actual del proyecto
        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);

        if ($project && $project['current_area_id']) {
            $areaAdminModel = new \App\Models\AreaAdminModel();
            $admins = $areaAdminModel->getAreaAdmins($project['current_area_id']);

            foreach ($admins as $admin) {
                if ($admin['user_id'] != $uploaderId) { // No notificar al mismo usuario que subió
                    $notificationId = $this->createNotification([
                        'user_id' => $admin['user_id'],
                        'project_id' => $projectId,
                        'area_id' => $project['current_area_id'],
                        'type' => 'document_uploaded',
                        'title' => 'Nuevo documento para revisión',
                        'message' => "Se ha subido un nuevo documento al proyecto {$project['code']} que requiere tu revisión.",
                        'action_url' => "/admin/projects/{$projectId}/documents",
                        'action_text' => 'Revisar Documento',
                        'priority' => 'high'
                    ]);

                    if ($notificationId) {
                        $notificationIds[] = $notificationId;
                    }
                }
            }
        }

        return $notificationIds;
    }

    /**
     * Crear notificación de documento aprobado
     */
    public function notifyDocumentApproved(int $projectId, int $documentId, int $userId): ?int
    {
        $documentModel = new \App\Models\DocumentModel();
        $document = $documentModel->find($documentId);

        if (!$document) {
            return null;
        }

        return $this->createNotification([
            'user_id' => $userId,
            'project_id' => $projectId,
            'type' => 'document_approved',
            'title' => 'Documento aprobado',
            'message' => "Tu documento '{$document['original_name']}' ha sido aprobado.",
            'action_url' => "/projects/{$projectId}/documents",
            'action_text' => 'Ver Documentos',
            'priority' => 'normal'
        ]);
    }

    /**
     * Crear notificación de fase asignada
     */
    public function notifyPhaseAssigned(int $phaseId, int $userId): ?int
    {
        $phaseModel = new \App\Models\ProjectPhaseModel();
        $phase = $phaseModel->select('project_phases.*, projects.code, projects.title, areas.name as area_name')
                           ->join('projects', 'projects.id = project_phases.project_id')
                           ->join('areas', 'areas.id = project_phases.area_id')
                           ->where('project_phases.id', $phaseId)
                           ->first();

        if (!$phase) {
            return null;
        }

        return $this->createNotification([
            'user_id' => $userId,
            'project_id' => $phase['project_id'],
            'area_id' => $phase['area_id'],
            'type' => 'phase_assigned',
            'title' => 'Nueva fase asignada',
            'message' => "Se te ha asignado la fase '{$phase['area_name']}' del proyecto {$phase['code']}.",
            'action_url' => "/admin/phases/{$phaseId}",
            'action_text' => 'Ver Fase',
            'priority' => 'high'
        ]);
    }

    /**
     * Crear recordatorio de fecha límite
     */
    public function notifyDeadlineReminder(int $projectId, int $userId, int $daysRemaining): ?int
    {
        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);

        if (!$project) {
            return null;
        }

        $urgency = $daysRemaining <= 1 ? 'urgent' : ($daysRemaining <= 3 ? 'high' : 'normal');
        $message = $daysRemaining == 0 
            ? "El proyecto {$project['code']} vence hoy."
            : "El proyecto {$project['code']} vence en {$daysRemaining} día(s).";

        return $this->createNotification([
            'user_id' => $userId,
            'project_id' => $projectId,
            'type' => 'deadline_reminder',
            'title' => 'Recordatorio de fecha límite',
            'message' => $message,
            'action_url' => "/projects/{$projectId}",
            'action_text' => 'Ver Proyecto',
            'priority' => $urgency
        ]);
    }

    /**
     * Crear notificación de proyecto completado
     */
    public function notifyProjectCompleted(int $projectId, int $userId): ?int
    {
        $projectModel = new \App\Models\ProjectModel();
        $project = $projectModel->find($projectId);

        if (!$project) {
            return null;
        }

        return $this->createNotification([
            'user_id' => $userId,
            'project_id' => $projectId,
            'type' => 'project_completed',
            'title' => '¡Proyecto completado!',
            'message' => "Felicitaciones! Tu proyecto '{$project['title']}' ha sido completado exitosamente.",
            'action_url' => "/projects/{$projectId}",
            'action_text' => 'Ver Proyecto',
            'priority' => 'high'
        ]);
    }

    /**
     * Notificar a múltiples usuarios
     */
    public function notifyMultipleUsers(array $userIds, array $notificationData): array
    {
        $notificationIds = [];

        foreach ($userIds as $userId) {
            $data = array_merge($notificationData, ['user_id' => $userId]);
            $notificationId = $this->createNotification($data);
            
            if ($notificationId) {
                $notificationIds[] = $notificationId;
            }
        }

        return $notificationIds;
    }

    /**
     * Obtener estadísticas de notificaciones
     */
    public function getNotificationStats(int $userId = null): array
    {
        $builder = $this->builder();
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }

        $stats = [
            'total' => $builder->countAllResults(false),
            'unread' => $builder->where('read_status', false)->countAllResults(false),
            'read' => $builder->where('read_status', true)->countAllResults(false),
            'high_priority' => $builder->whereIn('priority', ['high', 'urgent'])->countAllResults(false),
            'by_type' => $this->getNotificationCountByType($userId),
            'by_priority' => $this->getNotificationCountByPriority($userId),
            'recent' => $this->getRecentNotifications($userId, 7)
        ];

        return $stats;
    }

    /**
     * Obtener conteo por tipo
     */
    public function getNotificationCountByType(int $userId = null): array
    {
        $builder = $this->builder();
        $builder->select('type, COUNT(*) as count')
               ->groupBy('type');

        if ($userId) {
            $builder->where('user_id', $userId);
        }

        return $builder->orderBy('count', 'DESC')->get()->getResultArray();
    }

    /**
     * Obtener conteo por prioridad
     */
    public function getNotificationCountByPriority(int $userId = null): array
    {
        $builder = $this->builder();
        $builder->select('priority, COUNT(*) as count')
               ->groupBy('priority');

        if ($userId) {
            $builder->where('user_id', $userId);
        }

        return $builder->orderBy('count', 'DESC')->get()->getResultArray();
    }

    /**
     * Obtener notificaciones recientes
     */
    public function getRecentNotifications(int $userId = null, int $days = 7): array
    {
        $dateFrom = date('Y-m-d', strtotime("-{$days} days"));
        
        $builder = $this->where('created_at >=', $dateFrom);
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Eliminar notificaciones antiguas
     */
    public function cleanOldNotifications(int $daysToKeep = 90): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        
        return $this->where('created_at <', $cutoffDate)
                   ->where('read_status', true)
                   ->whereIn('priority', ['low', 'normal'])
                   ->delete();
    }

    /**
     * Eliminar notificaciones expiradas
     */
    public function cleanExpiredNotifications(): int
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
                   ->delete();
    }

    /**
     * Encolar notificación para envío por email
     */
    public function queueEmailNotification(int $notificationId): bool
    {
        // Marcar como pendiente de envío por email
        return $this->update($notificationId, [
            'email_sent' => false,
            'email_error' => null
        ]);
    }

    /**
     * Marcar email como enviado
     */
    public function markEmailSent(int $notificationId): bool
    {
        return $this->update($notificationId, [
            'email_sent' => true,
            'email_sent_at' => date('Y-m-d H:i:s'),
            'email_error' => null
        ]);
    }

    /**
     * Marcar error en envío de email
     */
    public function markEmailError(int $notificationId, string $error): bool
    {
        return $this->update($notificationId, [
            'email_sent' => false,
            'email_error' => $error
        ]);
    }

    /**
     * Obtener notificaciones pendientes de envío por email
     */
    public function getPendingEmailNotifications(int $limit = 100): array
    {
        return $this->select('notifications.*, users.email, users.full_name')
                   ->join('users', 'users.id = notifications.user_id')
                   ->where('notifications.email_sent', false)
                   ->where('notifications.email_error IS NULL')
                   ->whereIn('notifications.priority', ['high', 'urgent'])
                   ->orderBy('notifications.created_at', 'ASC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Obtener tipos de notificación disponibles
     */
    public function getNotificationTypes(): array
    {
        return [
            'info' => 'Información',
            'success' => 'Éxito',
            'warning' => 'Advertencia',
            'error' => 'Error',
            'project_created' => 'Proyecto Creado',
            'project_updated' => 'Proyecto Actualizado',
            'project_completed' => 'Proyecto Completado',
            'project_rejected' => 'Proyecto Rechazado',
            'document_uploaded' => 'Documento Subido',
            'document_approved' => 'Documento Aprobado',
            'document_rejected' => 'Documento Rechazado',
            'phase_assigned' => 'Fase Asignada',
            'phase_completed' => 'Fase Completada',
            'deadline_reminder' => 'Recordatorio de Fecha Límite',
            'system_maintenance' => 'Mantenimiento del Sistema'
        ];
    }
}