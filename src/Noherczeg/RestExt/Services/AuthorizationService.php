<?php

namespace Noherczeg\RestExt\Services;


interface AuthorizationService {

    /**
     * Checks if the authenticated User has any of the given Roles (names) specified in the parameter array
     *
     * @param array $roles  Array of strings
     * @return boolean
     */
    public function hasRoles(array $roles);

    /**
     * Checks if the authenticated User has the given Role or not
     *
     * @param string $role
     * @return boolean
     */
    public function hasRole($role);

} 