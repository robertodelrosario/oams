<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AreaInstrument;
use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\InstrumentProgram;
use App\InstrumentStatement;
use App\ParameterProgram;
use App\Program;
use App\ProgramStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth');
    }*/

    public function addProgram(request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'program_name' => 'required',
            'last_applied_level' => 'required',
            'duration_of_validity' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $check = Program::where([
            ['campus_id', $id], [strtolower('program_name'), strtolower($request->program_name)]
        ])->first();

        if(is_null($check)){
            $program = new Program();
            $program->program_name = $request->program_name;
            $program->last_applied_level = $request->last_applied_level;
            if($request->last_applied_level == 'Candidate') $program->accreditation_status = 'Candidate';
            elseif ($request->last_applied_level == 'Level I') $program->accreditation_status = 'Level I Accredited';
            elseif ($request->last_applied_level == 'Level II') $program->accreditation_status = 'Level II Re-accredited';
            elseif ($request->last_applied_level == 'Level III, Phase 1') $program->accreditation_status = 'Level II Re-accredited';
            elseif ($request->last_applied_level == 'Level III, Phase 2') $program->accreditation_status = 'Level III Re-accredited';
            elseif ($request->last_applied_level == 'Level IV, Phase 1') $program->accreditation_status = 'Level III Re-accredited';
            elseif ($request->last_applied_level == 'Level IV, Phase 2') $program->accreditation_status = 'Level IV Re-accredited';
            $program->duration_of_validity = \Carbon\Carbon::parse($request->duration_of_validity)->format('Y-m-d');
            $program->rating_obtained = $request->rating_obtained;
            $program->campus_id = $id;
            $program->save();
            return response()->json(['status' => true, 'message' => 'Successfully added program!']);
        }
        return response()->json(['status' => false, 'message' => 'Program already exist!']);
    }

    public function showProgram($id){
        $program = Program::where('campus_id', $id)->get();
        return response()->json($program);
    }

    public function deleteProgram($id){
        $program = Program::where('id',$id)->first();
        $check = ApplicationProgram::where('program_id', $program->id)->get();
        if ($check->count() > 0) return response()->json(['status' => false, 'message' => 'Cannot delete program!']);
        $program->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted program!']);
    }

    public function editProgram(request $request, $id){
        $program = Program::where('id', $id)->first();
        $program->program_name = $request->program_name;
        $program->save();
        return response()->json(['status' => true, 'message' => 'Successfully edited program name', 'suc' => $program]);
    }

    public function selectInstrument($programID, $intendedProgramID){
        $areas = AreaInstrument::where('intended_program_id', $intendedProgramID)->get();
        foreach ($areas as $area){
            $instrumentProgram = InstrumentProgram::where([
                ['program_id', $programID], ['area_instrument_id', $area->id]
            ])->first();

            if(!(is_null($instrumentProgram)))
            {
                $instrumentProgram->delete();
            }
            $instrumentProgram = new InstrumentProgram();
            $instrumentProgram->program_id = $programID;
            $instrumentProgram->area_instrument_id = $area->id;
            $instrumentProgram->save();

            $instrumentParamenters = InstrumentParameter::where('area_instrument_id', $area->id)->get();
            foreach ($instrumentParamenters as $instrumentParamenter){
                $parameter = new ParameterProgram();
                $parameter->program_instrument_id = $instrumentProgram->id;
                $parameter->parameter_id = $instrumentParamenter->parameter_id;
                $parameter->save();

                $statements = InstrumentStatement::where('instrument_parameter_id', $instrumentParamenter->id)->get();
                foreach ($statements as $statement){
                    $programStatement = new ProgramStatement();
                    $programStatement->program_parameter_id = $parameter->id;
                    $programStatement->benchmark_statement_id = $statement->benchmark_statement_id;
                    $programStatement->parent_statement_id = $statement->parent_statement_id;
                    $programStatement->save();
                }
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully added instrument!']);
    }

//    public function selectInstrument($programID, $instrumentID){
//
//        $instrumentProgram = InstrumentProgram::where([
//            ['program_id', $programID], ['area_instrument_id', $instrumentID]
//        ])->first();
//        if(is_null($instrumentProgram))
//        {
//            $instrumentProgram = new InstrumentProgram();
//            $instrumentProgram->program_id = $programID;
//            $instrumentProgram->area_instrument_id = $instrumentID;
//            $instrumentProgram->save();
//
//            $instrumentParamenters = InstrumentParameter::where('area_instrument_id', $instrumentID)->get();
//            foreach ($instrumentParamenters as $instrumentParamenter){
//                $parameter = new ParameterProgram();
//                $parameter->program_instrument_id = $instrumentProgram->id;
//                $parameter->parameter_id = $instrumentParamenter->parameter_id;
//                $parameter->save();
//
//                $statements = InstrumentStatement::where('instrument_parameter_id', $instrumentParamenter->id)->get();
//                foreach ($statements as $statement){
//                    $programStatement = new ProgramStatement();
//                    $programStatement->program_parameter_id = $parameter->id;
//                    $programStatement->benchmark_statement_id = $statement->benchmark_statement_id;
//                    $programStatement->parent_statement_id = $statement->parent_statement_id;
//                    $programStatement->save();
//                }
//            }
//            return response()->json(['status' => true, 'message' => 'Successfully added instrument!']);
//        }
//        return response()->json(['status' => false, 'message' => 'Already added']);
//    }

    public function showInstrumentProgram($id){
        $instrumentPrograms = DB::table('instruments_programs')
            ->join('area_instruments', 'instruments_programs.area_instrument_id', '=', 'area_instruments.id')
            ->where('instruments_programs.program_id', $id)
            ->get();
        return response()->json(['instruments' => $instrumentPrograms]);
    }

    public function showStatement($id){
        $area = InstrumentProgram::where('id', $id)->first();
        $instrumentStatement = DB::table('programs_statements')
            ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
            ->join('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'programs_statements.benchmark_statement_id')
            ->join('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
            ->join('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
            ->where('instruments_parameters.area_instrument_id',$area->area_instrument_id)
            ->where('programs_statements.program_instrument_id', $area->id)
            ->select('programs_statements.program_instrument_id', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','programs_statements.parent_statement_id', 'parameters_statements.parameter_id', 'parameters.parameter')
            ->orderBy('parameters.parameter')
            ->get();
        return response()->json(['statements' => $instrumentStatement]);
    }

    public function removeInstrument($programID, $instrumentID){
        $instrumentProgram = InstrumentProgram::where([
            ['program_id', $programID], ['area_instrument_id', $instrumentID]
        ]);
        $instrumentProgram->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted instrument!']);
    }

}
