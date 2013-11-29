<?php

namespace Noherczeg\RestExt;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use JMS\Serializer\SerializerBuilder;
use Noherczeg\RestExt\Http\Resource;
use Noherczeg\RestExt\Providers\HttpStatus;
use Noherczeg\RestExt\Providers\MediaType;
use Noherczeg\RestExt\Services\ResponseComposer;
use Symfony\Component\HttpFoundation\Response;

class RestResponseComposer implements ResponseComposer {

    private $serializer;

    /**
     * @var string The Media Type which is set for the HTTP Response
     */
    private $mediaType = null;

    /**
     * @var string The Charset of the Response
     */
    private $charset = null;

    /**
     * @var string Wildcard used for any Media Type
     */
    private $mediaTypeWildcard = '*/*';

    /**
     * @var array A list of supported MEdia Types. Used for produces(), and Request validation (Accept Header)
     */
    private $supportedMediaTypes = [ MediaType::APPLICATION_JSON, MediaType::APPLICATION_XML ];

    public function __construct()
    {
        $this->serializer = SerializerBuilder::create()->build();
        $this->mediaType = Config::get('restext::media_type');
        $this->charset = Config::get('restext::encoding');
    }

    /**
     * Returns a list of supported Medi Types
     *
     * @return array
     */
    public function getSupportedMediaTypes()
    {
        return $this->supportedMediaTypes;
    }

    /**
     * Overrides the returned MediaType of our Resource
     *
     * @param string $mt
     */
    public function setMediaType($mt)
    {
        $this->mediaType = $mt;
    }

    /**
     * Overrides the default character set of our responses
     *
     * @param string $cs
     */
    public function setCharset($cs)
    {
        $this->charset = $cs;
    }

    /**
     * Creates a Response object filled with the content and meta info of the Resource which is returned
     *
     * @param \Noherczeg\RestExt\Http\Resource $fromResource
     * @return \Illuminate\Http\Response|Response
     */
    public function sendResource(Resource $fromResource)
    {
        $response = new Response(
            $this->createResponseBody($fromResource),
            $this->createResponseCode(),
            ['Content-Type', $this->createContentType($this->mediaType, $this->charset)]
        );

        $response->setCharset($this->charset);

        return $response;
    }

    /**
     * Wrapper function to create a complete Content Type Header
     *
     * @param string $mediaType
     * @param string $charset
     * @return string
     */
    private function createContentType($mediaType, $charset)
    {
        return $this->assembleMediaType($mediaType) . '; ' . 'charset=' . $charset;
    }

    /**
     * Returns a MediaType after evaluating the context's settings
     *
     * @param $mediaType
     * @return string
     */
    private function assembleMediaType($mediaType)
    {
        $finalType = $mediaType;

        if (Config::get('restext::prefer_accept') && count(Request::getAcceptableContentTypes()) > 0 && !in_array($this->mediaTypeWildcard, Request::getAcceptableContentTypes())) {
            foreach(Request::getAcceptableContentTypes() as $acceptType) {
                if (in_array($acceptType, $this->getSupportedMediaTypes())) {
                    $finalType = Request::getAcceptableContentTypes()[0];
                    break;
                }
            }
        }

        return $finalType;
    }

    /**
     * Creates a Response code when working with a Resource which is aware of the Request's method type so this can
     * be used to replace some boilerplate code when trying to decide what to set at what scenario.
     *
     * The result of this method is a 2xx code! If a procedure fails, it should be handled in a Listener, or with
     * Exceptions, or whatever.
     *
     * @return int
     */
    private function createResponseCode()
    {
        $code = HttpStatus::OK;
        $method = strtolower(Request::getMethod());

        if ($method == 'post')
            $code = HttpStatus::CREATED;
        elseif ($method == 'put' || $method == 'patch' || $method == 'delete')
            $code == HttpStatus::NO_CONTENT;

        return $code;
    }

    /**
     * Creates MediaType-aware content from raw data
     *
     * @param mixed $data
     * @return string
     */
    private function createResponseBody($data)
    {
        $mediaType = $this->assembleMediaType($this->mediaType);

        if(!$mediaType == MediaType::APPLICATION_JSON)
            return $this->serializer->serialize($data, 'xml');

        return $this->serializer->serialize($data, 'json');
    }
}