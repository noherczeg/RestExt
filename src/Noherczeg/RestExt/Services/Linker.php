<?php

namespace Noherczeg\RestExt\Services;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

interface Linker {

    public static function generatePaginationLinks(Paginator $paginationObject);

    public static function createLink($rel, $href);

    public static function createLinkToFirstPage($rel);

    public static function createSelfLink($withQueryStrings = false);

    public static function createParentLink($parentResource = null);

    public static function generatePaginationMetaInfo(Paginator $paginationObject);

    public static function linksToEntityRelations(Model $ent);

} 