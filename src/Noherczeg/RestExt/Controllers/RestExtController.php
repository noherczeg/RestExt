<?php

namespace Noherczeg\RestExt\Controllers;

use ColladAPI\Exceptions\PermissionException;
use Illuminate\Routing\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Noherczeg\RestExt\Providers\HttpStatus;
use Noherczeg\RestExt\Services\ResponseComposer;

class RestExtController extends Controller
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

        $securityRoles = $this->securityRoles;
        $accessPolicy = $this->accessPolicy;

        // default action to prevent processing / returning of content if by default the access policy is set to
        // "whitelist" and no allowed roles have been set.
        $this->afterFilter(function() use ($securityRoles, $accessPolicy)
        {
            if ($accessPolicy == 'whitelist' && count($securityRoles) == 0)
                throw new PermissionException();
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
     * @throws \ColladAPI\Exceptions\PermissionException
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

    public function produce(array $mediaTypes)
    {
        if (in_array($this->mediaTypeWildcard, $mediaTypes))
            return true;

        foreach(Request::getAcceptableContentTypes() as $contentType) {
            if (!in_array($contentType, $mediaTypes) && Config::get('restext::restrict_accept'))
                App::abort(HttpStatus::UNSUPPORTED_MEDIA_TYPE, 'Requested MediaType is not supported');
        }
    }

    public function consume(array $mediaTypes)
    {
        if ($this->requestContentType() == null || in_array($this->requestContentType(), $mediaTypes))
            return true;

        App::abort(406, 'Provided Content Type is not allowed');
    }

    /**
     * Returns a page param's content if there is any, or false if there is none
     *
     * @return bool|string|int
     */
    public function pageParam()
    {
        $pageParam = Config::get('restext::page_param');

        if (Request::query($pageParam) !== null)
            return Request::query($pageParam);
        else
            return false;
    }

    /**
     * Returns the normal Content-Type of the Request
     *
     * @return string
     */
    private function requestContentType()
    {
        $full = Request::header('Content-Type');

        if (strpos($full, ';'))
            return trim(explode(';', $full)[0]);
        return $full;
    }
}