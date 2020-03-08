<?php

$router->get('/', function () {
    return response()->json([
        'api' => 'RESTfull',
        'description' => 'RESTfull API'
    ]);
});

$router->post('auth/login', [
    'uses' => 'AuthController@authenticate',
    'as' => 'api.auth.login'
]);

$router->group(['middleware' => 'jwt.auth'], function () use ($router) {
    ///////////// auth methods
    $router->get('/auth/me', [
        'uses' => 'AuthController@me',
        'as' => 'api.auth.me'
    ]);

    $router->get('/auth/users/{id}', [
        'uses' => 'AuthController@getUser',
        'as' => 'api.auth.get'
    ]);

    $router->get('/auth/users', [
        'uses' => 'AuthController@getUsers',
        'as' => 'api.auth.getAll'
    ]);

    $router->post('/auth/users', [
        'uses' => 'AuthController@createUser',
        'as' => 'api.auth.create'
    ]);

    $router->delete('/auth/users/{id}', [
        'uses' => 'AuthController@delete',
        'as' => 'api.auth.delete'
    ]);

    $router->put('/auth/users/{id}', [
        'uses' => 'AuthController@update',
        'as' => 'api.auth.update'
    ]);

});
