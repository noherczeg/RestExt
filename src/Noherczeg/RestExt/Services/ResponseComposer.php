<?php

namespace Noherczeg\RestExt\Services;


use Noherczeg\RestExt\Http\Resource;
use Noherczeg\RestExt\Providers\MediaType;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @param mixed $data           The data to send back
     * @param int $status           Status code of the Response
     * @param string $contentType   The Content Type of the Response
     * @return Response
     */
    public function plainResponse($data, $status = 200, $contentType = MediaType::APPLICATION_JSON);

} 