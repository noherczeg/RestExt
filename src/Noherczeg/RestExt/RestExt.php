<?php

namespace Noherczeg\RestExt;


use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Request;
use Noherczeg\RestExt\Http\Resource;
use Noherczeg\RestExt\Services\Linker;

class RestExt {

    /** @var Resource */
    private $resource = null;

    private $rawData = null;

    private $links = false;

    private $linker;

    public function __construct(Linker $linker)
    {
        $this->resource = new Resource();
        $this->linker = $linker;
    }

    /**
     * Finalizer method for Resource creation.
     *
     * Should be called at the end of the Resource setup process, or by it self if the second parameter is set. In that
     * case the Resource will be created with the default settings.
     *
     * @param bool $withContentSelfLink
     * @param mixed $fromData
     * @return Resource
     */
    public function create($withContentSelfLink = false, $fromData = null)
    {
        $data = ($fromData === null) ? $this->rawData->toArray() : $fromData;
        $contentCollection = null;

        if ($this->links) {

            // self Link for our Resource
            $this->resource->addLink($this->linker->createSelfLink());

            if ($this->rawData instanceof Paginator) {
                $this->rawData->links();

                $contentCollection = $data['data'];

                // Links for pagination
                $this->resource->addLinks($this->linker->generatePaginationLinks($this->rawData));

                // Paging Metainfo
                $this->resource->setPagesMeta($this->linker->generatePaginationMetaInfo($this->rawData));
            } else {
                // the Content it self in the Resource
                $contentCollection = $data;
            }

            // If we allow Links for nested Resources this will generate them
            if ($withContentSelfLink) {
                foreach ($contentCollection as $key => $resourceCandidate) {
                    $contentCollection[$key]['links'][] = ['self' => Request::url() . '/' . $resourceCandidate['id']];
                }
            }

            $this->resource->setContent($contentCollection);
        } else {
            $this->resource->setContent($data);
        }

        return $this->resource;
    }

    /**
     * Enables or disables link generation for a Resource.
     *
     * @param bool $boolvalue
     * @return RestExt
     */
    public function links($boolvalue = true)
    {
        $this->links = $boolvalue;

        return $this;
    }

    /**
     * Adds a single Link to the Resource under generation. As a reminder: Links are plain arrays.
     *
     * @param array $link
     * @return RestExt
     */
    public function addLink(array $link)
    {
        $this->resource->addLink($link);

        return $this;
    }

    /**
     * Adds multiple Links to the Resource under generation.
     *
     * @param array $links
     * @return RestExt
     */
    public function addLinks(array $links)
    {
        $this->resource->addLinks($links);

        return $this;
    }

    /**
     * Sets the content of the Resource. It can be basically anything.
     *
     * @param $rawResource
     * @return RestExt
     */
    public function from($rawResource)
    {
        $this->rawData = $rawResource;

        return $this;
    }

} 