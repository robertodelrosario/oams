<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AaccupController extends Controller
{
    public function showApplication(){
        $applications = DB::table('applications')
            ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
            ->get();
        return response()->json(['applications' => $applications]);
    }

    public function requestAccreditor(){
        
    }
    public function approve(request $request, $id){
        $program = ApplicationProgram::where('id', $id)->first();
        $program->approved_start_date = $request->approved_start_date;
        $program->approved_end_date = $request->approved_end_date;
        $program->status = "approved";
        $program->save();
        return response()->json(['status' => true, 'message' => 'Successfully approved program application']);
    }
    public function reject( $id){
        $program = ApplicationProgram::where('id', $id)->first();
        $program->status = "rejected";
        $program->save();
        return response()->json(['status' => true, 'message' => 'Rejected program application']);
    }
}
