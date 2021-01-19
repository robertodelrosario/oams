<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AreaInstrument;
use App\AssignedUser;
use App\AssignedUserHead;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentScore;
use App\ParameterMean;
use App\ParameterProgram;
use App\ProgramStatement;
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
        return response()->json(['status' => true, 'message' => 'Successfully added task!', 'users' => $head]);
    }

    public function assignTask(request $request, $id, $app_prog_id){  // TRANSACTION AREA INSTRUMENT ID
        $assignUser = new AssignedUser();
        $assignUser->transaction_id = $id;
        $assignUser->user_id = $request->user_id;
        $assignUser->app_program_id = $app_prog_id;
        $assignUser->role = $request->role;
        $assignUser->save();
        if ($request->role == 'internal accreditor' || $request->role == 'external accreditor'){
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
        return response()->json(['status' => true, 'message' => 'Successfully added task!', 'users' => $assignUser]);
    }

    public function assignAccreditor(request $request, $id){
        $application_program = ApplicationProgram::where('id', $id)->first();
        $count = count($request->tasks);
        for($x=0; $x<$count; $x++){
            if($request->tasks[$x]['role_type'] == 0)
            {
                $area = DB::table('instruments_programs')
                    ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                    ->where([
                        ['instruments_programs.program_id', $application_program->program_id], ['area_instruments.area_number', 7]
                    ])
                    ->select('instruments_programs.*')
                    ->first();
                $assignUser = new AssignedUser();
                $assignUser->transaction_id = $area->id;
                $assignUser->user_id = $request->tasks[$x]['user_id'];
                $assignUser->app_program_id = $id;
                if ($x == 0) $assignUser->role = 'external accreditor - leader';
                else $assignUser->role = 'external accreditor';
                $assignUser->save();

                $statements = ProgramStatement::where('program_instrument_id', $area->id)->get();
                foreach ($statements as $statement){
                    $item = new InstrumentScore();
                    $item->item_id = $statement->id;
                    $item->assigned_user_id = $request->tasks[$x]['user_id'];
                    $item->save();
                }
            }
            else{
                $areas = DB::table('instruments_programs')
                    ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                    ->where([
                        ['instruments_programs.program_id', $application_program->program_id], ['area_instruments.area_number','!=', 7]
                    ])
                    ->select('instruments_programs.*')
                    ->get();
                foreach ($areas as $area){
                    $assignUser = new AssignedUser();
                    $assignUser->transaction_id = $area->id;
                    $assignUser->user_id = $request->tasks[$x]['user_id'];
                    $assignUser->app_program_id = $id;
                    if ($x == 0) $assignUser->role = 'external accreditor - leader';
                    else $assignUser->role = 'external accreditor';
                    $assignUser->save();

                    $statements = ProgramStatement::where('program_instrument_id', $area->id)->get();
                    foreach ($statements as $statement){
                        $item = new InstrumentScore();
                        $item->item_id = $statement->id;
                        $item->assigned_user_id = $request->tasks[$x]['user_id'];
                        $item->save();
                    }
                }
            }
        }

    }

    public function deleteAssignedUser($userID, $transactionID){
        $user = AssignedUser::where([
            ['transaction_id', $transactionID], ['user_id', $userID]
        ]);
        $user->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted']);
    }
    public function deleteAssignedHeadUser($userID, $transactionID){
        $user = AssignedUserHead::where([
            ['application_program_id', $transactionID], ['user_id', $userID]
        ]);
        $user->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted']);
    }
}
