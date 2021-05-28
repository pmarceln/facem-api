<?php

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


// $router->post("/getallData", "DataController@getAllData");


// $router->group(["prefix"=> "auth"], function () use ($router) {
//     $router->post("/register", "AuthController@register");
//     $router->post("/login", ["uses" => "AuthController@authenticate"]);
// });

/**
 * Routes for categories and there items
 */
$router->group(['prefix' => 'api'], function () use ($router) {

    $router->post("login", ["uses" => "AuthController@authenticate"]);

    $router->get('getallData',['uses'=>'DataController@getAllData']);
    // $router->post('filterIsActive',['uses'=>'DataController@updateFilterIsActive']);

    $router->group(["middleware" => "jwt.auth"],
        function () use ($router) {
            $router->post("/user/activate", "UserController@activate");
            $router->post('/filterIsActive',['uses'=>'DataController@updateFilterIsActive']);
            $router->post('/projectIsActive',['uses'=>'DataController@updateProjectIsActive']);
            $router->post('/addEditFilter',['uses'=>'DataController@addEditFilter']);
            $router->post('/deleteFilter',['uses'=>'DataController@deleteFilter']);
            $router->post("/addProject", ['uses'=>'DataController@addProject']);
            $router->post("/editProject", ['uses'=>'DataController@editProject']);
            $router->post("/updateProjectPhotosDescriptionAndOrder", ['uses'=>'DataController@updateProjectPhotosDescriptionAndOrder']);
        }
    );

});
