<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentStatement;
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

            $statements = InstrumentStatement::where('area_instrument_id', $instrumentID)->get();
            foreach($statements as $statement){
                $program_statement = new ProgramStatement();
                $program_statement->program_instrument_id = $instrumentProgram->id;
                $program_statement->benchmark_statement_id = $statement->benchmark_statement_id;
                $program_statement->parent_statement_id = $statement->parent_statement_id;
                $program_statement->save();
            }
            return response()->json(['status' => true, 'message' => 'Successfully added instrument!']);
        }
        return response()->json(['status' => false, 'message' => 'Already added']);
    }

//    public function refreshSelectedInstrument($programID,$instrumentID){
//        $instrumentProgram = InstrumentProgram::where([
//            ['program_id', $programID], ['area_instrument_id', $instrumentID]
//        ])->first();
//        $statements = InstrumentStatement::where('area_instrument_id', $instrumentID)->get();
//        $statements_2 = ProgramStatement::where('program_instrument_id', $instrumentProgram->id)->get();
//        foreach($statements as $statement){
//            $program_statement = new ProgramStatement();
//            $program_statement->program_instrument_id = $instrumentProgram->id;
//            $program_statement->benchmark_statement_id = $statement->benchmark_statement_id;
//            $program_statement->parent_statement_id = $statement->parent_statement_id;
//            $program_statement->save();
//        }
//        return response()->json(['status' => true, 'message' => 'Successfully added instrument!']);
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
