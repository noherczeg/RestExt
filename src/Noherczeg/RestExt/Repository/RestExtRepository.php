<?php

namespace Noherczeg\RestExt\Repository;


use Illuminate\Database\Eloquent\Builder;
use Noherczeg\RestExt\Entities\Resource;

class RestExtRepository implements CRUDRepository {

    /** @var int Enable/disable pagination for the Entity associated with this Repository */
    protected $pagination = 0;

    /** @var Resource vagy annak leszarmazottja mellyel dolgozunk */
    protected $entity;

    public function __construct(Resource $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static
     */
    public function all()
    {
        return $this->entity->all();
    }

    /**
     * Kivalasztja az azonositohoz tartozo Entitast
     *
     * @param $entityId
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|static
     */
    public function findById($entityId)
    {
        return $this->entity->findOrFail($entityId);
    }

    /**
     * Torli az azonositohoz tartozo entitast
     *
     * @param $entityId
     * @return bool|null
     */
    public function delete($entityId)
    {
        $this->entity = Resource::findOrFail($entityId);
        return $this->entity->delete();
    }

    /**
     * Ment egy Entitast a megadott adatok felhasznalasaval
     *
     * @param array $entity
     * @return bool
     */
    public function save(array $entity)
    {
        $this->entity->fill($entity);
        $this->entity->validate();
        return $this->entity->save();
    }

    /**
     * Frissiti a kapott adatok alapjan az adatokhoz tartozo Entitast
     *
     * @param array $entityData
     * @return bool
     */
    public function update(array $entityData)
    {
        $this->entity->fill($entityData);
        $this->entity->validate();

        return $this->entity->save();
    }

    /**
     * Bekapcsolja/allitja a lapozast/annak mennyiseget per oldal
     *
     * @param boolean|int $value
     */
    public function enablePagination($value)
    {
        $this->pagination = $value;
    }

    /**
     * Returns with a pagination compatible Collection or a simple Eloquent Collection
     *
     * @param Builder $entity
     * @return array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\Paginator|static[]
     */
    public function restCollection(Builder $entity)
    {
        return $this->entity->restCollection($this->pagination);
    }
}