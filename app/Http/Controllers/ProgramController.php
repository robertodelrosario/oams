<?php

namespace App\Http\Controllers;

use App\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    public function addProgram(request $request)
    {
        $validator = Validator::make($request->all(), [
            'program_name' => 'required',
            'accreditation_status' => 'required',
            'duration_of_validity' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $program = new Program();
        $program->program_name = $request->program_name;
        $program->accreditation_status = $request->accreditation_status;
        $program->duration_of_validity = $request->duration_of_validity;
        $program->campus_id = $request->campus_id;
        $program->save();

        return response()->json(['status' => true, 'message' => 'Successfully added program!']);
    }
}
