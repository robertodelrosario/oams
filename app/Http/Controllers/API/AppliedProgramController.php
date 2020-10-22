<?php

namespace App\Http\Controllers\API;

use App\Application;
use App\ApplicationProgram;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppliedProgramController extends Controller
{
    public function program(request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required',
            'program_id' => 'required',
            'level' => 'required',
            'preferred_date' => 'required'
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Required program!']);

        $program = new ApplicationProgram();
        $program->application_id = $request->application_id;
        $program->program_id = $request->program_id;
        $program->level = $request->level;
        $program->preferred_date = \Carbon\Carbon::parse($request->preferred_date)->format('Y-m-d');
        $program->ppp = "need ppp";
        $program->compliance_report = "need compliance report";
        $program->narative_report = "need narrative report";
        $program->save();
        return response()->json(['status' => true, 'message' => 'Successfully added program!']);
    }

    public function delete($id){
        $program = ApplicationProgram::where('id',$id);
        $program->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted applied program!']);
    }

    public function uploadDocument(request $request){
        $validator = Validator::make($request->all(), [
            'ppp' => 'required|mimes:doc,docx,pdf|max:2048',
            'compliance_report' => 'required|mimes:doc,docx,pdf|max:2048',
            'narative_report' => 'required|mimes:doc,docx,pdf|max:2048'
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Application Letter!']);

        $program = ApplicationProgram::where('id', $request->id)->first();

        $fileName = time().'_'.$request->ppp->getClientOriginalName();
        $filePath = $request->file('ppp')->storeAs('uploads', $fileName, 'public');
        $program->ppp = '/storage/' . $filePath;

        $fileName = time().'_'.$request->compliance_report->getClientOriginalName();
        $filePath = $request->file('compliance_report')->storeAs('uploads', $fileName, 'public');
        $program->compliance_report = '/storage/' . $filePath;

        $fileName = time().'_'.$request->narative_report->getClientOriginalName();
        $filePath = $request->file('narative_report')->storeAs('uploads', $fileName, 'public');
        $program->narative_report ='/storage/' . $filePath;
        $program->save();
        return response()->json(['status' => true, 'message' => 'Successfully added supporting documents!']);
    }

    public function showProgram($id){
        $program = ApplicationProgram::where('application_id', $id)->get();
        return response()->json($program);
    }
}
