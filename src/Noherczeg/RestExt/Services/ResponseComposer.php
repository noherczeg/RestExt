<?php

namespace Noherczeg\RestExt\Services;


use Noherczeg\RestExt\Http\Resource;

interface ResponseComposer {

    /**
     * Returns a list of supported Medi Types
     *
     * @return array
     */
    public function getSupportedMediaTypes();

    /**
     * Creates a Response object filled with the content and meta info of the Resource which is returned
     *
     * @param \Noherczeg\RestExt\Http\Resource $resource
     * @return \Illuminate\Http\Response
     */
    public function sendResource(Resource $resource);

    /**
     * Overrides the returned MediaType of our Resource
     *
     * @param string $mt
     */
    public function setMediaType($mt);

    /**
     * Overrides the default character set of our responses
     *
     * @param string $cs
     */
    public function setCharset($cs);

} 