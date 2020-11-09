<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth');
    }*/

    public function addProgram(request $request)
    {
        $validator = Validator::make($request->all(), [
            'program_name' => 'required',
            'accreditation_status' => 'required',
            'duration_of_validity' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $check = Program::where([
            ['suc_id', $request->suc_id], [strtolower('program_name'), strtolower($request->program_name)]
        ])->first();

        if(is_null($check)){
            $program = new Program();
            $program->program_name = $request->program_name;
            $program->accreditation_status = $request->accreditation_status;
            $program->duration_of_validity = \Carbon\Carbon::parse($request->duration_of_validity)->format('Y-m-d');
            $program->suc_id = $request->suc_id;
            $program->save();
            return response()->json(['status' => true, 'message' => 'Successfully added program!']);
        }
        return response()->json(['status' => false, 'message' => 'Program already exist!']);
    }

    public function showProgram($id){
        $program = Program::where('suc_id', $id)->get();
        return response()->json($program);
    }

    public function removeProgram($sucID, $programID){
        $program = Program::where([
            ['id', $programID], ['suc_id', $sucID]
        ]);
        $program->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted program!']);
    }

    public function selectInstrument($programID, $instrumentID){
        $instrumentProgram = InstrumentProgram::where([
            ['program_id', $programID], ['area_instrument_id', $instrumentID]
        ])->first();
        if(is_null($instrumentProgram))
        {
            $instrumentProgram = new InstrumentProgram();
            $instrumentProgram->program_id = $programID;
            $instrumentProgram->area_instrument_id = $instrumentID;
            $instrumentProgram->save();
            return response()->json(['status' => true, 'message' => 'Successfully added instrument!']);
        }
        return response()->json(['status' => false, 'message' => 'Already added']);
    }


    public function removeInstrument($programID, $instrumentID){
        $instrumentProgram = InstrumentProgram::where([
            ['program_id', $programID], ['area_instrument_id', $instrumentID]
        ]);
        $instrumentProgram->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted instrument!']);
    }
}
