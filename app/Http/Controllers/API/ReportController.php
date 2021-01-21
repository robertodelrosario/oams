<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AssignedUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function uploadAreaReport(request $request, $id, $userID){
        $area = AssignedUser::where([
            ['user_id', $userID], ['app_program_id', $id]
        ])->first();

        if(!(is_null($request->sfr))){
            $fileName = time() . '_' . $request->sfr->getClientOriginalName();
            $filePath = $request->file('sfr')->storeAs('reports', $fileName);
            $area->sfr_report = $filePath;
        }
        if(!(is_null($request->sar))){
            $fileName = time() . '_' . $request->sar->getClientOriginalName();
            $filePath = $request->file('sar')->storeAs('reports', $fileName);
            $area->sar_report = $filePath;
        }
        $area->save();
        return response()->json(['status' => true, 'message' => 'Successfully added report documents!']);
    }

    public function uploadProgramReport(request $request, $id){
        $program = ApplicationProgram::where('id', $id)->first();

        if(!(is_null($request->sfr))){
            $fileName = time() . '_' . $request->sfr->getClientOriginalName();
            $filePath = $request->file('sfr')->storeAs('reports', $fileName);
            $program->sfr_report = $filePath;
        }
        if(!(is_null($request->sar))){
            $fileName = time() . '_' . $request->sar->getClientOriginalName();
            $filePath = $request->file('sar')->storeAs('reports', $fileName);
            $program->resule = $filePath;
        }
        $program->save();
        return response()->json(['status' => true, 'message' => 'Successfully added report documents!']);
    }


}
