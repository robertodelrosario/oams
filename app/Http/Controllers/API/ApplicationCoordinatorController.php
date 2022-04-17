<?php

namespace App\Http\Controllers\API;

use App\AccreditorRequest;
use App\Application;
use App\ApplicationCoordinator;
use App\ApplicationProgram;
use App\AreaInstrument;
use App\AreaMandatory;
use App\AreaMean;
use App\AssignedUser;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentScore;
use App\ParameterMean;
use App\ParameterProgram;
use App\Program;
use App\ProgramReportTemplate;
use App\ProgramStatement;
use App\ReportTemplate;
use App\SUC;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ApplicationCoordinatorController extends Controller
{
    public function approveRequest($id){
        $req = ApplicationCoordinator::where('id', $id)->first();
        $req->status = "accepted";
        $success = $req->save();
        if($success) return response()->json(['status' => true, 'message' => 'Successfully accepted request.']);
        else return response()->json(['status' => false, 'message' => 'unsuccessfully accepted request.']);
    }

    public function rejectRequest($id){
        $req = ApplicationCoordinator::where('id', $id)->first();
        $req->status = "rejected";
        $success = $req->save();
        if($success) return response()->json(['status' => true, 'message' => 'Successfully rejected request.']);
        else return response()->json(['status' => false, 'message' => 'unsuccessfully rejected request.']);
    }

    public function showMyRequest(){
        $collection = new Collection();
        $myPendingRequests = ApplicationCoordinator::where([
            ['user_id', auth()->user()->id],['status', 'pending']
        ])->get();
        $myRejectedRequests = ApplicationCoordinator::where([
            ['user_id', auth()->user()->id],['status', 'rejected']
        ])->get();
        foreach ($myPendingRequests as $myPendingRequest){
            $application = Application::where('id', $myPendingRequest->application_id)->first();
            $suc = SUC::where('id', $application->suc_id)->first();
            $collection->push([
                'id' =>  $myPendingRequest->id,
                'application_id' => $myPendingRequest->application_id,
                'title' => $application->title,
                'institution_name' => $suc->institution_name,
                'status' => $myPendingRequest->status,
                'date_requested' => $myPendingRequest->created_at,
                'date_updated' => $myPendingRequest->updated_at
            ]);
        }
        foreach ($myRejectedRequests as $myRejectedRequest){
            $application = Application::where('id', $myRejectedRequest->application_id)->first();
            $suc = SUC::where('id', $application->suc_id)->first();
            $collection->push([
                'id' =>  $myRejectedRequest->id,
                'application_id' => $myRejectedRequest->application_id,
                'title' => $application->title,
                'institution_name' => $suc->institution_name,
                'status' => $myRejectedRequest->status,
                'date_requested' => $myRejectedRequest->created_at,
                'date_updated' => $myRejectedRequest->updated_at
            ]);
        }
        return response()->json($collection);
    }

    public function showMyAccreditationApplication(){
        $collection = new Collection();
        $myAcceptedRequests = ApplicationCoordinator::where([
            ['user_id', auth()->user()->id],['status', 'accepted']
        ])->get();
        foreach ($myAcceptedRequests as $myAcceptedRequest){
            $application = Application::where('id', $myAcceptedRequest->application_id)->first();
            $suc = SUC::where('id', $application->suc_id)->first();
            $collection->push([
                'id' =>  $myAcceptedRequest->id,
                'application_id' => $myAcceptedRequest->application_id,
                'title' => $application->title,
                'institution_name' => $suc->institution_name,
                'status' => $myAcceptedRequest->status,
                'date_requested' => $myAcceptedRequest->created_at,
                'date_updated' => $myAcceptedRequest->updated_at
            ]);
        }
        return response()->json($collection);
    }

    public function showInstrument($id){
        $instrumentPrograms = new Collection();
        $applied_program_id = ApplicationProgram::where('id', $id)->first();

        $instruments =  InstrumentProgram::where('program_id', $applied_program_id->program_id)->get();
        $program = Program::where('id', $applied_program_id->program_id)->first();
        $users = new Collection();
        foreach($instruments as $instrument){
            $area = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
            $area_type = AreaMandatory::where([
                ['area_instrument_id',$area->id], ['program_status',$program->type]
            ])->first();
            if(is_null($area_type)) $type = null;
            elseif($area_type->type == 'Mandatory') $type = 'Mandatory';
            elseif($area_type->type == 'Optional') $type = 'Optional';
            $instrumentPrograms->push([
                'id' => $instrument->id,
                'program_id' => $instrument->program_id,
                'area_instrument_id' => $instrument->area_instrument_id,
                'created_at' => $instrument->created_at,
                'updated_at' => $instrument->updated_at,
                'intended_program_id' => $area->intended_program_id,
                'area_number' => $area->area_number,
                'area_name' => $area->area_name,
                'version' => $area->version,
                'type' => $type
            ]);
            $assigned_users = AssignedUser::where([
                ['transaction_id', $instrument->id], ['app_program_id', $id], ['role', 'like', '%external accreditor%']
            ])->get();
            foreach ($assigned_users as $assigned_user){
                $user = User::where('id', $assigned_user->user_id)->first();
                $users->push([
                    'id' => $assigned_user->id,
                    'instrument_id' => $instrument->id,
                    'user_id' => $assigned_user->user_id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                ]);
            }
        }
        return response()->json(['instruments' => $instrumentPrograms, 'users' => $users]);
    }

    public function showAccreditorRequested($id){
        $accreditors = new Collection();
        $requested_accreditors = AccreditorRequest::where([
            ['application_program_id', $id], ['status', 'accepted']
            ])->get();
        foreach ($requested_accreditors as $requested_accreditor){
            $user = User::where('id', $requested_accreditor->accreditor_id)->first();
            $accreditors->push([
                'id' => $requested_accreditor->id,
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $requested_accreditor->role
            ]);
        }
        return response()->json(['users' => $accreditors]);
    }

    public function showAllAccreditorRequested($id){
        $accreditors = new Collection();
        $requested_accreditors = AccreditorRequest::where('application_program_id', $id)->get();
        foreach ($requested_accreditors as $requested_accreditor){
            $user = User::where('id', $requested_accreditor->accreditor_id)->first();
            $accreditors->push([
                'id' => $requested_accreditor->id,
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->first_name,
                'role' => $requested_accreditor->role
            ]);
        }
        return response()->json(['users' => $accreditors]);
    }
    public function reassignTask($id, $application_program_id, $instrument_id){
        $requested_accreditor = AccreditorRequest::where('id', $id)->first();
        $check = AssignedUser::where([
            ['app_program_id', $application_program_id], ['user_id', $requested_accreditor->accreditor_id], ['transaction_id', $instrument_id]
        ])->first();
        if(is_null($check)){
            $task = new AssignedUser();
            $task->transaction_id = $instrument_id;
            $task->user_id = $requested_accreditor->accreditor_id;
            $task->app_program_id = $application_program_id;
            $task->role = $requested_accreditor->role;
            $success = $task->save();
            if($success){
                $accreditors = AssignedUser::where([
                    ['app_program_id', $application_program_id], ['transaction_id',$instrument_id]
                ])->get();
                $no_area_mean = true;
                foreach ($accreditors as $accreditor){
                    $check_area_mean = AreaMean::where([
                        ['instrument_program_id', $instrument_id], ['assigned_user_id', $accreditor->id]
                    ])->first();
                    if(!(is_null($check_area_mean))){
                        $no_area_mean = false;
                    }
                }
                if($no_area_mean) {
                    $area_mean = new AreaMean();
                    $area_mean->instrument_program_id = $instrument_id;
                    $area_mean->assigned_user_id = $task->id;
                    $area_mean->area_mean = 0;
                    $area_mean->save();
                }
                    $parameters = ParameterProgram::where('program_instrument_id',$instrument_id)->get();
                    foreach ($parameters as $parameter){
                        $item = new ParameterMean();
                        $item->program_parameter_id = $parameter->id;
                        $item->assigned_user_id = $task->id;
                        $item->parameter_mean = 0;
                        $item->save();

                        $statements = ProgramStatement::where('program_parameter_id', $parameter->id)->get();
                        foreach ($statements as $statement){
                            $item = new InstrumentScore();
                            $item->item_id = $statement->id;
                            $item->assigned_user_id = $task->id;
                            $item->save();
                        }
                    }
            }
            else return response()->json(['status' => false, 'message' => 'Error in making task.']);
            return response()->json(['status' => true, 'message' => 'Successfully assigned to this area.']);
        }
        else return response()->json(['status' => false, 'message' => 'Already assigned to this area.']);
    }
}
