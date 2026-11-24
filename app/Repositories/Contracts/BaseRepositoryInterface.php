<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get all records.
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find a record by its primary key.
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find a record or throw ModelNotFoundException.
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Create a new record.
     */
    public function create(array $data): Model;

    /**
     * Update an existing record.
     */
    public function update(int $id, array $data): Model;

    /**
     * Delete a record by ID.
     */
    public function delete(int $id): bool;
}
