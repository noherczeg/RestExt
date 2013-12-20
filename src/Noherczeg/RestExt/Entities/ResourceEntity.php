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

    /**
     * Utility method which returns the name of the class.
     *
     * Used for example when an association method is called because we provide namespaced class names, but in the
     * entity's relations we only provide plain names, so it's required to get the simple name.
     *
     * @param bool $withoutNamespace    Cut off the namespace and give back the simple class name only
     * @param bool $toLower             Set true for lower case version
     * @return string
     */
    public function getClassName($withoutNamespace = true, $toLower = false);

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @param  bool   $exists
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function newInstance($attributes = array(), $exists = false);

    /**
     * Being querying a model with eager loading.
     *
     * @param  array|string  $relations
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function with($relations);
} 