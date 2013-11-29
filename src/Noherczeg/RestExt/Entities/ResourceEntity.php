<?php

namespace Noherczeg\RestExt\Entities;


use Noherczeg\RestExt\Exceptions\ValidationException;

interface ResourceEntity {

    /**
     * Get the value of the model's primary key.
     *
     * @return int|long
     */
    public function getKey();

    /**
     * Depending of our need if we want pagination or not it produces a Collection accordingly
     *
     * @param $query
     * @param boolean|int $pagination
     * @return \Illuminate\Pagination\Paginator|\Illuminate\Database\Eloquent\Collection
     */
    public function scopeRestCollection($query, $pagination = false);

    /**
     * @throws ValidationException
     */
    public function validate();

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all();

    /**
     * @param int $entityId
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($entityId);

    /**
     * @param int $entityId
     * @return bool|null
     */
    public function delete($entityId);

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = array());

    /**
     * @param array $options
     * @return ResourceEntity|static
     */
    public function fill(array $options);
} 