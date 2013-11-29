<?php

namespace Noherczeg\RestExt\Entities;


interface AuthorizationSupport {

    /**
     * Returns an array of Role names for a User
     *
     * @return array
     */
    public function roles();

} 