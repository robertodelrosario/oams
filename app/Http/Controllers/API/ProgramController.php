<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AreaInstrument;
use App\BenchmarkStatement;
use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\InstrumentProgram;
use App\InstrumentStatement;
use App\Office;
use App\ParameterProgram;
use App\Program;
use App\ProgramStatement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
            'latest_applied_level' => 'required'
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $check = Program::where([
            ['campus_id', $id], [strtolower('program_name'), strtolower($request->program_name)]
        ])->first();

        if(is_null($check)){
            $program = new Program();
            $program->program_name = $request->program_name;
            $program->latest_applied_level = $request->latest_applied_level;
            $program->type = $request->type;
            if($request->latest_applied_level == 'For PSV Accreditation') $program->accreditation_status = 'For PSV Accreditation';
            elseif($request->latest_applied_level == 'Candidate') $program->accreditation_status = 'Candidate';
            elseif ($request->latest_applied_level == 'Level I') $program->accreditation_status = 'Level I Accredited';
            elseif ($request->latest_applied_level == 'Level II') $program->accreditation_status = 'Level II Re-accredited';
            elseif ($request->latest_applied_level == 'Level III, Phase 1') $program->accreditation_status = 'Level II Re-accredited';
            elseif ($request->latest_applied_level == 'Level III, Phase 2') $program->accreditation_status = 'Level III Re-accredited';
            elseif ($request->latest_applied_level == 'Level IV, Phase 1') $program->accreditation_status = 'Level III Re-accredited';
            elseif ($request->latest_applied_level == 'Level IV, Phase 2') $program->accreditation_status = 'Level IV Re-accredited';
            if(!(is_null($request->duration_of_validity)))$program->duration_of_validity = \Carbon\Carbon::parse($request->duration_of_validity)->format('Y-m-d');
            $program->rating_obtained = $request->rating_obtained;
            $program->campus_id = $id;
            if(!(is_null($request->office_id))) $program->office_id = $request->office_id;
            $program->save();
            return response()->json(['status' => true, 'message' => 'Successfully added program!']);
        }
        return response()->json(['status' => false, 'message' => 'Program already exist!']);
    }

    public function showProgram($id){
        $collection = new Collection();
        $programs = Program::where('campus_id', $id)->get();
        foreach ($programs as $program){
            $office_name = null;
            if($program->office_id != null){
                $office = Office::where('id',$program->office_id)->first();
                $office_name = $office->office_name;
            }
            $collection->push([
                'id' => $program->id,
                'program_name' => $program->program_name,
                'rating_obtained' => $program->rating_obtained,
                'accreditation_status' => $program->accreditation_status,
                'latest_applied_level' => $program->latest_applied_level,
                'duration_of_validity' => $program->duration_of_validity,
                'type' => $program->type,
                'campus_id' => $program->campus_id,
                'created_at' => $program->created_at,
                'updated_at' => $program->updated_at,
                'office_id' => $program->office_id,
                'office_name' => $office_name
            ]);
        }
        return response()->json($collection);
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
        $program->type = $request->type;
        $program->save();
        return response()->json(['status' => true, 'message' => 'Successfully edited program name', 'suc' => $program]);
    }

    public function addOffice($id, $office_id){
        $program = Program::where('id', $id)->first();
        $program->office_id = $office_id;
        $program->save();
        return response()->json(['status' => true, 'message' => 'Successfully assigned a program to office!']);
    }

    public function deleteProgramOffice($id){
        $program = Program::where('id', $id)->first();
        $program->office_id = null;
        $program->save();
        return response()->json(['status' => true, 'message' => 'Successfully program a program to office!']);
    }

    public function selectInstrument($programID, $intendedProgramID){
        $areas = AreaInstrument::where('intended_program_id', $intendedProgramID)->get();
        $count = 0;
        foreach ($areas as $area){
            $check = InstrumentProgram::where([
                ['program_id', $programID], ['area_instrument_id',$area->id]
            ])->first();
            if(!(is_null($check))) $count++;
        }
        if($count == 0)
        {
            $instrument = InstrumentProgram::where('program_id', $programID);
            $instrument->delete();

            $areas = AreaInstrument::where('intended_program_id', $intendedProgramID)->get();
            foreach ($areas as $area){
                $instrumentProgram = new InstrumentProgram();
                $instrumentProgram->program_id = $programID;
                $instrumentProgram->area_instrument_id = $area->id;
                $instrumentProgram->save();

                $instrumentParamenters = InstrumentParameter::where('area_instrument_id', $area->id)->get();
                if(count($instrumentParamenters) != 0){
                    foreach ($instrumentParamenters as $instrumentParamenter){
                        $parameter = new ParameterProgram();
                        $parameter->program_instrument_id = $instrumentProgram->id;
                        $parameter->parameter_id = $instrumentParamenter->parameter_id;
                        $parameter->save();

                        $statements = InstrumentStatement::where('instrument_parameter_id', $instrumentParamenter->id)->get();
                        if(count($statements) != 0){
                            foreach ($statements as $statement){
                                $programStatement = new ProgramStatement();
                                $programStatement->program_parameter_id = $parameter->id;
                                $programStatement->benchmark_statement_id = $statement->benchmark_statement_id;
                                $programStatement->parent_statement_id = $statement->parent_statement_id;
                                $programStatement->save();
                            }
                        }
                    }
                }

            }
            return response()->json(['status' => true, 'message' => 'Successfully added instrument!']);
        }
        else{
            $areas = AreaInstrument::where('intended_program_id', $intendedProgramID)->get();
            foreach ($areas as $area){

                $instrumentProgram = InstrumentProgram::where([
                    ['program_id', $programID], ['area_instrument_id',$area->id]
                ])->first();

                $instrumentParameters = InstrumentParameter::where('area_instrument_id', $area->id)->get();

                if(count($instrumentParameters) != 0){
                    foreach ($instrumentParameters as $instrumentParameter){
                        $parameter = ParameterProgram::where([
                            ['program_instrument_id', $instrumentProgram->id], ['parameter_id', $instrumentParameter->parameter_id]
                        ])->first();


                        if(is_null($parameter)){
                            $parameter = new ParameterProgram();
                            $parameter->program_instrument_id = $instrumentProgram->id;
                            $parameter->parameter_id = $instrumentParameter->parameter_id;
                            $parameter->save();
                        }

                        $statements = InstrumentStatement::where('instrument_parameter_id', $instrumentParameter->id)->get();
                        if(count($statements) != 0){
                            foreach ($statements as $statement){

                                $programStatement = ProgramStatement::where([
                                    ['program_parameter_id', $parameter->id], ['benchmark_statement_id', $statement->benchmark_statement_id]
                                ])->first();
                                if(is_null($programStatement)){
                                    $programStatement = new ProgramStatement();
                                    $programStatement->program_parameter_id = $parameter->id;
                                    $programStatement->benchmark_statement_id = $statement->benchmark_statement_id;
                                    $programStatement->parent_statement_id = $statement->parent_statement_id;
                                    $programStatement->save();
                                }
                            }
                        }
                    }
                }

            }
            return response()->json(['status' => true, 'message' => 'Successfully updated instrument!']);
        }


    }

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

    public function showAllProgram(){
        return response()->json(Program::all());
    }

    public function editProgramType($id, $type){
        $program = Program::where('id', $id)->first();
        if($type == 0) $program->type = 'Undergraduate';
        else $program->type = 'Graduate';
        $program->save();
    }
}
