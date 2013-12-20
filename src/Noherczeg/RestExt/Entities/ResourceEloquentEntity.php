<?php

namespace Noherczeg\RestExt\Entities;


use Illuminate\Support\Facades\Validator;
use Noherczeg\RestExt\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\Model;

abstract class ResourceEloquentEntity extends Model implements ResourceEntity {

    protected $rules = [];

    protected $rootRelName = null;

    /**
     * Sets the return type of an Entity Collection accordingly. Should be called instead of get() or paginate()
     *
     * @param $query
     * @param int $pagination
     * @return \Illuminate\Pagination\Paginator|\Illuminate\Database\Eloquent\Collection
     * @throws \InvalidArgumentException
     */
    public function scopeRestCollection($query, $pagination = 0)
    {
        if (!is_int($pagination) || $pagination < 0)
            throw new \InvalidArgumentException('Expecting 0 or greater int value!');
        return ($pagination === false || $pagination === 0) ? $query->get() : $query->paginate($pagination);
    }

    /**
     * Inner validator for Rest Entities with proper error handling
     *
     * @throws \Noherczeg\RestExt\Exceptions\ValidationException
     */
    public function validate()
    {
        $validator = Validator::make($this->attributes, $this->rules);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
    }

    public function getRootRelName()
    {
        return ($this->rootRelName === null) ? $this->getAttribute('table') : $this->rootRelName;
    }

    public function getClassName($withoutNamespace = true, $toLower = false) {
        $originalName = get_called_class();
        $parts = explode('\\', $originalName);
        $name = ($withoutNamespace) ? end($parts) : $originalName;
        return ($toLower) ? strtolower($name) : $name;
    }

    public function newInstance($attributes = array(), $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static((array) $attributes);

        $model->exists = $exists;

        return $model;
    }

    public static function with($relations)
    {
        if (is_string($relations)) $relations = func_get_args();

        $instance = new static;

        return $instance->newQuery()->with($relations);
    }
}