<?php namespace Noherczeg\RestExt\Services;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;

interface Pageable {

    /**
     * Sets the pagination limit.
     *
     * @param mixed $value
     * @return void
     */
    public function enablePagination($value);

    /**
     * Creates an Object of type depending on the pagination. If it's set it'll produce a Paginator, Else it'll create
     * a Collection.
     *
     * @return Paginator|Collection
     */
    public function restCollection();

} 