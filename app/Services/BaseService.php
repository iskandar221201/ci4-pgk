<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BaseModel;
use App\Validation\BaseValidator;
use App\Exceptions\ServiceException;
use App\Exceptions\ValidationException;
use Config\AppConstants;

abstract class BaseService
{
    /** @var string Must be set in the child class to bind a Model. */
    protected string $modelClass;
    
    protected ?BaseModel $model = null;
    protected BaseValidator $validator;

    public function __construct()
    {
        if (!empty($this->modelClass)) {
            $this->model = new $this->modelClass();
        }
        $this->validator = new BaseValidator();
    }

    public function findAll(array $filters = [], int $perPage = 0): array
    {
        $search  = $filters['search'] ?? null;
        $perPage = (int) ($filters['per_page'] ?? $perPage);
        $sort    = $filters['sort'] ?? null;
        $order   = $filters['order'] ?? 'asc';

        if ($search) {
            // QueryScopesTrait::search() gracefully handles empty searchableFields
            $this->model->search($search);
        }

        // Whitelist order direction to prevent SQL injection
        $allowedOrder = ['asc', 'desc'];
        $order        = in_array(strtolower($order), $allowedOrder, true) ? strtolower($order) : 'asc';

        // Whitelist sort column against model's allowedFields to prevent SQL injection
        if ($sort !== null && $sort !== '') {
            $allowedSortFields = $this->model->allowedFields ?? [];
            if (in_array($sort, $allowedSortFields, true)) {
                $this->model->orderBy($sort, $order);
            }
            // If sort column is not in allowedFields, silently ignore it (no error, no injection)
        }

        if ($perPage > 0) {
            $perPage = min($perPage, AppConstants::MAX_PER_PAGE);
            $data = $this->model->paginate($perPage);
            return [
                'data'  => $data,
                'pager' => $this->model->pager
            ];
        }

        return [
            'data' => $this->model->findAll()
        ];
    }

    public function findById(int|string $id): ?object
    {
        return $this->model->find($id);
    }

    public function create(array $data): int|string
    {
        $id = $this->model->insert($data, true);
        
        if ($id === false) {
            throw new ServiceException('Failed to create record', AppConstants::HTTP_SERVER_ERROR);
        }
        
        return $id;
    }

    public function update(int|string $id, array $data): bool
    {
        $result = $this->model->update($id, $data);

        if ($result === false || $this->model->db->affectedRows() === 0) {
            throw new ServiceException('Record not found or not modified', AppConstants::HTTP_NOT_FOUND);
        }

        return true;
    }

    public function delete(int|string $id): bool
    {
        $result = $this->model->delete($id);

        if ($result === false || $this->model->db->affectedRows() === 0) {
            throw new ServiceException('Record not found', AppConstants::HTTP_NOT_FOUND);
        }

        return true;
    }

    public function validate(array $data, array $rules, array $messages = []): bool
    {
        $valid = $this->validator->validate($data, $rules, $messages);
        
        if ($valid === false) {
            throw new ValidationException($this->validator->getErrors());
        }
        
        return true;
    }
}
