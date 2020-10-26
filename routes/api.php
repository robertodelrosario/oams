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
    Route::post('register/{id}', 'AuthController@register');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('setRole', 'AuthController@setRole');
    Route::get('/showUser/{id}', 'AuthController@showUser');

   /*
    Route::group(['prefix' => '/suc'], function() {
        Route::post('/addSuc', 'SUCController@addSuc');
        Route::get('/showSuc', 'SUCController@showSuc');
        Route::delete('/deleteSuc/{id}', 'SUCController@deleteSuc');

        Route::post('/addProgram', 'ProgramController@addProgram');
        Route::get('/showProgram/{id}', 'ProgramController@showProgram');
    });
    Route::group(['prefix' => '/instrument'], function() {
        Route::post('/createInstrument', 'InstrumentController@createInstrument');
        Route::post('/createStatement', 'StatementController@createStatement');
        Route::post('/createParameter', 'ParameterController@createParameter');
        Route::get('/showParameter/{id}', 'ParameterController@showParameter');
        Route::get('/showInstrument', 'InstrumentController@showInstrument');
        Route::get('/showStatement/{id}', 'StatementController@showStatement');
        Route::delete('/deleteParameter/{id}', 'ParameterController@deleteParameter');
        Route::delete('/deleteInstrument/{id}', 'InstrumentController@deleteInstrument');
        Route::put('/editStatement', 'StatementController@editStatement');
    });

    Route::group(['prefix' => '/application'], function() {
        Route::post('/application/{id}', 'ApplicationController@application');
        Route::delete('/deleteApplication/{id}', 'ApplicationController@deleteApplication');
        Route::get('/showApplication/{id}', 'ApplicationController@showApplication');
        Route::get('/viewFile/{id}', 'ApplicationController@viewFile');

        Route::post('/program', 'AppliedProgramController@program');
        Route::post('/uploadDocument', 'AppliedProgramController@uploadDocument');
        Route::delete('/delete/{id}', 'AppliedProgramController@delete');
        Route::get('/showProgram/{id}', 'AppliedProgramController@showProgram');
    });*/

});

Route::group(['prefix' => '/suc'], function() {
    Route::post('/addSuc', 'SUCController@addSuc');
    Route::get('/showSuc', 'SUCController@showSuc');
    Route::delete('/deleteSuc/{id}', 'SUCController@deleteSuc');

    Route::post('/addProgram', 'ProgramController@addProgram');
    Route::get('/showProgram/{id}', 'ProgramController@showProgram');
});
Route::group(['prefix' => '/instrument'], function() {
    Route::post('/createInstrument', 'InstrumentController@createInstrument');
    Route::post('/createStatement', 'StatementController@createStatement');
    Route::post('/createParameter', 'ParameterController@createParameter');
    Route::get('/showParameter/{id}', 'ParameterController@showParameter');
    Route::get('/showInstrument', 'InstrumentController@showInstrument');
    Route::get('/showStatement/{id}', 'StatementController@showStatement');
    Route::delete('/deleteParameter/{id}', 'ParameterController@deleteParameter');
    Route::delete('/deleteInstrument/{id}', 'InstrumentController@deleteInstrument');
    Route::put('/editStatement', 'StatementController@editStatement');
});

Route::group(['prefix' => '/application'], function() {
    Route::post('/application/{id}', 'ApplicationController@application');
    Route::delete('/deleteApplication/{id}', 'ApplicationController@deleteApplication');
    Route::get('/showApplication/{id}', 'ApplicationController@showApplication');
    Route::get('/viewFile/{id}', 'ApplicationController@viewFile');

    Route::post('/program', 'AppliedProgramController@program');
    Route::post('/uploadDocument', 'AppliedProgramController@uploadDocument');
    Route::delete('/delete/{id}', 'AppliedProgramController@delete');
    Route::get('/showProgram/{id}', 'AppliedProgramController@showProgram');
});
