<?php

namespace Noherczeg\RestExt\Controllers;

use Illuminate\Routing\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Noherczeg\RestExt\Exceptions\PermissionException;
use Noherczeg\RestExt\Facades\RestResponse;
use Noherczeg\RestExt\Providers\HttpStatus;
use Noherczeg\RestExt\Services\ResponseComposer;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class RestExtController extends Controller
{

    /**
     * @var string Actual URL Path where the Controller is called on
     */
    protected $route = null;

    /**
     * @var \Noherczeg\RestExt\Services\AuthorizationService Authorization Service Implementatio goes here
     */
    protected $authorizationService = null;

    /**
     * @var ResponseComposer
     */
    protected $responseComposer = null;

    /**
     * Overrides the default Content-Type on Responses
     *
     * @var mixed
     */
    protected $produces = null;

    /**
     * @var string Wildcard used for any Media Type
     */
    private $mediaTypeWildcard = '*/*';

    /**
     * @var array Roles set for authorization
     */
    private $securityRoles = [];

    protected $accessPolicy = null;

    public function __construct()
    {
        $this->route = Request::path();

        if ($this->accessPolicy === null)
            $this->accessPolicy = Config::get('restext::access_policy');

        // if we set the property it should override the Accept Header even if it it is set otherwise in the configs
        if ($this->produces !== null)
            $this->produce($this->produces);

        $securityRoles = $this->securityRoles;
        $accessPolicy = $this->accessPolicy;

        // Default actions
        $this->beforeFilter(function() use ($securityRoles, $accessPolicy)
        {

            // To prevent processing / returning of content if by default the access policy is set to
            // "whitelist" and no allowed roles have been set.
            if ($accessPolicy == 'whitelist' && count($securityRoles) == 0)
                throw new PermissionException();

            // If the "prefer_accept" configuration is set to true, we set RestResponse to send the MediaType given in
            // the Accept Header if it's compatible with our system. If not we set it to the default config's value.
            if (Config::get('restext::prefer_accept')) {
                if(in_array($this->requestAccepts(), RestResponse::getSupportedMediaTypes()))
                    RestResponse::setMediaType($this->requestAccepts());

                RestResponse::setMediaType(Config::get('restext::media_type'));
            }

        });
    }

    /**
     * Controls the accessability for a given Controller.
     *
     * only: only allows acces to the roles in the list (whitelist)
     * except: only allows access to users who don't have the roles in the list (blacklist)
     *
     * @param string $filter only|except
     * @param array $roles
     * @throws \InvalidArgumentException
     * @throws PermissionException
     * @return bool
     */
    protected function allowForRoles($filter = null, array $roles = [])
    {
        $this->securityRoles = $roles;

        if (!in_array(strtolower($filter), ['only', 'except']))
            throw new \InvalidArgumentException('Expecting: "only" or "except"');

        if (strtolower($filter) == 'only' && !$this->authorizationService->hasRoles($roles)) {
                throw new PermissionException();
        } elseif (strtolower($filter) == 'except' && $this->authorizationService->hasRoles($roles)) {
                throw new PermissionException();
        }
    }

    protected function produce(array $mediaTypes)
    {
        if (in_array($this->mediaTypeWildcard, $mediaTypes))
            return true;

        foreach(Request::getAcceptableContentTypes() as $contentType) {
            if (!in_array($contentType, $mediaTypes) && Config::get('restext::restrict_accept'))
                App::abort(HttpStatus::UNSUPPORTED_MEDIA_TYPE, 'Requested MediaType is not supported');
        }

        RestResponse::setMediaType($mediaTypes[0]);
    }

    /**
     * Checks if the provided Media Type of the Request is in the given list, or not
     *
     * @param array $mediaTypes
     * @return bool
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function consume(array $mediaTypes)
    {
        if ($this->requestContentType() == null || in_array($this->requestContentType(), $mediaTypes))
            return true;

        throw new HttpException(HttpStatus::NOT_ACCEPTABLE);
    }

    /**
     * Returns a page param's content if there is any, or false if there is none
     *
     * @return bool|string|int
     */
    protected function pageParam()
    {
        $pageParam = Config::get('restext::page_param');

        if (Request::query($pageParam) !== null)
            return Request::query($pageParam);
        else
            return false;
    }

    /**
     * Returns the selected element or all of the Accept Header.
     *
     * @param bool $all
     * @return mixed
     */
    protected function requestAccepts($all = false)
    {
        $fullList = Request::getAcceptableContentTypes();

        return ($all) ? $fullList : $fullList[0];
    }

    /**
     * Returns the normal Content-Type of the Request
     *
     * @return string
     */
    protected function requestContentType()
    {
        $full = Request::header('Content-Type');

        if (strpos($full, ';'))
            return trim(explode(';', $full)[0]);
        return $full;
    }
}