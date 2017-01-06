<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');
//redirect user to facebook login page
Route::get('auth/facebook', 'Auth\RegisterController@redirectToProvider');
//get user information from callback url via facebook
Route::get('auth/facebook/callback', 'Auth\RegisterController@handleProviderCallback');
