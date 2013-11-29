<?php

namespace Noherczeg\RestExt\Services;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

interface Linker {

    public function generatePaginationLinks(Paginator $paginationObject);

    public function createLink($rel, $href);

    public function createLinkToFirstPage($rel);

    public function createSelfLink($withQueryStrings = false);

    public function createParentLink($parentResource = null);

    public function generatePaginationMetaInfo(Paginator $paginationObject);

    public function linksToEntityRelations(Model $ent);

} 