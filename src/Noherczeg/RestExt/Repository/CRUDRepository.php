<?php

namespace Noherczeg\RestExt\Repository;


use Illuminate\Database\Eloquent\Builder;

interface CRUDRepository {

    public function all();

    public function save(array $entity);

    public function update(array $entity);

    public function findById($entityId);

    public function delete($entityId);

    public function restCollection(Builder $entity);

    public function enablePagination($boolValue);

}