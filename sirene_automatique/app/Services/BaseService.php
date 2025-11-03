<?php

namespace App\Services;

use App\Repositories\BaseRepository;

class BaseService
{
    protected $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(array $columns = ['*'])
    {
        return $this->repository->getAll($columns);
    }

    public function getAllPaginated($perPage = 15, array $columns = ['*'])
    {
        return $this->repository->getAllPaginated($perPage, $columns);
    }

    public function find($id, array $columns = ['*'])
    {
        return $this->repository->find($id, $columns);
    }

    public function findBy($column, $value, array $columns = ['*'])
    {
        return $this->repository->findBy($column, $value, $columns);
    }

    public function findAllBy($column, $value, array $columns = ['*'])
    {
        return $this->repository->findAllBy($column, $value, $columns);
    }

    public function findWhereIn($column, array $values, array $columns = ['*'])
    {
        return $this->repository->findWhereIn($column, $values, $columns);
    }

    public function findWhereNotIn($column, array $values, array $columns = ['*'])
    {
        return $this->repository->findWhereNotIn($column, $values, $columns);
    }

    public function findWhere(array $criteria, array $orderBy = null, $limit = null, $offset = null, array $columns = ['*'])
    {
        return $this->repository->findWhere($criteria, $orderBy, $limit, $offset, $columns);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function new(array $attributes = [])
    {
        return $this->repository->new($attributes);
    }

    public function fill(array $attributes)
    {
        return $this->repository->fill($attributes);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function deleteAllBy($column, $value)
    {
        return $this->repository->deleteAllBy($column, $value);
    }

    public function count()
    {
        return $this->repository->count();
    }
}