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
    Route::post('changePassword/{id}', 'AuthController@changePassword');
    Route::put('resetPassword/{id}', 'AuthController@resetPassword');
    Route::post('registerSucUser/{id}', 'AuthController@registerSucUser');
    Route::post('registerLocalAccreditor/{id}', 'AuthController@registerLocalAccreditor');
    Route::post('registerAaccupAccreditor', 'AuthController@registerAaccupAccreditor');
    Route::put('editUser/{id}', 'AuthController@editUser');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::get('/showCampusUser/{id}', 'AuthController@showCampusUser');
    Route::get('/showAaccup', 'AuthController@showAaccup');
    Route::get('/showAccreditor', 'AuthController@showAccreditor');
    Route::get('/showLocalAccreditor/{id}', 'AuthController@showLocalAccreditor');
    Route::get('/showAllUser', 'AuthController@showAllUser');
    Route::put('/deleteUser/{id}', 'AuthController@deleteUser');
    Route::put('/activateUser/{id}', 'AuthController@activateUser');
    Route::delete('/removeUser/{id}', 'AuthController@removeUser');
    Route::delete('/removeToCampus/{campusID}/{userID}', 'AuthController@removeToCampus');
    Route::put('/changeAccreditorStatus/{id}', 'AuthController@changeAccreditorStatus');

    Route::post('setRole/{userID}', 'UserRoleController@setRole');
    Route::delete('/deleteSetRole/{userID}/{roleID}/{officeID}', 'UserRoleController@deleteSetRole');
    Route::post('addRole', 'UserRoleController@addRole');
    Route::get('showRole', 'UserRoleController@showRole');
    Route::delete('deleteRole/{id}', 'UserRoleController@deleteRole');
    Route::delete('deleteOtherRole/{userID}/{roleID}', 'UserRoleController@deleteOtherRole');
    Route::put('editRole/{id}', 'UserRoleController@editRole');
    Route::delete('deleteUserRole/{id}', 'UserRoleController@deleteUserRole');

    Route::post('/addToOffice/{id}/{role_id}/{office_id}', 'OfficeUserController@addToOffice');
    Route::delete('/removeFromOffice/{id}', 'OfficeUserController@removeFromOffice');

    Route::put('savePersonalInfo/{id}', 'UserProfileController@savePersonalInfo'); //id is user_id
    Route::put('savePersonalInfo/{id}', 'UserProfileController@savePersonalInfo'); //id is user_id
    Route::get('showPersonalInfo/{id}', 'UserProfileController@showPersonalInfo'); //id is user_id

    Route::post('createEducationInfo/{id}', 'UserProfileController@createEducationInfo'); //id is user_id
    Route::get('showEducationInfo/{id}', 'UserProfileController@showEducationInfo'); //id is user_id
    Route::put('editEducationInfo/{id}', 'UserProfileController@editEducationInfo'); //id is user_education_id
    Route::delete('deleteEducationInfo/{id}', 'UserProfileController@deleteEducationInfo'); //id is user_education_id

    Route::post('createWorkExperience/{id}', 'UserProfileController@createWorkExperience');//id is user_id
    Route::get('showWorkExperience/{id}', 'UserProfileController@showWorkExperience');//id is user_id
    Route::put('editWorkExperience/{id}', 'UserProfileController@editWorkExperience');//id is user_work_experience_id
    Route::delete('deleteWorkExperience/{id}', 'UserProfileController@deleteWorkExperience');//id is user_work_experience_id

    Route::get('showOtherInformation/{id}', 'UserProfileController@showOtherInformation');//id is user_id
    Route::put('editOtherInformation/{id}', 'UserProfileController@editOtherInformation');//id is user_id

    Route::get('showAccreditorProfile/{id}', 'UserProfileController@showAccreditorProfile');//id is user_id
    Route::put('editAccreditorProfile/{id}', 'UserProfileController@editAccreditorProfile');//id is user_id

    Route::post('addAccreditorSpecialization/{id}', 'UserProfileController@addAccreditorSpecialization');//id is user_id
    Route::get('showAccreditorSpecialization/{id}', 'UserProfileController@showAccreditorSpecialization');//id is user_id
    Route::delete('deleteAccreditorSpecialization/{id}', 'UserProfileController@deleteAccreditorSpecialization');//id is user_specialization_id

    Route::post('addToCampus/{campusID}/{userID}', 'AuthController@addToCampus');//id is user_id
    Route::get('showAllCampusUser', 'AuthController@showAllCampusUser');//id is user_id

    Route::get('showTF/{id}', 'AuthController@showTF');//id is user_id
});

Route::group(['prefix' => '/suc'], function() {
    Route::post('/addCampus/{id}', 'CampusController@addCampus');
    Route::get('/showCampus/{id}', 'CampusController@showCampus');
    Route::get('/showAllCampus', 'CampusController@showAllCampus');
    Route::delete('/deleteCampus/{id}', 'CampusController@deleteCampus');
    Route::put('/editCampus/{id}', 'CampusController@editCampus');
    Route::get('/showCampusUser/{id}', 'CampusController@showCampusUser');

    Route::post('/createOffice/{id}', 'OfficeController@createOffice');
    Route::get('/showOffice/{id}', 'OfficeController@showOffice');
    Route::get('/showAllOffice', 'OfficeController@showAllOffice');
    Route::get('/showDepartment/{id}', 'OfficeController@showDepartment');
    Route::delete('/deleteOffice/{id}', 'OfficeController@deleteOffice');
    Route::put('/editOffice/{id}', 'OfficeController@editOffice');
    Route::post('/transferOffice/{id}', 'OfficeController@transferOffice');
    Route::delete('/removeOfficeFromCampus/{officeID}/{campusID}', 'OfficeController@removeOfficeFromCampus');

    Route::post('/addBestPractice/{id}', 'OfficeController@addBestPractice');
    Route::put('/editBestPractice/{id}', 'OfficeController@editBestPractice');
    Route::delete('/deleteBestPractice/{id}', 'OfficeController@deleteBestPractice');
    Route::get('/showBestPractice/{id}', 'OfficeController@showBestPractice');
    Route::post('/attachDocument/{practice_office_id}/{document_id}', 'OfficeController@attachDocument');
    Route::delete('/removeAttachDocument/{id}', 'OfficeController@removeAttachDocument');
    Route::post('/addTag/{id}', 'OfficeController@addTag');
    Route::delete('/removeTag/{id}', 'OfficeController@removeTag');

    Route::post('/addProgram/{id}', 'ProgramController@addProgram');
    Route::get('/showProgram/{id}', 'ProgramController@showProgram');
    Route::delete('/deleteProgram/{id}', 'ProgramController@deleteProgram');
    Route::put('/editProgram/{id}', 'ProgramController@editProgram');
    Route::put('/addOffice/{id}/{office_id}', 'ProgramController@addOffice');
    Route::put('/deleteProgramOffice/{id}', 'ProgramController@deleteProgramOffice');

    Route::get('/showAllProgram', 'ProgramController@showAllProgram');
    Route::put('/editProgramType/{id}/{type}', 'ProgramController@editProgramType');

    Route::post('/selectInstrument/{programID}/{intendedProgramID}', 'ProgramController@selectInstrument');
    Route::get('/showInstrumentProgram/{id}', 'ProgramController@showInstrumentProgram');
    Route::get('/showStatement/{id}', 'ProgramController@showStatement');
    Route::delete('/removeInstrument/{programID}/{instrumentID}', 'ProgramController@removeInstrument');

    Route::post('/acceptDeclineReschedule/{id}/{userID}', 'QuasController@acceptDeclineReschedule');

    Route::post('addCampusUser', 'CampusController@addCampusUser');
});

Route::group(['prefix' => '/task'], function() {
    Route::get('/showProgram/{id}', 'UserController@showProgram');
    Route::get('/showInstrument/{id}/{app_prog}', 'UserController@showInstrument');
    Route::get('/showParameter/{id}/{app_prog}', 'UserController@showParameter');
    Route::get('/showParameterInternal/{id}/', 'UserController@showParameterInternal');
    Route::get('/showProgramHead/{id}', 'UserController@showProgramHead');
    Route::get('/showInstrumentHead/{app_prog}', 'UserController@showInstrumentHead');
    Route::get('/showCollegeCoordinator/{id}', 'TaskForceController@showCollegeCoordinator');
});

Route::group(['prefix' => '/role'], function() {
    Route::post('/createRole', 'RoleController@createRole');
    Route::put('/editRole/{id}', 'RoleController@editRole');
    Route::post('/deleteRole/{id}', 'RoleController@deleteRole');
});

Route::group(['prefix' => '/ppp'], function() {
    Route::post('/addPPPStatement/{id}', 'PPPController@addPPPStatement');
    Route::put('/editPPPStatement/{id}', 'PPPController@editPPPStatement');
    Route::delete('/deletePPPStatement/{id}', 'PPPController@deletePPPStatement');
    Route::get('/showPPPStatement/{id}', 'PPPController@showPPPStatement');
    Route::post('/attachFile/{statement_id}/{document_id}', 'PPPController@attachFile');
    Route::delete('/removeFile/{id}', 'PPPController@removeFile');
    Route::get('/showAllBestPractice/{id}', 'PPPController@showAllBestPractice');
    Route::get('/downloadPPP/{id}', 'PPPController@downloadPPP');
    Route::get('/sample', 'PPPController@sample');
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

    Route::put('/saveRequiredRating', 'AaccupController@saveRequiredRating');
    Route::post('/setRequiredRating', 'AaccupController@setRequiredRating');
    Route::get('/showRequiredRating', 'AaccupController@showRequiredRating');
    Route::get('/showRequiredProgramRating/{id}', 'AaccupController@showRequiredProgramRating');

    Route::post('/registerQA/{id}', 'CampusController@registerQA');

});

Route::group(['prefix' => '/document'], function() {
    Route::post('/uploadDocument/{userID}/{id}', 'DocumentController@uploadDocument');
    Route::get('/showContainer/{id}', 'DocumentController@showContainer');
    Route::get('/showAllContainer/{userID}/{id}', 'DocumentController@showAllContainer');
    Route::delete('/deleteContainer/{id}', 'DocumentController@deleteContainer');
    Route::put('/editContainer/{id}', 'DocumentController@editContainer');
    Route::get('/showDocument/{id}', 'DocumentController@showDocument');
    Route::delete('/deleteDocument/{id}', 'DocumentController@deleteDocument');
    Route::delete('/removeDocument/{id}', 'DocumentController@removeDocument');
    Route::get('/viewFile/{id}', 'DocumentController@viewFile');
    Route::get('/viewLang/{id}', 'DocumentController@viewLang');
    Route::get('/view/{id}', 'DocumentController@view');
    Route::post('/makeDocumentList/{id}', 'DocumentController@makeDocumentList');
    Route::put('/editDocumentName/{id}', 'DocumentController@editDocumentName');
    Route::post('/addTag/{id}', 'DocumentController@addTag');
    Route::delete('/deleteTag/{id}', 'DocumentController@deleteTag');
    Route::post('/makeOwnDocumentList/{id}', 'DocumentController@makeOwnDocumentList');
    Route::post('/uploadOwnDocument/{id}/{statementID}', 'DocumentController@uploadOwnDocument');
    Route::get('/showOwnDocument/{id}', 'DocumentController@showOwnDocument');
    Route::get('/showOwnContainer/{id}', 'DocumentController@showOwnContainer');
    Route::delete('/deleteAllDocument', 'DocumentController@deleteAllDocument');
    Route::get('/showAllDocument', 'DocumentController@showAllDocument');
    Route::get('/showAllDocumentPerCampus/{id}', 'DocumentController@showAllDocumentPerCampus');

    Route::get('/deleteDoc/{id}', 'DocumentController@deleteDoc');
    Route::get('/deleteCon/{id}', 'DocumentController@deleteCon');
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
    Route::post('/createCriteriaInstrument', 'InstrumentController@createCriteriaInstrument');
    Route::post('/createCriteriaInstrumentLevelIV', 'InstrumentController@createCriteriaInstrumentLevelIV');
    Route::post('/cloneInstrument/{id}', 'InstrumentController@cloneInstrument');
//    Route::post('/createStatement', 'StatementController@createStatement');
    Route::post('/createStatement/{id}', 'StatementController@createStatement');
    Route::post('/createCriteriaStatement/{id}', 'StatementController@createCriteriaStatement');
    Route::post('/createParameter', 'ParameterController@createParameter');
    Route::get('/showProgram', 'InstrumentController@showProgram');
    Route::get('/showParameter/{id}', 'ParameterController@showParameter');
    Route::get('/showInstrument/{id}', 'InstrumentController@showInstrument');
    Route::get('/showAllStatement', 'StatementController@showAllStatement');
    Route::get('/showStatement/{id}', 'StatementController@showStatement');
    Route::delete('/deleteParameter/{id}', 'ParameterController@deleteParameter');
    Route::delete('/deleteProgram/{id}', 'InstrumentController@deleteProgram');
    Route::delete('/deleteStatement/{instrumentID}/{statementID}', 'StatementController@deleteStatement');
    Route::put('/editStatement', 'StatementController@editStatement');
    Route::put('/editProgram/{id}', 'InstrumentController@editProgram');
    Route::put('/editInstrument/{id}', 'InstrumentController@editInstrument');
    Route::put('/editParameter/{id}', 'ParameterController@editParameter');
    Route::post('/setAreaMandatory/{id}', 'InstrumentController@setAreaMandatory');
    Route::post('/setInitialAreaMandatory', 'InstrumentController@setInitialAreaMandatory');
    Route::delete('/removeAreaMandatory/{id}', 'InstrumentController@removeAreaMandatory');

    Route::get('/showCriteriaInstrument/{id}', 'CriteriaForm@showCriteriaInstrument');
    Route::post('/addInstrument/{id}/{program_id}', 'CriteriaForm@addInstrument');
    Route::delete('/removeInstrument/{id}', 'CriteriaForm@removeInstrument');

    Route::post('/addInstrumentTag/{id}', 'InstrumentController@addInstrumentTag');
    Route::delete('/removeInstrumentTag/{id}', 'InstrumentController@removeInstrumentTag');
    Route::get('/showInstrumentTag/{id}', 'InstrumentController@showInstrumentTag');

    Route::put('/changeParent/{parameterID}/{statementID}/{parentID}', 'StatementController@changeParent');
    Route::put('/updateSelectedInstrument/{programID}', 'ProgramController@updateSelectedInstrument');
    Route::put('/updateSelectedStatement/{id}/{instrumentParameterID}', 'ProgramController@updateSelectedStatement');
    Route::put('/updateSelectedOBE/{id}', 'ProgramController@updateSelectedOBE');
});

Route::group(['prefix' => '/notification'], function() {
    Route::get('/showAllNotification/{id}', 'NotificationController@showAllNotification');
    Route::get('/viewNotication/{id}', 'NotificationController@viewNotication');
    Route::delete('/deleteNotification/{id}', 'NotificationController@deleteNotification');
});

Route::group(['prefix' => '/report'], function() {
    Route::get('/generateAreaSAR/{id}/{app_prog}', 'ReportController@generateAreaSAR');
    Route::get('/generateAreaSARInternal/{id}/{app_prog}', 'ReportController@generateAreaSARInternal');
    Route::get('/generateProgramSAR/{id}/{app_prog}', 'ReportController@generateProgramSAR');
    Route::get('/generateProgramSFR/{id}/{role}', 'ReportController@generateProgramSFR');
    Route::post('/saveSFR/{programID}/{instrumentID}', 'ReportController@saveSFR');
    Route::get('/showSFR/{id}/{role}', 'ReportController@showSFR');
    Route::get('/viewSFR/{programID}/{instrumentID}/{role}', 'ReportController@viewSFR');
    Route::get('/generateAccreditorReport/{id}', 'ReportController@generateAccreditorReport');
    Route::get('/generateAccreditorAreaReport/{id}/{instrument_id}', 'ReportController@generateAccreditorAreaReport');
    Route::get('/downloadOBE/{id}/{instrument_id}', 'ReportController@downloadOBE');

    Route::get('/showTagOption', 'ReportTemplateController@showTagOption');
    Route::post('/addTemplate/{id}', 'ReportTemplateController@addTemplate');
    Route::get('/showTemplate/{id}', 'ReportTemplateController@showTemplate');
    Route::get('/downloadFile/{id}', 'ReportTemplateController@downloadFile');
    Route::delete('/deleteTemplate/{id}', 'ReportTemplateController@deleteTemplate');
    Route::post('/addTemplateTag/{id}', 'ReportTemplateController@addTemplatetag');
    Route::delete('/removeTemplateTag/{id}', 'ReportTemplateController@removeTemplateTag');
});

Route::group(['prefix' => '/coordinator'], function() {
    Route::post('/requestCoordinator/{application_id}/{user_id}', 'AaccupController@requestCoordinator');
    Route::get('/showCoordinatorRequest/{id}', 'AaccupController@showCoordinatorRequest');
    Route::delete('/removeCoordinatorRequest/{id}', 'AaccupController@removeCoordinatorRequest');
    Route::put('/approveRequest/{id}', 'ApplicationCoordinatorController@approveRequest');
    Route::put('/rejectRequest/{id}', 'ApplicationCoordinatorController@rejectRequest');
    Route::get('/showMyRequest', 'ApplicationCoordinatorController@showMyRequest');
    Route::get('/showMyAccreditationApplication', 'ApplicationCoordinatorController@showMyAccreditationApplication');
    Route::get('/showInstrument/{id}', 'ApplicationCoordinatorController@showInstrument');
    Route::get('/showAccreditorRequested/{id}', 'ApplicationCoordinatorController@showAccreditorRequested');
    Route::get('/showAllAccreditorRequested/{id}', 'ApplicationCoordinatorController@showAllAccreditorRequested');
    Route::post('/reassignTask/{id}/{application_program_id}/{instrument_id}', 'ApplicationCoordinatorController@reassignTask');
});

Route::group(['prefix' => '/message'], function() {
    Route::post('/sendRemark/{applied_program_id}/{statement_id}', 'ScoreRemarkController@sendRemark');
    Route::get('/showRemark/{applied_program_id}/{statement_id}/{type}', 'ScoreRemarkController@showRemark');
    Route::get('/showUnreadMessage/{applied_program_id}', 'ScoreRemarkController@showUnreadMessage');
});

Route::group(['prefix' => '/application'], function() {
    Route::post('/createApplication/{sucID}/{userID}', 'ApplicationController@createApplication');
    Route::put('/editApplication/{id}', 'ApplicationController@editApplication');
    Route::post('/submitApplication/{id}/{sucID}', 'ApplicationController@submitApplication');
    Route::delete('/deleteApplication/{id}', 'ApplicationController@deleteApplication');
    Route::get('/showApplication/{id}', 'ApplicationController@showApplication');
//    Route::get('/showSubmittedApplication/{id}', 'ApplicationController@showSubmittedApplication');

    Route::post('/uploadFile/{id}', 'ApplicationController@uploadFile');
    Route::delete('/deleteFile/{id}', 'ApplicationController@deleteFile');
    Route::get('/viewFile/{id}', 'ApplicationController@viewFile');

    Route::post('/approveApplication/{id}', 'ApplicationController@approveApplication');
    Route::post('/disapproveApplication/{id}', 'ApplicationController@disapproveApplication');

    Route::post('/program', 'AppliedProgramController@program');
    Route::delete('/delete/{id}', 'AppliedProgramController@delete');
    Route::put('/edit/{id}', 'AppliedProgramController@edit');
    Route::put('/changeApplication/{application_id}/{program_id}', 'AppliedProgramController@changeApplication');
    Route::get('/showProgram/{id}', 'AppliedProgramController@showProgram');
    Route::get('/programList/{id}', 'AppliedProgramController@programList');
    Route::get('/showAllAppliedProgram/{id}', 'AppliedProgramController@showAllAppliedProgram');
    Route::get('/showInstrumentProgram/{id}', 'AppliedProgramController@showInstrumentProgram');
    Route::get('/showStatementDocument/{id}', 'AppliedProgramController@showStatementDocument');
    Route::put('/lockSelfSurvey/{id}', 'AppliedProgramController@lockSelfSurvey');
    Route::put('/editLevel/{id}', 'AppliedProgramController@editLevel');

    Route::post('/uploadFile/{id}/{userID}', 'AppliedProgramController@uploadFile');
    Route::post('/updateProgramFile/{id}', 'AppliedProgramController@updateProgramFile');
    Route::delete('/deleteProgramFile/{id}/{user_id}', 'AppliedProgramController@deleteProgramFile');
    Route::get('/viewProgramFile/{id}', 'AppliedProgramController@viewProgramFile');
    Route::get('/showProgramFile/{id}', 'AppliedProgramController@showProgramFile');
    Route::get('/showFile/{id}', 'AppliedProgramController@showFile');
    Route::get('/showFileTFH/{id}', 'AppliedProgramController@showFileTFH');
    Route::get('/showFileQA/{id}', 'AppliedProgramController@showFileQA');
    Route::get('/showFileIA/{id}/{userID}', 'AppliedProgramController@showFileIA');
    Route::get('/showFileEA/{id}/{userID}', 'AppliedProgramController@showFileEA');
    Route::get('/showFileTF/{id}/{userID}', 'AppliedProgramController@showFileTF');
    Route::put('/changeProgramFileStatus', 'AppliedProgramController@changeProgramFileStatus');

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

    Route::post('/attachAreaSupportDocument/{id}/{doc_id}', 'MSIAttachmentController@attachAreaSupportDocument');
    Route::delete('/removeAreaSupportDocument/{id}', 'MSIAttachmentController@removeAreaSupportDocument');
    Route::get('/showAreaSupportDocument/{id}', 'MSIAttachmentController@showAreaSupportDocument');

    Route::get('/showStatementDocument/{id}/{transactionID}', 'MSIController@showStatementDocument');

    Route::post('/uploadDummyDocument', 'MSITransactionController@uploadDummyDocument');
    Route::get('/showDummyDocument', 'MSITransactionController@showDummyDocument');

    Route::post('/assignTask/{id}/{app_prog_id}', 'AssignTaskController@assignTask');
    Route::post('/assignAccreditor/{id}', 'AssignTaskController@assignAccreditor');
    Route::put('/updateInternalRole/{id}', 'AssignTaskController@updateInternalRole');
    Route::delete('/deleteAssignedUser/{id}', 'AssignTaskController@deleteAssignedUser');
    Route::get('/listAssignedUser/{id}', 'AssignTaskController@listAssignedUser');
    Route::get('/areaMean/{id}', 'AssignTaskController@areaMean');
    Route::get('/allAreaMean', 'AssignTaskController@allAreaMean');

    Route::post('/assignHeadTask/{id}', 'AssignTaskController@assignHeadTask');
    Route::delete('/deleteAssignedHeadUser/{userID}/{transactionID}', 'AssignTaskController@deleteAssignedHeadUser');
    Route::post('/assignHeadTaskCoordinator/{id}', 'AssignTaskController@assignHeadTaskCoordinator');
    Route::delete('/deleteAssignedHeadUserCoordinator/{userID}/{transactionID}', 'AssignTaskController@deleteAssignedHeadUserCoordinator');

    Route::put('/setScore/{id}/{assignedUserId}', 'MSIEvaluationController@setScore');
    Route::post('/saveAreaScore/{id}/{assigned_user_id}', 'MSIEvaluationController@saveAreaScore');
    Route::get('/showBestPractice/{id}/{assignedUserId}', 'MSIEvaluationController@showBestPractice');
    Route::put('/editBestPractice/{id}', 'MSIEvaluationController@editBestPractice');
    Route::delete('/deleteBestPractice/{id}', 'MSIEvaluationController@deleteBestPractice');

    Route::delete('/deleteAreaMean/{id}', 'MSIEvaluationController@deleteAreaMean');
    Route::get('/showMeanScore/{id}', 'MSIEvaluationController@showMeanScore');

    Route::post('/saveRecommendation/{id}', 'MSIEvaluationController@saveRecommendation');
    Route::put('/editRecommendation/{id}', 'MSIEvaluationController@editRecommendation');
    Route::get('/showRecommendation/{id}', 'MSIEvaluationController@showRecommendation');
    Route::delete('/deleteRecommendation/{id}', 'MSIEvaluationController@deleteRecommendation');

    Route::get('/showSFRData/{id}/{role}', 'MSIEvaluationController@showSFRData');
    Route::get('/showPersonalSFRData/{id}/{role}', 'MSIEvaluationController@showPersonalSFRData');
    Route::get('/showProgramSAR/{app_prog}/{role}', 'OverviewController@showProgramSAR');

    Route::get('/showOptionArea/{id}', 'AppliedProgramController@showOptionArea');
//    Route::get('/showCriteriaInstrument/{id}', 'CriteriaForm@showCriteriaInstrument');
//    Route::post('/addInstrument/{id}/{program_id}', 'CriteriaForm@addInstrument');
//    Route::delete('/removeInstrument/{id}', 'CriteriaForm@removeInstrument');
});

