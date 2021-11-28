<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return response("Hello World", 200);
});

$router->group([
    'middleware' => 'auth',
    'prefix' => 'api'
], function ($router) {
    $router->post('logout', ['uses' => 'AuthController@logout', 'as' => 'logout']);
    $router->post('refresh', 'AuthController@refresh');
    $router->post('me', ['uses' => 'AuthController@me', 'as' => 'myself']);
    $router->post('comments', ['uses' => 'CommentController@create', 'as' => 'createComment']);
    $router->delete('comments/{id}', ['uses' => 'CommentController@deactivate', 'as' => 'deactivateComment']);
    $router->delete('posts/{id}', ['uses' => 'PostController@deactivate', 'as' => 'deactivatePost']);
});


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('/', function () {
        return response()->json(["result" => ["message" => "Welcome to the CMS api"], "count" => 0], 200);
    });
    $router->post('login', ['uses' => 'AuthController@login', 'as' => 'login']);
    $router->get('comments', ['uses' => 'CommentController@comments', 'as' => 'getComments']);
    $router->get('posts', ['uses' => 'PostController@posts', 'as' => 'getPosts']);
});
