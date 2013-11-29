<?php

namespace Noherczeg\RestExt\Facades;


use Illuminate\Support\Facades\Facade;

class RestExt extends Facade {

    /**
     * Get the registered component.
     *
     * @return object
     */
    protected static function getFacadeAccessor(){ return 'restext'; }

} 