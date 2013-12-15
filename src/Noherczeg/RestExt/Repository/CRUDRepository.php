<?php

namespace Noherczeg\RestExt\Repository;


interface CRUDRepository {

    public function all();

    public function save(array $entity);

    public function update(array $entity);

    public function findById($entityId);

    public function delete($entityId);

    public function restCollection();

    public function enablePagination($boolValue);

    public function attach($parentId, $entityName, $entityId, array $pivotData = array());

    public function detach($parentId, $entityName, $entityId);

    public function associate($parentId, $entityName, $entityId);

}