<?php

namespace Noherczeg\RestExt;


use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Noherczeg\RestExt\Entities\ResourceEntity;
use Noherczeg\RestExt\Http\Resource;
use Noherczeg\RestExt\Services\CSVConverter;
use Noherczeg\RestExt\Services\Linker;

class RestExt {

    /** @var Resource */
    private $resource = null;

    private $rawData = null;

    private $links = false;

    private $linker;

    private $version = '';
    
    private $config;

    public function __construct(Linker $linker, Repository $config)
    {
        $this->config = $config;
        $this->resource = new Resource();
        $this->linker = $linker;
        $this->version = $this->config->get('restext::version');
    }

    /**
     * Finalizer method for Resource creation.
     *
     * Should be called at the end of the Resource setup process, or by it self if the second parameter is set. In that
     * case the Resource will be created with the default settings.
     *
     * @param bool $withContentSelfLink
     * @param mixed $fromData
     * @return \Noherczeg\RestExt\Http\Resource
     */
    public function create($withContentSelfLink = false, $fromData = null)
    {
        $data = ($fromData === null) ? $this->rawData->toArray() : $fromData;
        $contentCollection = null;

        $version = strlen($this->version) > 0 ? $this->version . '/' : '';

        if ($this->links) {

            // self Link for our Resource
            $this->resource->addLink($this->linker->createSelfLink(true));

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
                $this->nestedSelfRels($this->rawData, $contentCollection, $version);
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

    /**
     * Sets the version number to use while generating Resources.
     *
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Returns the registered version number.
     *
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the given $targetCollection's nested "self" links from the given raw data using the given version number.
     *
     * @param Collection|Paginator $rawData     Data provided
     * @param $targetCollection                 Collection on links are set
     * @param mixed $version                    Version number could potentialy come from anywhere
     */
    private function nestedSelfRels($rawData, &$targetCollection, $version)
    {
        $nestedData = null;

        if ($rawData instanceof Model) {
            $nestedData = $rawData->getRelations();
        } else {
            $nestedData = $rawData;
        }

        foreach ($nestedData as $key => $resourceCandidate) {
            if ($resourceCandidate instanceof ResourceEntity) {

                // root Resource is a selected Resource not a Collection therefore we need the nested Resource's root rel
                $addition = ($rawData instanceof Model) ? $resourceCandidate->getRootRelName() . '/' : '';

                $targetCollection[$key]['links'][] = ['self' => Request::url() . '/' . $addition . $resourceCandidate->id];
            }

            if ($resourceCandidate instanceof Collection) {
                foreach ($resourceCandidate as $rcKey => $innerCandidate) {
                    $targetCollection[$key][$rcKey]['links'][] = ['self' => Request::url() . '/' . $innerCandidate->getRootRelName() . '/' . $innerCandidate->id];
                }
            }
        }
    }

    /**
     * Creates a CSV String from the given collection.
     *
     * Modified version of: https://gist.github.com/johanmeiring/2894568
     *
     * @param Collection|array $input
     * @throws \InvalidArgumentException
     * @return string
     */
    public function collectionToCSVString($input)
    {
        $converted = [];

        if ($input instanceof Collection) {
            $converted = $input->toArray();
        } elseif (is_array($input)) {
            $converted = $input;
        } else {
            throw new \InvalidArgumentException;
        }
        
        if (count($converted) == 0)
            return '';

        $converter = new CSVConverter(array_keys($converted[0]));

        foreach ($converted as $row) {
            $converter->addRow($row);
        }

        return (string) $converter;
    }

} 