<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BaseModel;
use App\Validation\BaseValidator;
use App\Exceptions\ServiceException;
use App\Exceptions\ValidationException;
use App\Config\AppConstants;

abstract class BaseService
{
    /** @var string Wajib di-set di child class */
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
        $search = $filters['search'] ?? null;
        $perPage = $filters['per_page'] ?? $perPage;
        $sort = $filters['sort'] ?? null;
        $order = $filters['order'] ?? 'asc';

        if ($search) {
            // BaseModel::search() gracefully handles empty searchableFields
            $this->model->search($search);
        }

        if ($sort) {
            $this->model->orderBy($sort, $order);
        }

        if ((int)$perPage > 0) {
            $data = $this->model->paginate((int)$perPage);
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
        
        if ($result === false) {
            throw new ServiceException('Failed to update record', AppConstants::HTTP_SERVER_ERROR);
        }
        
        return true;
    }

    public function delete(int|string $id): bool
    {
        $result = $this->model->delete($id);
        
        if ($result === false) {
            throw new ServiceException('Failed to delete record', AppConstants::HTTP_SERVER_ERROR);
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
