<?php

use Noherczeg\RestExt\Providers\Charset;
use Noherczeg\RestExt\Providers\MediaType;

return array(

    /*
    |--------------------------------------------------------------------------
    | Media Type of Responses
    |--------------------------------------------------------------------------
    |
    | Default Media Type of Responses sent back to clients. Not only does it
    | set the headers, but the serialization will be handled accordingly as
    | well.
    |
    | This value can be overriden in Controllers.
    |
    | Default:  MediaType::APPLICATION_JSON
    |
    */

    'media_type' => MediaType::APPLICATION_JSON,

    /*
    |--------------------------------------------------------------------------
    | Response Encoding
    |--------------------------------------------------------------------------
    |
    | You can set the default Encoding of your Responses here, but it's not
    | a great practice to do so since according to RFC4627 the advised
    | encoding of JSON Responses is UTF-8!
    |
    | This configuration only sets the Response Headers, and is not involved in
    | the conversion/handling of the actual data in them!
    |
    | Default: Charset::UTF8
    |
    */

    'encoding' => Charset::UTF8,

    /*
    |--------------------------------------------------------------------------
    | Exception on Out Of Bounds Error
    |--------------------------------------------------------------------------
    |
    | Turns on / off Exceptions when a user requests pages which don't exist
    | on a particular Resource. These Exceptions should be caught in the
    | filters.php file and handled accordingly (404 Responses).
    |
    | Default: true
    |
    */

    'paging_out_of_bounds_exception' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Access to Resource
    |--------------------------------------------------------------------------
    |
    | Determines the default access control behavior of the app.
    |
    | If set to "whitelist", then access to a certain Resource will be denied
    | unless it is set otherwise in a particular Controller method, or
    | constructor.
    |
    | If set to "blacklist", then the exact opposite behavior should be
    | expected: everyone will have access to all the Resources except to ones
    | which have restrictive controls over them!
    |
    | Default: 'blacklist'
    |
    */

    'access_policy' => 'blacklist',

    /*
    |--------------------------------------------------------------------------
    | 406 Error
    |--------------------------------------------------------------------------
    |
    | Determines what to do if the produces() method is set in a method call,
    |  - true: If the Accept Header doesn't match the produces type, then a 406
    |       page will be generated
    |  - false: the Accept Header doesn't generate any errors if doesn't match
    |       the produces value(es)
    |
    */

    'restrict_accept' => false,

    /*
    |--------------------------------------------------------------------------
    | MediaType preference
    |--------------------------------------------------------------------------
    |
    | Not yet used!
    |
    | Should set wether the Accept Header should be prioritized, or the extension.
    |
    */

    'prefer_accept' => true,

    /*
    |--------------------------------------------------------------------------
    | Pagination Parameter name
    |--------------------------------------------------------------------------
    |
    | Name of the Query String Parameter which is used for pagination. If using
    | an Eloquent Repository this should not be changed!
    |
    */

    'page_param' => 'page',

    /*
    |--------------------------------------------------------------------------
    | HTTP Realm Name
    |--------------------------------------------------------------------------
    |
    | Name of the Realm used with HTTP operations (usually with basic auth).
    |
    */

    'realm' => 'My Realm',

    /*
    |--------------------------------------------------------------------------
    | List of available Languages
    |--------------------------------------------------------------------------
    |
    | Just a list of langauge codes which are available for Localization.
    |
    | Not yet used by RestExt.
    |
    */

    'available_languages' => ['en'],


    'version' => 'v1'

);