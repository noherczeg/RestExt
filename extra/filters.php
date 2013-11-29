<?php

/*
|--------------------------------------------------------------------------
| HTTP Localization handler
|--------------------------------------------------------------------------
|
| Custom handler which observes the HTTP Accept-Language header and if it
| exists, and contains an available language code, then the app
| automatically sets it as the locale.
|
*/

App::before(function($request)
{
    foreach ($request->getLanguages() as $requestLanguage) {
        if (in_array($requestLanguage, Config::get('app.available_languages'))) {
            App::setLocale($requestLanguage);
            break;
        }
    }
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest()) return Redirect::guest('login');
});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});

Route::filter('basic.once', function()
{
    return Auth::onceBasic();
});

Route::filter('api.auth', function()
{
    if (!Request::getUser())
    {
        App::abort(401, 'A valid API key is required');
    }

    $user = User::where('api_key', '=', Request::getUser())->first();

    if (!$user)
    {
        App::abort(401);
    }

    Auth::login($user);
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});