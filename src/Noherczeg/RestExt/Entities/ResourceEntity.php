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
    public static function all();

    /**
     * @param int $entityId
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findOrFail($entityId);

    /**
     * @return bool|null
     */
    public function delete();

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

    /**
     * Returns the name of the rel which thi sentity is represented through. The name is the name which comes after the
     * root url e.g. for UserEntity it should return "users", etc...
     *
     * @return string
     */
    public function getRootRelName();
} 