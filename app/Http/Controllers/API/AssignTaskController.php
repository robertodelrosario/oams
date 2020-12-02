<?php

namespace App\Http\Controllers\API;

use App\AssignedUser;
use App\AssignedUserHead;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentScore;
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

    public function assignTask(request $request, $id){  // TRANSACTION AREA INSTRUMENT ID
        $assignUser = new AssignedUser();
        $assignUser->transaction_id = $id;
        $assignUser->user_id = $request->user_id;
        $assignUser->role = $request->role;
        $assignUser->save();
        if ($request->role == 'internal accreditor' || $request->role == 'external accreditor'){
            $statements = ProgramStatement::where('program_instrument_id', $id)->get();
            foreach ($statements as $statement){
                $item = new InstrumentScore();
                $item->item_id = $statement->id;
                $item->assigned_user_id = $assignUser->user_id;
                $item->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully added task!', 'users' => $assignUser]);
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

//    public function showTask($id){
//        $list = array();
//        $tasks = AssignedUser::where('user_id', $id)->get();
//        foreach ($tasks as $task){
//            $assignedTask = InstrumentProgram::where('id', $task->transaction_id)->first();
//            $instrument = DB::table('instruments_programs')
//                ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
//                ->join('programs', 'programs.id', '=', 'instruments_programs.program_id')
//                ->join('applications_programs', 'applications_programs.program_id', '=', 'instruments_programs.program_id')
//                ->where('instruments_programs.id', $assignedTask->id)
//                ->select('instruments_programs.*', 'area_instruments.area_number','area_instruments.area_name', 'programs.program_name', 'applications_programs.*' )
//                ->get();
//            $list = Arr::prepend($list, $instrument);
//        }
//        return response()->json(['tasks' => $list, 'user' => $tasks]);
//    }

//    public function showTaskUser($id){
//        $user = DB::table('assigned_users')
//            ->join('users', 'users.id', '=', 'assigned_users.user_id')
//            ->where('assigned_users.transaction_id', $id)
//            ->get();
//        return response()->json(['user' => $user]);
//    }
}
