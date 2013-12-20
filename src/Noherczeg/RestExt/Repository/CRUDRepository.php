<?php

namespace Noherczeg\RestExt\Repository;


use Noherczeg\RestExt\Services\Pageable;

interface CRUDRepository extends Pageable {

    public function all();

    public function save(array $entity);

    public function update($id, array $entity);

    public function findById($entityId);

    public function delete($entityId);

    public function attach($parentId, $entityName, $entityId, array $pivotData = array());

    public function detach($parentId, $entityName, $entityId);

    public function associate($parentId, $entityName, $entityId);

    public function getRelatedCollection($parentId, $relationName);

    public function getRelatedCollectionElement($parentId, $relationName, $elementId);

}