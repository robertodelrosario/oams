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
    Route::get('/showAaccup', 'AuthController@showAaccup');
    Route::get('/showAllUser', 'AuthController@showAllUser');
    Route::delete('/deleteUser/{id}', 'AuthController@deleteUser');
    Route::delete('/deleteSetRole/{userID}/{roleID}', 'AuthController@deleteSetRole');

});

Route::group(['prefix' => '/suc'], function() {
    Route::post('/addCampus/{id}', 'CampusController@addCampus');
    Route::get('/showCampus/{id}', 'CampusController@showCampus');
    Route::delete('/deleteCampus/{id}', 'CampusController@deleteCampus');
    Route::put('/editCampus/{id}', 'CampusController@editCampus');

    Route::post('/addProgram/{id}', 'ProgramController@addProgram');
    Route::get('/showProgram/{id}', 'ProgramController@showProgram');
    Route::delete('/deleteProgram/{id}', 'ProgramController@deleteProgram');
    Route::put('/editProgram/{id}', 'ProgramController@editProgram');

    Route::post('/selectInstrument/{programID}/{instrumentID}', 'ProgramController@selectInstrument');
    Route::get('/showInstrumentProgram/{id}', 'ProgramController@showInstrumentProgram');
    Route::get('/showStatement/{id}', 'ProgramController@showStatement');
    Route::delete('/removeInstrument/{sucID}/{programID}', 'ProgramController@removeInstrument');
});


Route::group(['prefix' => '/taskForce'], function() {
    Route::get('/showTask/{id}', 'UserController@showTask');
    Route::get('/showHeadTask/{id}', 'UserController@showHeadTask');
});

Route::group(['prefix' => '/aaccup'], function() {
    Route::post('/addSuc', 'SUCController@addSuc');
    Route::get('/showSuc', 'SUCController@showSuc');
    Route::delete('/deleteSuc/{id}', 'SUCController@deleteSuc');
    Route::put('/editSuc/{id}', 'SUCController@editSuc');

    Route::get('/showAllPrograms', 'AaccupController@showAllPrograms');
    Route::get('/showApplication', 'AaccupController@showApplication');
    Route::put('/setDate/{id}', 'AaccupController@setDate');
    Route::put('/reject/{id}', 'AaccupController@reject');
    Route::post('/requestAccreditor/{id}', 'AaccupController@requestAccreditor');
    Route::get('/viewAccreditorRequest', 'AaccupController@viewAccreditorRequest');
    Route::delete('/deleteAccreditorRequest/{id}', 'AaccupController@deleteAccreditorRequest');

});

Route::group(['prefix' => '/accreditor'], function() {
    Route::get('/viewRequest/{id}', 'AccreditorController@viewRequest');
    Route::put('/acceptRequest/{id}', 'AccreditorController@acceptRequest');
    Route::put('/rejectRequest/{id}', 'AccreditorController@rejectRequest');
    Route::get('/viewRemark/{id}', 'AccreditorController@viewRemark');

    Route::get('/showProgram/{id}', 'AccreditorController@showProgram');
    Route::get('/showInstrument/{id}/{app_prog}', 'AccreditorController@showInstrument');
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
    Route::post('/createApplication/{id}', 'ApplicationController@createApplication');
    Route::delete('/deleteApplication/{id}', 'ApplicationController@deleteApplication');
    Route::get('/showApplication/{id}', 'ApplicationController@showApplication');

    Route::post('/uploadFile/{id}', 'ApplicationController@uploadFile');
    Route::delete('/deleteFile/{id}', 'ApplicationController@deleteFile');
    Route::get('/viewFile/{id}', 'ApplicationController@viewFile');

    Route::post('/program', 'AppliedProgramController@program');
    Route::delete('/delete/{id}', 'AppliedProgramController@delete');
    Route::get('/showProgram/{id}', 'AppliedProgramController@showProgram');
    Route::get('/showInstrumentProgram/{id}', 'AppliedProgramController@showInstrumentProgram');
    Route::get('/showStatementDocument/{id}', 'AppliedProgramController@showStatementDocument');

    Route::post('/uploadPPP/{id}', 'AppliedProgramController@uploadPPP');
    Route::post('/uploadCompliance/{id}', 'AppliedProgramController@uploadCompliance');
    Route::post('/uploadNarrative/{id}', 'AppliedProgramController@uploadNarrative');
    Route::get('/viewPPP/{id}', 'AppliedProgramController@viewPPP');
    Route::get('/viewCompliance/{id}', 'AppliedProgramController@viewCompliance');
    Route::get('/viewNarrative/{id}', 'AppliedProgramController@viewNarrative');
    Route::delete('/deletePPP/{id}', 'AppliedProgramController@deletePPP');
    Route::delete('/deleteCompliance/{id}', 'AppliedProgramController@deleteCompliance');
    Route::delete('/deleteNarrative/{id}', 'AppliedProgramController@deleteNarrative');

    Route::post('/attachSupportDocument', 'MSIAttachmentController@attachSupportDocument');
    Route::delete('/removeSupportDocument/{id}', 'MSIAttachmentController@removeSupportDocument');
    Route::get('/viewSupportDocument/{id}', 'MSIAttachmentController@viewSupportDocument');

    Route::get('/showStatementDocument/{id}/{transactionID}', 'MSIController@showStatementDocument');

    Route::post('/uploadDummyDocument', 'MSITransactionController@uploadDummyDocument');
    Route::get('/showDummyDocument', 'MSITransactionController@showDummyDocument');

    Route::post('/assignTask/{id}/{app_prog_id}', 'AssignTaskController@assignTask');
    //Route::get('/showTask/{id}', 'AssignTaskController@showTask');
    //Route::get('/showTaskUser/{id}', 'AssignTaskController@showTaskUser');
    Route::delete('/deleteAssignedUser/{userID}/{transactionID}', 'AssignTaskController@deleteAssignedUser');

    Route::post('/assignHeadTask/{id}', 'AssignTaskController@assignHeadTask');
    Route::delete('/deleteAssignedHeadUser/{userID}/{transactionID}', 'AssignTaskController@deleteAssignedHeadUser');

    Route::put('/setScore', 'MSIEvaluationController@setScore');

});

