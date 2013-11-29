<?php

use Illuminate\Support\Facades\Route;

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
