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
}