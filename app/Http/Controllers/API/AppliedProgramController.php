<?php

namespace App\Http\Controllers\API;

use App\Application;
use App\ApplicationProgram;
use App\BenchmarkStatement;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AppliedProgramController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login', 'register', 'me']]);
    }*/

    public function program(request $request)
    {
        $check = ApplicationProgram::where([
            ['application_id', $request->application_id], ['program_id', $request->program_id]
        ])->first();
        if(is_null($check)){
            $program = new ApplicationProgram();
            $program->application_id = $request->application_id;
            $program->program_id = $request->program_id;
            $program->level = $request->level;
            $program->preferred_start_date = \Carbon\Carbon::parse($request->preferred_start_date)->format('Y-m-d');
            $program->preferred_end_date = \Carbon\Carbon::parse($request->preferred_end_date)->format('Y-m-d');
            $program->ppp = "NONE";
            $program->compliance_report = "NONE";
            $program->narative_report = "NONE";
            $program->save();
            $check = ApplicationProgram::where([
                ['application_id', $request->application_id], ['program_id', $request->program_id]
            ])->first();
            return response()->json(['status' => true, 'message' => 'Successfully added program!', 'applied_program'=> $check]);
        }
        return response()->json(['status' => false, 'message' => 'program already applied!']);

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
        $program = DB::table('applications_programs')
            ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
            ->where('applications_programs.application_id', $id)
            ->get();
        return response()->json([$program,$program->id]);
    }

    public function showInstrumentProgram($id){
        //$instrumentPrograms = InstrumentProgram::where('program_id', $id)->get();
        $instrumentPrograms = DB::table('instruments_programs')
            ->join('area_instruments', 'instruments_programs.area_instrument_id', '=', 'area_instruments.id')
            ->where('instruments_programs.program_id', $id)
            ->get();
        if(is_null($instrumentPrograms)) return response()->json(['status' => false, 'message' => 'Do not have instruments']);
         return response()->json($instrumentPrograms);
    }
}
