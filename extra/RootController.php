<?php

use Noherczeg\RestExt\Facades\RestLinker;
use Noherczeg\RestExt\Facades\RestResponse;
use Noherczeg\RestExt\Http\Resource;
use Noherczeg\RestExt\Services\AuthorizationService;

class RootController extends RestExtController {

    public function __construct(AuthorizationService $as)
    {
        parent::__construct();
        $this->authorizationService = $as;
    }

    public function discover()
    {
        $resource = new Resource();

        $resource->addLink(RestLinker::createLinkToFirstPage('users'));

        // viewing the list of roles should be only allowed for admins
        if ($this->authorizationService->hasRoles(['ADMIN'])) $resource->addLink(RestLinker::createLink('roles'));

        // etc...

        return RestResponse::sendResource($resource);
    }
} 