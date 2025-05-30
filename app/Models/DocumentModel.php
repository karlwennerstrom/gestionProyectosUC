<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Documentos
 * Sistema Multi-Área Universidad Católica
 */
class DocumentModel extends Model
{
    protected $table = 'documents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'project_id',
        'phase_id',
        'user_id',
        'document_type',
        'original_name',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'file_hash',
        'version',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'reviewer_comments',
        'is_required',
        'is_latest',
        'download_count',
        'uploaded_at'
    ];

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = null;

    // Validación
    protected $validationRules = [
        'project_id' => 'required|integer|is_not_unique[projects.id]',
        'user_id' => 'required|integer|is_not_unique[users.id]',
        'document_type' => 'required|in_list[ficha_formalizacion,especificacion_tecnica,diagrama_arquitectura,manual_usuario,documentacion_tecnica,plan_pruebas,certificado_seguridad,otros]',
        'original_name' => 'required|min_length[3]|max_length[255]',
        'file_name' => 'required|min_length[3]|max_length[255]',
        'file_path' => 'required|max_length[500]',
        'file_size' => 'required|integer|greater_than[0]',
        'mime_type' => 'required|max_length[100]',
        'file_hash' => 'required|min_length[32]|max_length[128]',
        'status' => 'required|in_list[uploaded,pending,approved,rejected,replaced]',
        'version' => 'required|integer|greater_than[0]'
    ];

    protected $validationMessages = [
        'project_id' => [
            'is_not_unique' => 'El proyecto especificado no existe'
        ],
        'user_id' => [
            'is_not_unique' => 'El usuario especificado no existe'
        ],
        'document_type' => [
            'in_list' => 'Tipo de documento no válido'
        ],
        'file_size' => [
            'greater_than' => 'El archivo debe tener un tamaño válido'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['setUploadedAt', 'markAsLatest'];
    protected $beforeUpdate = ['updateLatestFlag'];

    /**
     * Establecer fecha de subida
     */
    protected function setUploadedAt(array $data): array
    {
        if (empty($data['data']['uploaded_at'])) {
            $data['data']['uploaded_at'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    /**
     * Marcar como última versión
     */
    protected function markAsLatest(array $data): array
    {
        if (!isset($data['data']['is_latest'])) {
            $data['data']['is_latest'] = true;
        }
        return $data;
    }

    /**
     * Actualizar flag de última versión
     */
    protected function updateLatestFlag(array $data): array
    {
        if (isset($data['data']['is_latest']) && $data['data']['is_latest']) {
            // Si se marca como última versión, desmarcar otras del mismo tipo y proyecto
            $currentDoc = $this->find($data['id'][0]);
            if ($currentDoc) {
                $this->where('project_id', $currentDoc['project_id'])
                    ->where('document_type', $currentDoc['document_type'])
                    ->where('id !=', $currentDoc['id'])
                    ->set(['is_latest' => false])
                    ->update();
            }
        }
        return $data;
    }

    /**
     * Subir nuevo documento
     */
    public function uploadDocument(array $documentData, $uploadedFile): ?int
    {
        // Validar archivo
        if (!$uploadedFile->isValid()) {
            return null;
        }

        // Generar nombre único para el archivo
        $fileName = $this->generateUniqueFileName($uploadedFile);
        $filePath = $this->getUploadPath($documentData['project_id']);
        
        // Crear directorio si no existe
        if (!is_dir(FCPATH . $filePath)) {
            mkdir(FCPATH . $filePath, 0755, true);
        }

        // Mover archivo
        if (!$uploadedFile->move(FCPATH . $filePath, $fileName)) {
            return null;
        }

        // Calcular hash del archivo
        $fileHash = hash_file('sha256', FCPATH . $filePath . $fileName);

        // Verificar si ya existe un archivo con el mismo hash
        $existingDoc = $this->where('file_hash', $fileHash)
                           ->where('project_id', $documentData['project_id'])
                           ->first();

        if ($existingDoc) {
            // Eliminar archivo duplicado
            unlink(FCPATH . $filePath . $fileName);
            return null; // Archivo duplicado
        }

        // Obtener versión del documento
        $version = $this->getNextVersion($documentData['project_id'], $documentData['document_type']);

        // Marcar versiones anteriores como no-latest
        $this->markPreviousVersionsAsOld($documentData['project_id'], $documentData['document_type']);

        // Preparar datos del documento
        $docData = [
            'project_id' => $documentData['project_id'],
            'phase_id' => $documentData['phase_id'] ?? null,
            'user_id' => $documentData['user_id'],
            'document_type' => $documentData['document_type'],
            'original_name' => $uploadedFile->getClientName(),
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getClientMimeType(),
            'file_hash' => $fileHash,
            'version' => $version,
            'status' => 'pending',
            'is_required' => $documentData['is_required'] ?? false,
            'is_latest' => true,
            'download_count' => 0
        ];

        return $this->insert($docData) ? $this->getInsertID() : null;
    }

    /**
     * Generar nombre único para archivo
     */
    protected function generateUniqueFileName($file): string
    {
        $extension = $file->getClientExtension();
        $timestamp = date('Y-m-d_H-i-s');
        $random = bin2hex(random_bytes(8));
        
        return "doc_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Obtener ruta de subida
     */
    protected function getUploadPath(int $projectId): string
    {
        $year = date('Y');
        $month = date('m');
        return "uploads/documents/{$year}/{$month}/project_{$projectId}/";
    }

    /**
     * Obtener siguiente versión del documento
     */
    protected function getNextVersion(int $projectId, string $documentType): int
    {
        $lastDoc = $this->where('project_id', $projectId)
                       ->where('document_type', $documentType)
                       ->orderBy('version', 'DESC')
                       ->first();

        return $lastDoc ? $lastDoc['version'] + 1 : 1;
    }

    /**
     * Marcar versiones anteriores como no-latest
     */
    protected function markPreviousVersionsAsOld(int $projectId, string $documentType): void
    {
        $this->where('project_id', $projectId)
            ->where('document_type', $documentType)
            ->set(['is_latest' => false])
            ->update();
    }

    /**
     * Aprobar documento
     */
    public function approveDocument(int $documentId, int $reviewerId, string $comments = null): bool
    {
        return $this->update($documentId, [
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'reviewer_comments' => $comments,
            'rejection_reason' => null
        ]);
    }

    /**
     * Rechazar documento
     */
    public function rejectDocument(int $documentId, int $reviewerId, string $reason, string $comments = null): bool
    {
        return $this->update($documentId, [
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason,
            'reviewer_comments' => $comments
        ]);
    }

    /**
     * Obtener documentos de un proyecto
     */
    public function getProjectDocuments(int $projectId, bool $onlyLatest = false): array
    {
        $builder = $this->select('documents.*, users.full_name as uploader_name, reviewer.full_name as reviewer_name')
                       ->join('users', 'users.id = documents.user_id')
                       ->join('users as reviewer', 'reviewer.id = documents.reviewed_by', 'left')
                       ->where('documents.project_id', $projectId);

        if ($onlyLatest) {
            $builder->where('documents.is_latest', true);
        }

        return $builder->orderBy('documents.document_type', 'ASC')
                      ->orderBy('documents.version', 'DESC')
                      ->findAll();
    }

    /**
     * Obtener documentos por tipo
     */
    public function getDocumentsByType(int $projectId, string $documentType): array
    {
        return $this->select('documents.*, users.full_name as uploader_name')
                   ->join('users', 'users.id = documents.user_id')
                   ->where('documents.project_id', $projectId)
                   ->where('documents.document_type', $documentType)
                   ->orderBy('documents.version', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener documentos pendientes de revisión
     */
    public function getPendingDocuments(int $areaId = null): array
    {
        $builder = $this->select('documents.*, projects.code as project_code, projects.title as project_title, users.full_name as uploader_name')
                       ->join('projects', 'projects.id = documents.project_id')
                       ->join('users', 'users.id = documents.user_id')
                       ->where('documents.status', 'pending');

        if ($areaId) {
            $builder->where('projects.current_area_id', $areaId);
        }

        return $builder->orderBy('documents.uploaded_at', 'ASC')->findAll();
    }

    /**
     * Obtener documentos de un usuario
     */
    public function getUserDocuments(int $userId): array
    {
        return $this->select('documents.*, projects.code as project_code, projects.title as project_title')
                   ->join('projects', 'projects.id = documents.project_id')
                   ->where('documents.user_id', $userId)
                   ->orderBy('documents.uploaded_at', 'DESC')
                   ->findAll();
    }

    /**
     * Buscar documentos con filtros
     */
    public function searchDocuments(array $filters = []): array
    {
        $builder = $this->select('documents.*, projects.code as project_code, projects.title as project_title, users.full_name as uploader_name')
                       ->join('projects', 'projects.id = documents.project_id')
                       ->join('users', 'users.id = documents.user_id');

        // Filtros
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                   ->like('documents.original_name', $search)
                   ->orLike('projects.code', $search)
                   ->orLike('projects.title', $search)
                   ->groupEnd();
        }

        if (!empty($filters['project_id'])) {
            $builder->where('documents.project_id', $filters['project_id']);
        }

        if (!empty($filters['document_type'])) {
            $builder->where('documents.document_type', $filters['document_type']);
        }

        if (!empty($filters['status'])) {
            $builder->where('documents.status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $builder->where('documents.user_id', $filters['user_id']);
        }

        if (isset($filters['is_latest'])) {
            $builder->where('documents.is_latest', $filters['is_latest']);
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'documents.uploaded_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';
        $builder->orderBy($orderBy, $orderDir);

        return $builder->get()->getResultArray();
    }

    /**
     * Incrementar contador de descargas
     */
    public function incrementDownloadCount(int $documentId): bool
    {
        $document = $this->find($documentId);
        if (!$document) {
            return false;
        }

        return $this->update($documentId, [
            'download_count' => $document['download_count'] + 1
        ]);
    }

    /**
     * Obtener estadísticas de documentos
     */
    public function getDocumentStats(int $projectId = null): array
    {
        $builder = $this->builder();
        
        if ($projectId) {
            $builder->where('project_id', $projectId);
        }

        $stats = [
            'total' => $builder->countAllResults(false),
            'pending' => $builder->where('status', 'pending')->countAllResults(false),
            'approved' => $builder->where('status', 'approved')->countAllResults(false),
            'rejected' => $builder->where('status', 'rejected')->countAllResults(false),
            'total_size' => $this->getTotalFileSize($projectId),
            'by_type' => $this->getDocumentCountByType($projectId),
            'by_mime_type' => $this->getDocumentCountByMimeType($projectId),
            'recent_uploads' => $this->getRecentUploads($projectId, 7)
        ];

        return $stats;
    }

    /**
     * Obtener tamaño total de archivos
     */
    public function getTotalFileSize(int $projectId = null): int
    {
        $builder = $this->builder();
        $builder->select('SUM(file_size) as total_size');

        if ($projectId) {
            $builder->where('project_id', $projectId);
        }

        $result = $builder->get()->getRow();
        return $result ? (int)$result->total_size : 0;
    }

    /**
     * Obtener conteo por tipo de documento
     */
    public function getDocumentCountByType(int $projectId = null): array
    {
        $builder = $this->builder();
        $builder->select('document_type, COUNT(*) as count')
               ->groupBy('document_type');

        if ($projectId) {
            $builder->where('project_id', $projectId);
        }

        return $builder->orderBy('count', 'DESC')->get()->getResultArray();
    }

    /**
     * Obtener conteo por tipo MIME
     */
    public function getDocumentCountByMimeType(int $projectId = null): array
    {
        $builder = $this->builder();
        $builder->select('mime_type, COUNT(*) as count')
               ->groupBy('mime_type');

        if ($projectId) {
            $builder->where('project_id', $projectId);
        }

        return $builder->orderBy('count', 'DESC')->get()->getResultArray();
    }

    /**
     * Obtener subidas recientes
     */
    public function getRecentUploads(int $projectId = null, int $days = 7): array
    {
        $dateFrom = date('Y-m-d', strtotime("-{$days} days"));
        
        $builder = $this->select('documents.*, users.full_name as uploader_name')
                       ->join('users', 'users.id = documents.user_id')
                       ->where('documents.uploaded_at >=', $dateFrom);

        if ($projectId) {
            $builder->where('documents.project_id', $projectId);
        }

        return $builder->orderBy('documents.uploaded_at', 'DESC')->findAll();
    }

    /**
     * Eliminar documento físico y registro
     */
    public function deleteDocument(int $documentId): bool
    {
        $document = $this->find($documentId);
        if (!$document) {
            return false;
        }

        $this->db->transStart();

        // Eliminar archivo físico
        $filePath = FCPATH . $document['file_path'] . $document['file_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Eliminar registro
        $this->delete($documentId);

        // Si era la última versión, marcar la anterior como latest
        if ($document['is_latest']) {
            $previousVersion = $this->where('project_id', $document['project_id'])
                                   ->where('document_type', $document['document_type'])
                                   ->where('id !=', $documentId)
                                   ->orderBy('version', 'DESC')
                                   ->first();

            if ($previousVersion) {
                $this->update($previousVersion['id'], ['is_latest' => true]);
            }
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Obtener tipos de documento disponibles
     */
    public function getDocumentTypes(): array
    {
        return [
            'ficha_formalizacion' => 'Ficha de Formalización',
            'especificacion_tecnica' => 'Especificación Técnica',
            'diagrama_arquitectura' => 'Diagrama de Arquitectura',
            'manual_usuario' => 'Manual de Usuario',
            'documentacion_tecnica' => 'Documentación Técnica',
            'plan_pruebas' => 'Plan de Pruebas',
            'certificado_seguridad' => 'Certificado de Seguridad',
            'otros' => 'Otros'
        ];
    }

    /**
     * Validar si se puede eliminar un documento
     */
    public function canDelete(int $documentId): array
    {
        $document = $this->find($documentId);
        $issues = [];

        if (!$document) {
            $issues[] = 'El documento no existe';
            return ['can_delete' => false, 'issues' => $issues];
        }

        // No se puede eliminar si está aprobado y es requerido
        if ($document['status'] === 'approved' && $document['is_required']) {
            $issues[] = 'No se pueden eliminar documentos aprobados y requeridos';
        }

        // Verificar si es la única versión de un documento requerido
        if ($document['is_required']) {
            $versionCount = $this->where('project_id', $document['project_id'])
                               ->where('document_type', $document['document_type'])
                               ->countAllResults();
            
            if ($versionCount <= 1) {
                $issues[] = 'No se puede eliminar la única versión de un documento requerido';
            }
        }

        return [
            'can_delete' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Obtener tamaño de archivo formateado
     */
    public function getFormattedFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Limpiar archivos huérfanos (sin registro en BD)
     */
    public function cleanOrphanedFiles(): int
    {
        $uploadPath = FCPATH . 'uploads/documents/';
        $cleanedFiles = 0;

        if (!is_dir($uploadPath)) {
            return 0;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $fileName = $file->getFilename();
                
                // Verificar si existe en la BD
                $exists = $this->where('file_name', $fileName)->first();
                
                if (!$exists && $file->getMTime() < strtotime('-1 day')) {
                    unlink($file->getPathname());
                    $cleanedFiles++;
                }
            }
        }

        return $cleanedFiles;
    }
}