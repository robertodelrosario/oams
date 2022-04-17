<?php

namespace App\Http\Controllers\API;

use App\AccreditorDegree;
use App\AccreditorSpecialization;
use App\ApplicationProgram;
use App\AreaInstrument;
use App\AreaMean;
use App\AssignedUser;
use App\AssignedUserHead;
use App\Campus;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentScore;
use App\ParameterMean;
use App\ParameterProgram;
use App\ProgramStatement;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class AssignTaskController extends Controller
{
    public function assignHeadTask(request $request, $id){
        $head = new AssignedUserHead();
        $head->application_program_id = $id;
        $head->user_id = $request->user_id;
        $head->role = $request->role;
        $head->save();

        $user = User::where('id', $head->user_id)->first();
        return response()->json(['status' => true, 'message' => 'Successfully added task!', 'users' => $head, 'details' => $user]);
    }

    public function assignTask(request $request, $id, $app_prog_id){  // TRANSACTION AREA INSTRUMENT ID
        $check = AssignedUser::where([
            ['app_program_id', $app_prog_id], ['user_id', $request->user_id]
        ])->first();

        $initial_role = null;
        if(!(is_null($check)) && $check->role == 'internal accreditor - leader' && $request->role == 'internal accreditor')
            $initial_role = $check->role;
        elseif(!(is_null($check)) && $check->role != $request->role)
            return response()->json(['status' => false, 'message' => 'You are already assigned as '.$check->role]);
        else $initial_role = $request->role;
        $check_1 = AssignedUser::where([
            ['transaction_id', $id], ['user_id', $request->user_id]
        ])->first();
        if(!(is_null($check_1))) return response()->json(['status' => false, 'message' => 'You are already assigned as to this area.']);

        $assignUser = new AssignedUser();
        $assignUser->transaction_id = $id;
        $assignUser->user_id = $request->user_id;
        $assignUser->app_program_id = $app_prog_id;
        $assignUser->role = $initial_role;
        $assignUser->save();

        $check = AssignedUser::where([
            ['app_program_id', $app_prog_id], ['transaction_id', $id], ['role','like' ,'internal accreditor%']
        ])->get();
        if(count($check) <= 1 )
        {
            $area_mean = new AreaMean();
            $area_mean->instrument_program_id = $id;
            $area_mean->assigned_user_id = $assignUser->id;
            $area_mean->area_mean = 0;
            $area_mean->save();
        }

        $user = User::where('id', $assignUser->user_id)->first();

        if (Str::contains($initial_role, 'internal accreditor')){
            $parameters = ParameterProgram::where('program_instrument_id',$id)->get();
            foreach ($parameters as $parameter){
                $item = new ParameterMean();
                $item->program_parameter_id = $parameter->id;
                $item->assigned_user_id = $assignUser->id;
                $item->parameter_mean = 0;
                $item->save();

                $statements = ProgramStatement::where('program_parameter_id', $parameter->id)->get();
                foreach ($statements as $statement){
                    $item = new InstrumentScore();
                    $item->item_id = $statement->id;
                    $item->assigned_user_id = $assignUser->id;
                    $item->save();
                }
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully added task!', 'users' => $assignUser, 'details' => $user]);
    }

    public function deleteAssignedUser($id){
        $user = AssignedUser::where('id', $id)->first();
        $assigned_users = AssignedUser::where([
            ['app_program_id', $user->app_program_id], ['transaction_id', $user->transaction_id]
        ])->get();

        if(count($assigned_users) >= 2){
            $area_mean = AreaMean::where('assigned_user_id', $user->id)->first();
            if(is_null($area_mean)){
                $success = $user->delete();
                if($success) return response()->json(['status' => true, 'message' => 'Successfully deleted [1]']);
            }
            else{
                foreach ($assigned_users as $assigned_user){
                    if($assigned_user->id != $user->id){
                        $area_mean = new AreaMean();
                        $area_mean->instrument_program_id = $user->transaction_id;
                        $area_mean->assigned_user_id = $assigned_user->id;
                        $area_mean->area_mean = 0;
                        if($area_mean->save()) {
                            $success = $user->delete();
                            if($success) return response()->json(['status' => true, 'message' => 'Successfully deleted [2]']);
                        }
                    }
                }
            }
        }
        else return response()->json(['status' => false, 'message' => 'Unsuccessfully deleted']);
    }

    public function deleteAssignedHeadUser($userID, $transactionID){
        $user = AssignedUserHead::where([
            ['application_program_id', $transactionID], ['user_id', $userID]
        ])->first();
        $user->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted']);
    }

    public function updateInternalRole(request $request,$id){
        foreach($request->users as $user){
            $areas = AssignedUser::where([
                ['app_program_id', $id], ['user_id', $user['user_id']]
            ])->get();
            foreach ($areas as $area){
                $area->role = $user['role']; //'internal accreditor - lead'
                $area->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully updated role.']);
    }

//    public function assignHeadTaskCoordinator(request $request, $id){
//        $head = new AssignedUserHead();
//        $head->application_program_id = $id;
//        $head->user_id = $request->user_id;
//        $head->role = $request->role;
//        $head->save();
//
//        $user = User::where('id', $head->user_id)->first();
//        return response()->json(['status' => true, 'message' => 'Successfully added task!', 'users' => $head, 'details' => $user]);
//    }
//    public function deleteAssignedHeadUserCoordinator($userID, $transactionID){
//        $user = AssignedUserHead::where([
//            ['application_program_id', $transactionID], ['user_id', $userID]
//        ])->first();
//        $user->delete();
//        return response()->json(['status' => true, 'message' => 'Successfully deleted']);
//    }

    public function listAssignedUser($id){
        $users = AssignedUser::where('app_program_id', $id)->get();
        return response()->json($users);
    }

    public function areaMean($id){
        $users = AssignedUser::where('app_program_id', $id)->get();
        $area_mean_external = new Collection();
        $area_mean_internal = new Collection();
        foreach($users as $user){
            if(Str::contains($user->role, 'external accreditor')){
                echo $user;
                $score = AreaMean::where('assigned_user_id', $user->id)->first();
                if(!is_null($score)) $area_mean_external->push($score);
            }
            elseif(Str::contains($user->role, 'internal accreditor')){
                $user;
                $score = AreaMean::where('assigned_user_id', $user->id)->first();
                if(!(is_null($score))) $area_mean_internal->push($score);;
            }
        }
        return response()->json(['external' => $area_mean_external, 'internal' => $area_mean_internal ]);
    }

    public function allAreaMean(){
        return response()->json(AreaMean::all());   
    }
}
