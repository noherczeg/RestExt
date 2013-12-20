<?php

namespace Noherczeg\RestExt;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Noherczeg\RestExt\Entities\ResourceEntity;
use Noherczeg\RestExt\Http\QueryStringOperations;
use Noherczeg\RestExt\Services\Linker;
use Illuminate\Support\Facades\Request;

class RestLinker implements Linker {

    /**
     * @var \Noherczeg\RestExt\Http\QueryStringOperations
     */
    private $queryStringOps;

    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @var \Illuminate\Routing\UrlGenerator
     */
    private $url;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $config;
    
    public function __construct(QueryStringOperations $qso)
    {
        $this->queryStringOps = $qso;
        $this->request = App::make('request');
        $this->url = App::make('url');
        $this->config = App::make('config');
    }
    
    /**
     * Intelligently creates pagination links from the raw Pagination object
     *
     * @param Paginator $paginationObject
     * @return array
     */
    public function generatePaginationLinks(Paginator $paginationObject)
    {
        $links = [];

        $pageParam = Config::get('restext::page_param');

        $links[] = $this->createLink('first', $this->url->to($this->request->url() . $this->queryStringOps->setQueryStringParam($pageParam, 1)));

        if ($paginationObject->getCurrentPage() > 1)
            $links[] = $this->createLink('previous', $this->url->to($this->request->url() . $this->queryStringOps->setQueryStringParam($pageParam, ($paginationObject->getCurrentPage() - 1))));

        if ($paginationObject->getCurrentPage() < $paginationObject->getLastPage())
            $links[] = $this->createLink('next', $this->url->to($this->request->url() . $this->queryStringOps->setQueryStringParam($pageParam, ($paginationObject->getCurrentPage() + 1))));

        $links[] = $this->createLink('last', $this->url->to($this->request->url() . $this->queryStringOps->setQueryStringParam($pageParam, $paginationObject->getLastPage())));

        return $links;
    }

    /**
     * Helper method to assemble a Llink array.
     *
     * @param string $rel
     * @param string $href
     * @return array
     */
    public function createLink($rel, $href = null) {
        $url = ($href === null) ? $this->url->to($this->request->url() . '/' . strtolower($rel)) : $href;
        return ['rel' => $rel, 'href' => $url];
    }

    /**
     * Creates a Link to the first page of a Resource Collection.
     *
     * Makes sense to have this ability when we would like to embed a link to a sub Resource Collection which should
     * be paginated due to the fact that it maybe contains tons of data, so this way we don't expose all the Collection,
     * sparing some server side resources.
     *
     * @param $rel
     * @return array
     */
    public function createLinkToFirstPage($rel) {
        $url = $this->url->to($this->request->url() . '/' . strtolower($rel) . $this->queryStringOps->setQueryStringParam($this->config->get('restext::page_param'), 1));
        return ['rel' => $rel, 'href' => $url];
    }

    /**
     * Creates a parent Link to a Resource or Resource Collection
     *
     * @param $parentResource      The name of the parent Resource
     * @return array
     */
    public function createParentLink($parentResource = null)
    {
        $original = $this->request->url();
        $parentName = ($parentResource === null) ? 'parent' : $parentResource;

        return $this->createLink($parentName, substr($original, 0, strrpos($original, '/')));
    }

    /**
     * Creates self links to the currently called Resource with, or without the provided Query Strings.
     *
     * @param bool $withQueryStrings    Decides if Query Strings should be attached as well, or not
     * @return array
     */
    public function createSelfLink($withQueryStrings = false)
    {
        return $this->createLink('self', ($withQueryStrings) ? $this->url->full() : $this->request->url());
    }

    /**
     * Creates a Link which points to a URL up $steps times from the current URL
     *
     * @param string $rel   Name of the Rel of the Link
     * @param int $steps    Steps to go up in the URL
     * @return array
     */
    public function createLinkUp($rel, $steps = 1)
    {
        $uriParts = explode("/",ltrim($this->request->path()));
        $count = 0;

        while ($count < $steps) {
            array_pop($uriParts);
            $count++;
        }

        return $this->createLink($rel, $this->request->root() . '/' . implode('/', $uriParts));
    }

    /**
     * Creates an array of meta information for pagination
     *
     * @param Paginator $paginationObject
     * @return array
     */
    public function generatePaginationMetaInfo(Paginator $paginationObject)
    {
        return [
            'total' => $paginationObject->getTotal(),
            'perPage' => $paginationObject->getPerPage(),
            'isFirstPage' => ($paginationObject->getCurrentPage() == 1) ? true : false,
            'isLastPage' => ($paginationObject->getCurrentPage() == $paginationObject->getLastPage()) ? true : false
        ];
    }

    /**
     * Creates Links to all of the provided Model's relations
     *
     * Keep in mind that relations may only be scanned after they are attached to a certain Model, which means "join"
     * operation(s) where triggered. For example: with() method was called on the Model with params!
     *
     * @param Model $ent                    The Model that provides the Relations for our links
     * @param bool $forceReturn             If set to true, it will return an empty array, and won't throw an Exception
     * @throws \InvalidArgumentException
     * @return array
     */
    public function linksToEntityRelations(Model $ent, $forceReturn = false)
    {
        $links = [];
        $relations = $ent->getRelations();

        if(!is_array($relations) || count($relations) == 0) {
            if ($forceReturn === false)
                throw new \InvalidArgumentException('Provided Entity does not contain any relations, can\'t generate links...');

            return $links;
        }

        foreach ($relations as $key => $rel) {
            if ($rel instanceof ResourceEntity) {
                $links[] = $this->createLink($rel->getRootRelName(), $this->request->url() . '/' . $rel->getRootRelName());
            } else {
                $links[] = $this->createLink($key, $this->request->url() . '/' . $key);
            }

        }

        return $links;
    }
}