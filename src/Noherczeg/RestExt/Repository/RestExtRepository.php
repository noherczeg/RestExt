<?php

namespace Noherczeg\RestExt\Repository;


use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;
use Noherczeg\RestExt\Entities\ResourceEntity;

abstract class RestExtRepository implements CRUDRepository {

    /** @var int Enable/disable pagination for the Entity associated with this Repository */
    protected $pagination = 0;

    /** @var Resource vagy annak leszarmazottja mellyel dolgozunk */
    protected $entity;

    public function __construct(ResourceEntity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static
     */
    public function all()
    {
        return $this->entity->restCollection($this->pagination);
    }

    /**
     * Selects an Entity with the given Primary Key
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
        $this->entity = $this->entity->findOrFail($entityId);
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
        $this->entity = $this->entity->findOrFail($entityData[$this->entity->getKey()]);
        $this->entity->fill($entityData);
        $this->entity->validate();

        return $this->entity->save();
    }

    /**
     * Bekapcsolja/allitja a lapozast/annak mennyiseget per oldal
     *
     * @param int|boolean $value
     */
    public function enablePagination($value)
    {
        $this->pagination = $value;
    }

    /**
     * Returns with a pagination compatible Collection or a simple Eloquent Collection
     *
     * @return array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\Paginator|static[]
     */
    public function restCollection()
    {
        return $this->entity->restCollection($this->pagination);
    }

    /**
     * Connects a given Entity to the one this method is called on if they are in relation to each other. On success
     * it returns the parent, on failure it returns false, or throws exceptions.
     *
     * This method intelligently guesses the relation type, works with belongsTo, HasMany, and every other variant as
     * well. You don't need to bother handling it.
     *
     * @param integer $parentId             The Id of the parent Entity
     * @param string $entityName            Name of the Entity which is in relation to the Repository's Entity
     * @param integer $entityId             Id of the selected Entity
     * @param array $pivotData              Optional data if we have pivot values
     * @throws \InvalidArgumentException
     * @return ResourceEntity
     */
    public function attach($parentId, $entityName, $entityId, array $pivotData = array())
    {
        $parent = $this->entity->findOrFail($parentId);

        $modelToAttach = $entityName::findOrFail($entityId);

        $relationInstance = $this->createRelationInstance($parent, $modelToAttach);

        if ($parent->timestamps && !in_array('created_at', $pivotData))
            $pivotData['created_at'] = new DateTime();

        if ($relationInstance instanceof BelongsToMany) {
            return $relationInstance->save($modelToAttach, $pivotData);
        } elseif ($relationInstance instanceof HasOneOrMany) {
            return $relationInstance->save($modelToAttach);
        }

        throw new InvalidArgumentException('The given Entity ' . $entityName . ' is not in relation with ' . get_class($this->entity));

    }

    public function detach($parentId, $entityName, $entityId)
    {
        $parent = $this->entity->findOrFail($parentId);

        $modelToDetach = $entityName::findOrFail($entityId);

        $relationInstance = $this->createRelationInstance($parent, $modelToDetach);

        return $relationInstance->detach($entityId);

    }

    /**
     * Associates an Entity with the one this Repository handles.
     *
     * @param integer $parentId             The Id of the parent Entity
     * @param string $entityName            Namespaced name of the Entity you wish to associate
     * @param integer $entityId             Id of the Entity you wish to associate
     * @throws \InvalidArgumentException
     * @return ResourceEntity
     */
    public function associate($parentId, $entityName, $entityId)
    {
        $parent = $this->entity->findOrFail($parentId);

        $modelToAttach = $entityName::findOrFail($entityId);

        if (!($modelToAttach instanceof ResourceEntity))
            throw new InvalidArgumentException('The provided Entity is not an instance of ResourceEntity');

        $relationMethodname = $modelToAttach->getClassName(true, true);

        $parent->$relationMethodname()->associate($modelToAttach);

        return $parent->save();
    }

    /**
     * Utility method which creates a Relation instance for an Entity and the given Related one.
     *
     * This can be used for example manipulating pivot data, since we can't call relation methods (e.g. belongsTo, etc..)
     * dynamically.
     *
     * @param ResourceEntity $parent
     * @param ResourceEntity $relatedEntity
     * @return Relation
     * @throws \InvalidArgumentException
     */
    protected function createRelationInstance(ResourceEntity $parent, ResourceEntity $relatedEntity)
    {
        if (!($relatedEntity instanceof ResourceEntity))
            throw new InvalidArgumentException('The provided Entity is not an instance of ResourceEntity');

        $relToAttach = $relatedEntity->getRootRelName();

        if ($relToAttach === null)
            throw new InvalidArgumentException('The $rootRelName parameter has not been set!');

        return $parent->$relToAttach();
    }

    /**
     * Returns a Collection of elements requested for the id
     *
     * @param int $parentId         ID of the parent Entity
     * @param string $relationName  Name of the relation
     * @return Collection
     */
    public function getRelatedCollection($parentId, $relationName)
    {
        return $this->entity->with($relationName)->findOrFail($parentId)->$relationName;
    }

    /**
     * Returns an Entity from a Relation to a given Entity.
     *
     * @param int $parentId
     * @param string  $relationName
     * @param int $elementId
     * @return ResourceEntity
     */
    public function getRelatedCollectionElement($parentId, $relationName, $elementId)
    {
        // get the reference key which will be used in the eager-load constaint, this way we don't need to run a find
        // method on the DB
        $parentName = $this->entity->getClassName(false);
        $parentEnt = new $parentName();
        $referenceKey = $this->simpleOtherKeyFromNamespaced($parentEnt->$relationName()->getOtherKey());

        // optimized way of returning only the single requested nested entity from the db
        return $this->entity->with(array($relationName => function($query) use ($elementId, $referenceKey) {
            $query->where($referenceKey, $elementId);
        }))->findOrFail($parentId)->$relationName->first();
    }

    /**
     * Returns the non concatenated name of a key from a relation.
     *
     * @param string $namespacedKeyName
     * @return string
     */
    protected function simpleOtherKeyFromNamespaced($namespacedKeyName)
    {
        $split = explode('.', $namespacedKeyName);
        return end($split);
    }

}