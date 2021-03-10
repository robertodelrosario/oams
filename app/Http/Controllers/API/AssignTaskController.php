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

        if(!(is_null($check)) && $check->role != $request->role) return response()->json(['status' => false, 'message' => 'You are already assigned as '.$check->role]);

        $check_1 = AssignedUser::where([
            ['transaction_id', $id], ['user_id', $request->user_id]
        ])->first();
        if(!(is_null($check_1))) return response()->json(['status' => false, 'message' => 'You are already assigned as to this area.']);

        $assignUser = new AssignedUser();
        $assignUser->transaction_id = $id;
        $assignUser->user_id = $request->user_id;
        $assignUser->app_program_id = $app_prog_id;
        $assignUser->role = $request->role;
        $assignUser->save();

        $check = AssignedUser::where([
            ['app_program_id', $app_prog_id], ['transaction_id', $id], ['role', 'internal accreditor']
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

        if ($request->role == 'internal accreditor'){
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

    public function deleteAssignedUser($userID, $transactionID){
        $user = AssignedUser::where([
            ['transaction_id', $transactionID], ['user_id', $userID]
        ])->first();
        $user->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted']);
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
                ['app_program_id', $id], ['user_id', $user->user_id]
            ])->get();
            foreach ($areas as $area){
                $area->role = $user->role; //'internal accreditor - lead'
                $area->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully updated role.']);
    }
}
