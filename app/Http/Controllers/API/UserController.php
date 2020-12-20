<?php

namespace App\Http\Controllers\API;

use App\AssignedUser;
use App\AssignedUserHead;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function showTask($id){
        $tasks = DB::table('assigned_users')
            ->join('instruments_programs', 'instruments_programs.id', '=', 'assigned_users.transaction_id')
            ->join('programs', 'programs.id', '=', 'instruments_programs.program_id')
            ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
            ->join('sucs', 'sucs.id', '=', 'programs.suc_id')
            ->select('instruments_programs.*','sucs.*', 'programs.program_name', 'area_instruments.area_number', 'area_instruments.area_name', 'assigned_users.*')
            ->where('assigned_users.user_id', $id)
            ->get();
        return response()->json(['tasks' => $tasks]);
    }

    public function showHeadTask($id){
        $tasks = DB::table('assigned_user_heads')
            ->join('applications_programs', 'applications_programs.id', '=', 'assigned_user_heads.application_program_id')
            ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
            ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
            ->where('assigned_user_heads.user_id', $id)
            ->get();
        return response()->json(['tasks'=> $tasks]);
    }


}
