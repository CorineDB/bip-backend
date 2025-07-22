<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function getModel(): ?Model;

    public function all(array $columns = ['*']): \Illuminate\Support\Collection;

    public function find(int|string $id, array $columns = ['*']): ?Model;

    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    public function create(array $attributes): Model;

    public function update(int|string $id, array $attributes): bool;

    public function delete(int|string $id): bool;

    public function paginate(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    /**
     * Rechercher une occurence de donnée d'une table grâce à l'un des attributs de la table.
     *
     * @param string $attributName
     * @param string $attributValue
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Model
     */
    public function findByAttribute(
        string $attributName,
        string $attributValue,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model;
}
