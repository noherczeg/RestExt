<?php

use Noherczeg\RestExt\Http\Resource;
use Noherczeg\RestExt\Services\ResponseComposer;
use Noherczeg\RestExt\Services\AuthorizationService;

class RootController extends RestExtController {

    public function __construct(ResponseComposer $rc, AuthorizationService $as)
    {
        parent::__construct();
        $this->responseComposer = $rc;
        $this->authorizationService = $as;
    }

    public function discover()
    {
        $resource = new Resource();

        $resource->addLink($this->createLinkToFirstPage('users'));
        /*if ($this->authorizationService->hasRoles(['ADMIN'])) */$resource->addLink($this->createLink('roles'));

        // etc...

        return $this->responseComposer->sendResource($resource);
    }
} 