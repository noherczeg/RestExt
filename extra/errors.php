<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Response;
use Noherczeg\RestExt\Exceptions\ErrorMessageException;
use Noherczeg\RestExt\Exceptions\NotFoundException;
use Noherczeg\RestExt\Exceptions\PermissionException;

App::error(function(Exception $exception, $code) {
    Log::error($exception);
});

App::error(function(Symfony\Component\HttpKernel\Exception\HttpException $e, $code) {
    $headers = $e->getHeaders();
    $content = ["content" => null, "links" => [
        ["rel" => "self", "href" => URL::full()],
    ]];

    switch ($code)
    {
        case 401:
            $content['content'] = 'Invalid API key';
            $headers['WWW-Authenticate'] = 'Basic realm="Your Realm"';
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

App::error(function(ErrorMessageException $e) {
    $messages = $e->getMessages()->all();

    return Response::json([ 'reason' => $messages[0], ], 400);
});

App::error(function(NotFoundException $e) {
    $default_message = 'Requested page not found';

    return Response::json([ 'reason' => $e->getMessage() ?: $default_message, ], 404);
});

App::error(function(PermissionException $e) {
    $default_message = 'Access denied';

    return Response::json([ 'reason' => $e->getMessage() ?: $default_message, ], 403);
});

App::error(function(ModelNotFoundException $e)
{
    return Response::json(['reason' => 'Requested Resource not found'], 404);
});

App::error(function(\Predis\Connection\ConnectionException $e)
{
    return Response::json(['reason' => 'The Cache server is not responding'], 500);
});

App::error(function(\Doctrine\DBAL\ConnectionException $e)
{
    return Response::json(['reason' => 'The Database server is not responding'], 500);
});
