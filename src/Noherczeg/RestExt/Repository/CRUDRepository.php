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

}