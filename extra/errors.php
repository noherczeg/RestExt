<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Response;
use Noherczeg\RestExt\Exceptions\ErrorMessageException;
use Noherczeg\RestExt\Exceptions\NotFoundException;
use Noherczeg\RestExt\Exceptions\PermissionException;

/*
|--------------------------------------------------------------------------
| Error Logging
|--------------------------------------------------------------------------
|
| We log any errors in our App.
|
*/

App::error(function(Exception $exception, $code)
{
    Log::error($exception);
});

/*
|--------------------------------------------------------------------------
| HTTP Exceptions
|--------------------------------------------------------------------------
|
| HTTP Exceptions are translated to proper REST Responses.
|
| TODO Content should be loaded with the Localization tool
|
*/

App::error(function(Symfony\Component\HttpKernel\Exception\HttpException $e, $code)
{
    $headers = $e->getHeaders();
    $content = ["content" => null, "links" => [
        ["rel" => "self", "href" => URL::full()],
    ]];

    switch ($code)
    {
        case 401:
            $content['content'] = 'Invalid API key';
            $headers['WWW-Authenticate'] = 'Basic realm="' . Config::get('restext::realm') . '"';
            break;

        case 403:
            $content['content'] = 'Access denied';
            break;

        case 404:
            $content['content'] = 'Requested Resource not found';
            break;

        case 406:
            $content['content'] = 'Given Content-Type not acceptable';
            break;

        default:
            $content['content'] = 'An unknown error occured';
    }

    return Response::json($e->getMessage() ?: $content, $code, $headers);
});

/*
|--------------------------------------------------------------------------
| Application Error Messages
|--------------------------------------------------------------------------
|
| Error Messages set ususaly when a user error occures.
|
*/

App::error(function(ErrorMessageException $e)
{
    return Response::json([
        'reason' => $e->getMessages()->all(),
        'links' => [['rel' => 'self', 'href' => URL::full()]]
    ], 400);
});

/*
|--------------------------------------------------------------------------
| 404 Error
|--------------------------------------------------------------------------
|
|
*/

App::error(function(NotFoundException $e)
{
    return Response::json([
        'reason' => $e->getMessage() ?: 'Requested page not found',
        'links' => [['rel' => 'self', 'href' => URL::full()]]
    ], 404);
});

/*
|--------------------------------------------------------------------------
| Permission Error
|--------------------------------------------------------------------------
|
| Unauthorized errors are handled here.
|
*/

App::error(function(PermissionException $e)
{
    return Response::json([
        'reason' => $e->getMessage() ?: 'Access denied',
        'links' => [['rel' => 'self', 'href' => URL::full()]]
    ], 403);
});

/*
|--------------------------------------------------------------------------
| Repository Error
|--------------------------------------------------------------------------
|
| Sent when an Entity couldn't be found in the Repositories.
|
*/

App::error(function(ModelNotFoundException $e)
{
    return Response::json([
        'reason' => 'Requested Resource not found',
        'links' => [['rel' => 'self', 'href' => URL::full()]]
    ], 404);
});

/*
|--------------------------------------------------------------------------
| Database Error
|--------------------------------------------------------------------------
|
| Redis doesn't respond to Requests
|
*/
App::error(function(\Predis\Connection\ConnectionException $e)
{
    return Response::json([
        'reason' => 'The Cache server is not responding',
        'links' => [['rel' => 'self', 'href' => URL::full()]]
    ], 500);
});

/*
|--------------------------------------------------------------------------
| Database Error
|--------------------------------------------------------------------------
|
| The Relational Database doesn't respond to Requests.
|
*/
App::error(function(\Doctrine\DBAL\ConnectionException $e)
{
    return Response::json([
        'reason' => 'The Database server is not responding',
        'links' => [['rel' => 'self', 'href' => URL::full()]]
    ], 500);
});
