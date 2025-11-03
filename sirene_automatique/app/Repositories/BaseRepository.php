<?php

namespace App\Repositories;

interface BaseRepository
{
    /**
     * Get all records.
     *
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll(array $columns = ['*']);

    /**
     * Get all records with pagination.
     *
     * @param int $perPage
     * @param array $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPaginated($perPage = 15, array $columns = ['*']);

    /**
     * Find a record by its ID.
     *
     * @param int $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($id, array $columns = ['*']);

    /**
     * Find a record by a specific column.
     *
     * @param string $column
     * @param mixed $value
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findBy($column, $value, array $columns = ['*']);

    /**
     * Find all records by a specific column.
     *
     * @param string $column
     * @param mixed $value
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findAllBy($column, $value, array $columns = ['*']);

    /**
     * Find all records where a column is in a given array.
     *
     * @param string $column
     * @param array $values
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findWhereIn($column, array $values, array $columns = ['*']);

    /**
     * Find all records where a column is not in a given array.
     *
     * @param string $column
     * @param array $values
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findWhereNotIn($column, array $values, array $columns = ['*']);
    
    /**
     * Find all records matching a set of criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findWhere(array $criteria, array $orderBy = null, $limit = null, $offset = null, array $columns = ['*']);

    /**
     * Create a new record.
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data);

    /**
     * Create a new instance of the model.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function new(array $attributes = []);

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function fill(array $attributes);

    /**
     * Update a record by its ID.
     *
     * @param int $id
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $data);

    /**
     * Delete a record by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);

    /**
     * Delete all records by a specific column.
     *
     * @param string $column
     * @param mixed $value
     * @return bool
     */
    public function deleteAllBy($column, $value);

    /**
     * Count the number of records.
     *
     * @return int
     */
    public function count();
}