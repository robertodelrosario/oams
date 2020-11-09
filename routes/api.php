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
    Route::post('registerSucUser/{id}', 'AuthController@registerSucUser');
    Route::post('registerAaccupAccreditor', 'AuthController@registerAaccupAccreditor');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('setRole/{userID}', 'AuthController@setRole');
    Route::get('/showSucUser/{id}', 'AuthController@showSucUser');
    Route::get('/showAaccupAccreditor', 'AuthController@showAaccupAccreditor');
    Route::get('/showAllUser', 'AuthController@showAllUser');
    Route::delete('/deleteUser/{id}', 'AuthController@deleteUser');
    Route::delete('/deleteSetRole/{userID}/{roleID}', 'AuthController@deleteSetRole');

});

Route::group(['prefix' => '/suc'], function() {
    Route::post('/addSuc', 'SUCController@addSuc');
    Route::get('/showSuc', 'SUCController@showSuc');
    Route::delete('/deleteSuc/{id}', 'SUCController@deleteSuc');

    Route::post('/addProgram', 'ProgramController@addProgram');
    Route::get('/showProgram/{id}', 'ProgramController@showProgram');
    Route::post('/selectInstrument/{programID}/{instrumentID}', 'ProgramController@selectInstrument');
});
Route::group(['prefix' => '/instrument'], function() {
    Route::post('/createInstrument', 'InstrumentController@createInstrument');
    Route::post('/cloneInstrument', 'InstrumentController@cloneInstrument');
    Route::post('/createStatement', 'StatementController@createStatement');
    Route::post('/createParameter', 'ParameterController@createParameter');
    Route::get('/showParameter/{id}', 'ParameterController@showParameter');
    Route::get('/showInstrument', 'InstrumentController@showInstrument');
    Route::get('/showStatement/{id}', 'StatementController@showStatement');
    Route::delete('/deleteParameter/{id}', 'ParameterController@deleteParameter');
    Route::delete('/deleteInstrument/{id}', 'InstrumentController@deleteInstrument');
    Route::delete('/deleteStatement/{instrumentID}/{statementID}', 'StatementController@deleteStatement');
    Route::put('/editStatement', 'StatementController@editStatement');
    Route::put('/editInstrument/{id}', 'InstrumentController@editInstrument');
    Route::put('/editParameter/{id}', 'ParameterController@editParameter');

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
    Route::get('/showInstrumentProgram/{id}', 'AppliedProgramController@showInstrumentProgram');

    Route::post('/attachSupportDocument', 'MSITransactionController@attachSupportDocument');
    Route::get('/showTransactionInstrument/{id}', 'MSITransactionController@showTransactionInstrument');
    Route::delete('/removeSupportDocument/{id}', 'MSITransactionController@removeSupportDocument');

    Route::post('/uploadDummyDocument', 'MSITransactionController@uploadDummyDocument');
    Route::get('/showDummyDocument', 'MSITransactionController@showDummyDocument');

    Route::post('/assignTask/{id}', 'AssignTaskController@assignTask');
    Route::delete('/deleteAssignedUser/{userID}/{transactionID}', 'AssignTaskController@deleteAssignedUser');
});
