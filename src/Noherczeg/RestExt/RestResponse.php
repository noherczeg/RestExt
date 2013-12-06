<?php

namespace Noherczeg\RestExt;


use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use JMS\Serializer\SerializerInterface;
use Noherczeg\RestExt\Exceptions\ErrorMessageException;
use Noherczeg\RestExt\Http\Resource;
use Noherczeg\RestExt\Providers\HttpStatus;
use Noherczeg\RestExt\Providers\MediaType;
use Noherczeg\RestExt\Services\ResponseComposer;

class RestResponse implements ResponseComposer {

    /**
     * Illuminate config repository.
     *
     * @var Repository
     */
    protected $config;

    /**
     * @var \JMS\Serializer\SerializerInterface Serializer for our Responses
     */
    protected $serializer;

    /**
     * @var string Wildcard used for any Media Type
     */
    protected $mediaTypeWildcard = '*/*';

    /**
     * @var array A list of supported MEdia Types. Used for produces(), and Request validation (Accept Header)
     */
    protected $supportedMediaTypes = [ MediaType::APPLICATION_JSON, MediaType::APPLICATION_XML, MediaType::TEXT_CSV ];

    public function __construct(SerializerInterface $serializer, Repository $config)
    {
        $this->serializer = $serializer;
        $this->config = $config;
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
     * @return $this
     */
    public function setMediaType($mt)
    {
        $this->config->set('restext::media_type', $mt);

        return $this;
    }

    /**
     * Returns the actual MediaType
     *
     * @return string
     */
    private function getMediaType()
    {
        return $this->config->get('restext::media_type');
    }

    /**
     * Overrides the default character set of our responses
     *
     * @param string $cs
     * @return $this
     */
    public function setCharset($cs)
    {
        $this->config->set('restext::encoding', $cs);

        return $this;
    }

    /**
     * Creates a Response object filled with the content and meta info of the Resource which is returned
     *
     * @param \Noherczeg\RestExt\Http\Resource $fromResource
     * @return \Illuminate\Http\Response|Response
     */
    public function sendResource(Resource $fromResource)
    {
        $finalizedResource = ($fromResource->getLinks() === null && $fromResource->getPagesMeta() === null) ? $fromResource->getContent() : $fromResource;

        $response = Response::make($this->createResponseBody($finalizedResource), $this->createResponseCode());
        $response->setCharset($this->config->get('restext::encoding'));
        $response->header('Content-Type', $this->createContentType($this->config->get('restext::media_type'), $this->config->get('restext::encoding')));

        return $response;
    }

    public function sendFile($content, $fileName = 'tmpFile', $contentType = 'string')
    {
        if ($contentType !== 'string')
            throw new ErrorMessageException('Can only convert from string');

        if ($this->getMediaType() !== MediaType::TEXT_CSV)
            throw new ErrorMessageException('Can only convert to CSV');

        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setContent($content);
        $response->setStatusCode(HttpStatus::OK);
        $response->headers->set('Content-Type', MediaType::TEXT_CSV);
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

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

        if ($this->config->get('restext::prefer_accept') && count(Request::getAcceptableContentTypes()) > 0 && !in_array($this->mediaTypeWildcard, Request::getAcceptableContentTypes())) {
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
            $code = HttpStatus::NO_CONTENT;

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
        $mediaType = $this->assembleMediaType($this->config->get('restext::media_type'));

        if($mediaType == MediaType::APPLICATION_JSON)
            return $this->serializer->serialize($data, 'json');

        if($mediaType == MediaType::APPLICATION_XML)
            return $this->serializer->serialize($data, 'xml');

        return $this->serializer->serialize($data, 'json');
    }
}