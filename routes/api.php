<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
});
Route::group(['prefix' => '/suc'], function() {
    Route::post('/addCampus', 'CampusController@addCampus');
    Route::post('/addProgram', 'ProgramController@addProgram');
});
Route::group(['prefix' => '/instrument'], function() {
    Route::post('/createInstrument', 'InstrumentController@createInstrument');
    Route::post('/createStatement', 'InstrumentController@createStatement');
    Route::post('/createParameter', 'InstrumentController@createParameter');
    Route::get('/showParameter/{id}', 'InstrumentController@showParameter');
    Route::get('/showInstrument/{id}', 'InstrumentController@showInstrument');
    Route::get('/showStatement/{id}', 'InstrumentController@showStatement');
    Route::delete('/deleteParameter/{id}', 'InstrumentController@deleteParameter');
    Route::delete('/deleteInstrument/{id}', 'InstrumentController@deleteInstrument');
});


