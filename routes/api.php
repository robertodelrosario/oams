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
    Route::post('registerLocalAccreditor/{id}', 'AuthController@registerLocalAccreditor');
    Route::post('registerAaccupAccreditor', 'AuthController@registerAaccupAccreditor');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('/addToOffice/{id}/{office_id}', 'AuthController@addToOffice');
    Route::put('/removeFromOffice/{id}', 'AuthController@removeFromOffice');
    Route::post('setRole/{userID}', 'AuthController@setRole');
    Route::get('/showCampusUser/{id}', 'AuthController@showCampusUser');
    Route::get('/showAaccup', 'AuthController@showAaccup');
    Route::get('/showAccreditor', 'AuthController@showAccreditor');
    Route::get('/showLocalAccreditor/{id}', 'AuthController@showAccreditor');
    Route::get('/showAllUser', 'AuthController@showAllUser');
    Route::put('/deleteUser/{id}', 'AuthController@deleteUser');
    Route::put('/activateUser/{id}', 'AuthController@activateUser');
    Route::delete('/deleteSetRole/{userID}/{roleID}', 'AuthController@deleteSetRole');

});

Route::group(['prefix' => '/suc'], function() {
    Route::post('/addCampus/{id}', 'CampusController@addCampus');
    Route::get('/showCampus/{id}', 'CampusController@showCampus');
    Route::get('/showAllCampus', 'CampusController@showAllCampus');
//    Route::delete('/deleteCampus/{id}', 'CampusController@deleteCampus');
    Route::put('/editCampus/{id}', 'CampusController@editCampus');

    Route::post('/createOffice/{id}', 'OfficeController@createOffice');
    Route::get('/showOffice/{id}', 'OfficeController@showOffice');
    Route::delete('/deleteOffice/{id}', 'OfficeController@deleteOffice');
    Route::put('/editOffice/{id}', 'OfficeController@editOffice');

    Route::post('/addProgram/{id}', 'ProgramController@addProgram');
    Route::get('/showProgram/{id}', 'ProgramController@showProgram');
    Route::delete('/deleteProgram/{id}', 'ProgramController@deleteProgram');
    Route::put('/editProgram/{id}', 'ProgramController@editProgram');

    Route::post('/selectInstrument/{programID}/{intendedProgramID}', 'ProgramController@selectInstrument');
    Route::get('/showInstrumentProgram/{id}', 'ProgramController@showInstrumentProgram');
    Route::get('/showStatement/{id}', 'ProgramController@showStatement');
    Route::delete('/removeInstrument/{programID}/{instrumentID}', 'ProgramController@removeInstrument');

    Route::post('/acceptDeclineReschedule/{id}/{userID}', 'QuasController@acceptDeclineReschedule');


});

Route::group(['prefix' => '/task'], function() {
    Route::get('/showProgram/{id}', 'UserController@showProgram');
    Route::get('/showInstrument/{id}/{app_prog}', 'UserController@showInstrument');
    Route::get('/showParameter/{id}/{app_prog}', 'UserController@showParameter');
    Route::get('/showParameterInternal/{id}/', 'UserController@showParameterInternal');
    Route::get('/showProgramHead/{id}', 'UserController@showProgramHead');
    Route::get('/showInstrumentHead/{app_prog}', 'UserController@showInstrumentHead');
});

Route::group(['prefix' => '/aaccup'], function() {
    Route::post('/addSuc', 'SUCController@addSuc');
    Route::get('/showSuc', 'SUCController@showSuc');
    Route::delete('/deleteSuc/{id}', 'SUCController@deleteSuc');
    Route::put('/editSuc/{id}', 'SUCController@editSuc');

    Route::get('/showAllProgram', 'AaccupController@showAllProgram');
    Route::get('/showApplication', 'AaccupController@showApplication');
    Route::get('/showProgram/{id}', 'AaccupController@showProgram');
    Route::put('/approve/{id}', 'AaccupController@approve');
    Route::post('/rechedule/{id}/{userID}', 'AaccupController@rechedule');
    Route::put('/reject/{id}', 'AaccupController@reject');
    Route::post('/requestAccreditor/{id}', 'AaccupController@requestAccreditor');
    Route::post('/request/{userID}/{id}', 'AaccupController@request');
    Route::post('/addRequest/{userID}/{id}', 'AaccupController@addRequest');
    Route::put('/editRequest/{id}', 'AaccupController@editRequest');
    Route::put('/setAccreditorLead/{id}', 'AaccupController@setAccreditorLead');
    Route::get('/viewAccreditorRequest', 'AaccupController@viewAccreditorRequest');
    Route::delete('/deleteAccreditorRequest/{id}', 'AaccupController@deleteAccreditorRequest');

    Route::put('/setAcceptableScoreGap/{id}', 'AaccupController@setAcceptableScoreGap');
    Route::get('/showAcceptableScoreGap/{id}', 'AaccupController@showAcceptableScoreGap');
    Route::put('/editAcceptableScoreGap', 'AaccupController@editAcceptableScoreGap');
    Route::delete('/removeAcceptableScoreGap/{id}', 'AaccupController@removeAcceptableScoreGap');
});

Route::group(['prefix' => '/document'], function() {
    Route::post('/uploadDocument/{userID}/{id}', 'DocumentController@uploadDocument');
    Route::get('/showDocument/{id}', 'DocumentController@showDocument');
    Route::delete('/deleteDocument/{id}', 'DocumentController@deleteDocument');
    Route::put('/removeDocument/{id}', 'DocumentController@removeDocument');
    Route::get('/viewFile/{id}', 'DocumentController@viewFile');
    Route::post('/makeDocumentList/{id}', 'DocumentController@makeDocumentList');
    Route::put('/editDocumentName/{id}', 'DocumentController@editDocumentName');
    Route::post('/addTag/{id}', 'DocumentController@addTag');
    Route::delete('/deleteTag/{id}', 'DocumentController@deleteTag');

});

Route::group(['prefix' => '/accreditor'], function() {
    Route::get('/viewRequest/{id}', 'AccreditorController@viewRequest');
    Route::put('/acceptRequest/{id}', 'AccreditorController@acceptRequest');
    Route::put('/rejectRequest/{id}', 'AccreditorController@rejectRequest');
    Route::get('/viewRemark/{id}', 'AccreditorController@viewRemark');

    Route::get('/showProgram/{id}', 'AccreditorController@showProgram');
    Route::get('/showInstrument/{id}/{app_prog}', 'AccreditorController@showInstrument');
    Route::get('/showParameter/{id}', 'AccreditorController@showParameter');
});

Route::group(['prefix' => '/instrument'], function() {
    Route::post('/createInstrument', 'InstrumentController@createInstrument');
    Route::post('/cloneInstrument/{id}', 'InstrumentController@cloneInstrument');
//    Route::post('/createStatement', 'StatementController@createStatement');
    Route::post('/createStatement/{id}', 'StatementController@createStatement');
    Route::post('/createParameter', 'ParameterController@createParameter');
    Route::get('/showProgram', 'InstrumentController@showProgram');
    Route::get('/showParameter/{id}', 'ParameterController@showParameter');
    Route::get('/showInstrument/{id}', 'InstrumentController@showInstrument');
    Route::get('/showStatement/{id}', 'StatementController@showStatement');
    Route::delete('/deleteParameter/{id}', 'ParameterController@deleteParameter');
    Route::delete('/deleteProgram/{id}', 'InstrumentController@deleteProgram');
    Route::delete('/deleteStatement/{instrumentID}/{statementID}', 'StatementController@deleteStatement');
    Route::put('/editStatement', 'StatementController@editStatement');
    Route::put('/editProgram/{id}', 'InstrumentController@editProgram');
    Route::put('/editParameter/{id}', 'ParameterController@editParameter');

});

Route::group(['prefix' => '/notification'], function() {
    Route::get('/showAllNotification/{id}', 'NotificationController@showAllNotification');
    Route::get('/viewNotication/{id}', 'NotificationController@viewNotication');
    Route::delete('/deleteNotification/{id}', 'NotificationController@deleteNotification');
});

Route::group(['prefix' => '/report'], function() {
    Route::get('/generateAreaSAR/{id}/{app_prog}', 'ReportController@generateAreaSAR');
});

Route::group(['prefix' => '/application'], function() {
    Route::post('/createApplication/{sucID}/{userID}', 'ApplicationController@createApplication');
    Route::post('/submitApplication/{id}/{sucID}', 'ApplicationController@submitApplication');
    Route::delete('/deleteApplication/{id}', 'ApplicationController@deleteApplication');
    Route::get('/showApplication/{id}', 'ApplicationController@showApplication');
//    Route::get('/showSubmittedApplication/{id}', 'ApplicationController@showSubmittedApplication');

    Route::post('/uploadFile/{id}', 'ApplicationController@uploadFile');
    Route::delete('/deleteFile/{id}', 'ApplicationController@deleteFile');
    Route::get('/viewFile/{id}', 'ApplicationController@viewFile');

    Route::post('/program', 'AppliedProgramController@program');
    Route::delete('/delete/{id}', 'AppliedProgramController@delete');
    Route::put('/edit/{id}', 'AppliedProgramController@edit');
    Route::get('/showProgram/{id}', 'AppliedProgramController@showProgram');
    Route::get('/programList/{id}', 'AppliedProgramController@programList');
    Route::get('/showInstrumentProgram/{id}', 'AppliedProgramController@showInstrumentProgram');
    Route::get('/showStatementDocument/{id}', 'AppliedProgramController@showStatementDocument');

    Route::post('/uploadFile/{id}/{userID}', 'AppliedProgramController@uploadFile');
    Route::delete('/deleteProgramFile/{id}', 'AppliedProgramController@deleteProgramFile');
    Route::get('/viewProgramFile/{id}', 'AppliedProgramController@viewProgramFile');
    Route::get('/showProgramFile/{id}', 'AppliedProgramController@showProgramFile');

    Route::post('/uploadPPP/{id}', 'AppliedProgramController@uploadPPP');
    Route::post('/uploadCompliance/{id}', 'AppliedProgramController@uploadCompliance');
    Route::post('/uploadNarrative/{id}', 'AppliedProgramController@uploadNarrative');
    Route::get('/viewPPP/{id}', 'AppliedProgramController@viewPPP');
    Route::get('/viewCompliance/{id}', 'AppliedProgramController@viewCompliance');
    Route::get('/viewNarrative/{id}', 'AppliedProgramController@viewNarrative');
    Route::delete('/deletePPP/{id}', 'AppliedProgramController@deletePPP');
    Route::delete('/deleteCompliance/{id}', 'AppliedProgramController@deleteCompliance');
    Route::delete('/deleteNarrative/{id}', 'AppliedProgramController@deleteNarrative');

    Route::post('/attachSupportDocument/{id}/{docID}', 'MSIAttachmentController@attachSupportDocument');
    Route::delete('/removeSupportDocument/{id}', 'MSIAttachmentController@removeSupportDocument');
    Route::get('/viewSupportDocument/{id}', 'MSIAttachmentController@viewSupportDocument');
    Route::get('/showDocument', 'MSIAttachmentController@showDocument');

    Route::get('/showStatementDocument/{id}/{transactionID}', 'MSIController@showStatementDocument');

    Route::post('/uploadDummyDocument', 'MSITransactionController@uploadDummyDocument');
    Route::get('/showDummyDocument', 'MSITransactionController@showDummyDocument');

    Route::post('/assignTask/{id}/{app_prog_id}', 'AssignTaskController@assignTask');
    Route::post('/assignAccreditor/{id}', 'AssignTaskController@assignAccreditor');
    Route::delete('/deleteAssignedUser/{userID}/{transactionID}', 'AssignTaskController@deleteAssignedUser');

    Route::post('/assignHeadTask/{id}', 'AssignTaskController@assignHeadTask');
    Route::delete('/deleteAssignedHeadUser/{userID}/{transactionID}', 'AssignTaskController@deleteAssignedHeadUser');

    Route::put('/setScore/{id}/{assignedUserId}', 'MSIEvaluationController@setScore');
    Route::get('/showBestPractice/{id}/{assignedUserId}', 'MSIEvaluationController@showBestPractice');
    Route::put('/editBestPractice/{id}', 'MSIEvaluationController@editBestPractice');
    Route::delete('/deleteBestPractice/{id}', 'MSIEvaluationController@deleteBestPractice');

    Route::post('/saveRecommendation/{id}', 'MSIEvaluationController@saveRecommendation');
    Route::put('/editRecommendation/{id}', 'MSIEvaluationController@editRecommendation');
    Route::get('/showRecommendation/{id}', 'MSIEvaluationController@showRecommendation');
    Route::get('/showAllRecommendation/{id}', 'MSIEvaluationController@showAllRecommendation');
    Route::delete('/deleteRecommendation/{id}', 'MSIEvaluationController@deleteRecommendation');
});

