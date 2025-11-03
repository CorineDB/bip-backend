<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class EloquentRepository implements BaseRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getAll(array $columns = ['*'])
    {
        try {
            return $this->model->all($columns);
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getAllPaginated($perPage = 15, array $columns = ['*'])
    {
        try {
            return $this->model->paginate($perPage, $columns);
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'perPage' => $perPage,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function find($id, array $columns = ['*'])
    {
        try {
            return $this->model->find($id, $columns);
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function findBy($column, $value, array $columns = ['*'])
    {
        try {
            return $this->model->where($column, $value)->first($columns);
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'column' => $column,
                'value' => $value,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function findAllBy($column, $value, array $columns = ['*'])
    {
        try {
            return $this->model->where($column, $value)->get($columns);
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'column' => $column,
                'value' => $value,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function findWhereIn($column, array $values, array $columns = ['*'])
    {
        try {
            return $this->model->whereIn($column, $values)->get($columns);
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'column' => $column,
                'values' => $values,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function findWhereNotIn($column, array $values, array $columns = ['*'])
    {
        try {
            return $this->model->whereNotIn($column, $values)->get($columns);
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'column' => $column,
                'values' => $values,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function findWhere(array $criteria, array $orderBy = null, $limit = null, $offset = null, array $columns = ['*'])
    {
        try {
            $query = $this->model->query();

            foreach ($criteria as $criterion) {
                $query->where($criterion[0], $criterion[1], $criterion[2]);
            }

            if ($orderBy) {
                foreach ($orderBy as $field => $direction) {
                    $query->orderBy($field, $direction);
                }
            }

            if ($limit) {
                $query->limit($limit);
            }

            if ($offset) {
                $query->offset($offset);
            }

            return $query->get($columns);
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'criteria' => $criteria,
                'orderBy' => $orderBy,
                'limit' => $limit,
                'offset' => $offset,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function create(array $data)
    {
        try {
            return $this->model->create($data);
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function new(array $attributes = [])
    {
        return $this->model->newInstance($attributes);
    }

    public function fill(array $attributes)
    {
        return $this->model->fill($attributes);
    }

    public function update($id, array $data)
    {
        try {
            $record = $this->find($id);
            $record->update($data);
            return $record;
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'id' => $id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            return $this->model->destroy($id);
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function deleteAllBy($column, $value)
    {
        try {
            return $this->model->where($column, $value)->delete();
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'column' => $column,
                'value' => $value,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function count()
    {
        try {
            return $this->model->count();
        } catch (Throwable $e) {
            Log::error(sprintf('[%s] [%s] %s', get_class($this), __FUNCTION__, $e->getMessage()), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}