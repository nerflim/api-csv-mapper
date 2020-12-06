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
    return $router->app->version();
});

// Contacts
$router->group(['prefix' => 'contacts'], function () use ($router) {
    $router->get('/', ['as' => 'contacts', 'uses' => 'ContactController@index']);
    $router->post('/import', ['as' => 'contacts-import', 'uses' => 'ContactController@import']);
});