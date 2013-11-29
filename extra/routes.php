<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Versioning
|--------------------------------------------------------------------------
| REST APIs should be versioned by either a URL prefix, or by Request
| headers. In this case we use prefixes.
*/

Route::get('/', function()
{
    return Redirect::to('/v1', 301);
});

Route::group(array('prefix' => 'v1', 'before' => 'api.auth'), function()
{
    Route::get('/', 'RootController@discover');
    Route::get('users', 'Usersontroller@index');
    Route::get('users/{id}', 'UsersController@show');

    // etc...

});
