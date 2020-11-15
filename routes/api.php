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

//ETL Route (For Development Debug Only)


//News Aggregator Data View Route
//Country
$router->group(['prefix' => 'country'], function () use ($router) {
	$router->get('/',					'CountryController@index');
	$router->get('{id}', 				'CountryController@show');
});

//News Category
$router->group(['prefix' => 'category'], function () use ($router) {
	$router->get('/',					'NewsCategoryController@index');
	$router->get('{id}', 				'NewsCategoryController@show');
});

//News Source
$router->group(['prefix' => 'source'], function () use ($router) {
	$router->get('/search',				'NewsSourceController@search');
	$router->get('/',					'NewsSourceController@index');
	$router->get('{id}', 				'NewsSourceController@show');
});

//News
$router->group(['prefix' => 'news'], function () use ($router) {
	$router->get('/search',				'NewsDashboardController@search');
	$router->get('/search-title',		'NewsDashboardController@searchTitleOnly');
	$router->get('/published',			'NewsFilterController@getByPublishedDate');
	$router->get('/filter',				'NewsFilterController@newsFilter');
	$router->get('/',					'NewsDashboardController@index');
	$router->get('{id}', 				'NewsDashboardController@show');
	$router->get('{id}/category', 		'NewsDashboardController@getByCategory');
	$router->get('{id}/country', 		'NewsDashboardController@getByCountry');
	$router->get('{id}/source', 		'NewsDashboardController@getBySource');
});