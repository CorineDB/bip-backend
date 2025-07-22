<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\BaseRepositoryInterface;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }


    /**
     * Créer une instance d'un Model.
     *
     *  @return Model
    */
    public function newInstance() : Model
    {
        return $this->model->newInstance();
    }

    /**
     * get model
     *
     * @return Model
     */
    public function getInstance(): Model
    {
        return $this->model;
    }

    /**
     * Créer une instance d'un nouveau Model avec ces données.
     *
     * @return Model
     */
    public function new($payload): Model
    {
        return new $this->model($payload);
    }

    /**
     * Créer une instance d'un nouveau Model avec ces données.
     *
     * @return Model
     */
    public function fill($payload): Model
    {
        return $this->model->fill($payload);
    }

    /**
     * Compter le nombre d'occurence de donnée existante d'une table.
     *
     * @return int
    */
    public function getCount(): int
    {
        return $this->model->count();
    }

    public function all(array $columns = ['*']): \Illuminate\Support\Collection
    {
        return $this->model->orderByDesc('created_at')->get($columns);
    }

    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->model->findOrFail($id, $columns);
    }

    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    public function update(int|string $id, array $attributes): bool
    {
        $instance = $this->find($id);
        return $instance ? $instance->update($attributes) : false;
    }

    public function delete(int|string $id): bool
    {
        $instance = $this->find($id);
        return $instance ? $instance->delete() : false;
    }

    public function paginate(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }
}