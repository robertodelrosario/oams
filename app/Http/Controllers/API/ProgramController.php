<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AreaInstrument;
use App\AreaMandatory;
use App\AreaMean;
use App\AssignedUser;
use App\BenchmarkStatement;
use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\InstrumentProgram;
use App\InstrumentScore;
use App\InstrumentStatement;
use App\Office;
use App\ParameterMean;
use App\ParameterProgram;
use App\Program;
use App\ProgramReportTemplate;
use App\ProgramStatement;
use App\ReportTemplate;
use App\TemplateTag;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
            elseif ($request->latest_applied_level == 'Level III') $program->accreditation_status = 'Level III Re-accredited';
            elseif ($request->latest_applied_level == 'Level IV') $program->accreditation_status = 'Level IV Re-accredited';
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
        $program->rating_obtained = $request->rating_obtained;
        if(Str::contains($request->latest_applied_level,'For PSV Accreditation')) $program->accreditation_status = 'For PSV Accreditation';
        elseif(Str::contains($request->latest_applied_level, 'Candidate')) $program->accreditation_status = 'Candidate';
        elseif (Str::contains($request->latest_applied_level ,'Level I')) $program->accreditation_status = 'Level I Accredited';
        elseif (Str::contains($request->latest_applied_level,'Level II')) $program->accreditation_status = 'Level II Re-accredited';
        elseif (Str::contains($request->latest_applied_level, 'Level III')) $program->accreditation_status = 'Level III Re-accredited';
        elseif (Str::contains($request->latest_applied_level, 'Level IV')) $program->accreditation_status = 'Level IV Re-accredited';
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
        $prog = Program::where('id', $programID)->first();
        if($count == 0)
        {
            $instrument = InstrumentProgram::where('program_id', $programID);
            $instrument->delete();

            $areas = AreaInstrument::where('intended_program_id', $intendedProgramID)->get();
            $areas_collection = new Collection();
            if($intendedProgramID == 42 || $intendedProgramID == 47){
                if($intendedProgramID == 42) $level = 'LEVEL III -';
                else $level = 'LEVEL IV -';
                foreach ($areas as $area){
                    $area_mandatories = AreaMandatory::where('area_instrument_id', $area->id)->get();
                    foreach ($area_mandatories as $area_mandatory){
                        if($area_mandatory->type == 'Mandatory' && $prog->type == $area_mandatory->program_status){
                            $areas_collection->push([
                                'id' =>  $area->id,
                                'area_name' => $level.' '.$area->area_name
                            ]);
                        }
                    }

                }
            }
            else{
                foreach ($areas as $area){
                    $areas_collection->push([
                        'id' =>  $area->id,
                        'area_name' => $area->area_name
                    ]);
                }
            }
            foreach ($areas_collection as $area){
                $instrumentProgram = new InstrumentProgram();
                $instrumentProgram->program_id = $programID;
                $instrumentProgram->area_instrument_id = $area['id'];
                $instrumentProgram->save();

                $instrumentParamenters = InstrumentParameter::where('area_instrument_id', $area['id'])->get();
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
                $templates = ReportTemplate::where('campus_id', $prog->campus_id)->get();
                foreach ($templates as $template){
                    $temp_tags = TemplateTag::where('report_template_id', $template->id)->get();
                    foreach ($temp_tags as $temp_tag){
                        if($temp_tag->tag == $area['area_name']){
                            $program_report_template = new ProgramReportTemplate();
                            $program_report_template->report_template_id = $template->id;
                            $program_report_template->instrument_program_id = $instrumentProgram->id;
                            $program_report_template->save();
                        }
                    }
                }
            }
            return response()->json(['status' => true, 'message' => 'Successfully added instrument!']);
        }
        else{
            $areas = AreaInstrument::where('intended_program_id', $intendedProgramID)->get();
            $areas_collection = new Collection();
            if($intendedProgramID == 42 || $intendedProgramID == 47){
                if($intendedProgramID == 42) $level = 'LEVEL III -';
                else $level = 'LEVEL IV -';
                foreach ($areas as $area){
                    $area_mandatories = AreaMandatory::where('area_instrument_id', $area->id)->get();
                    foreach ($area_mandatories as $area_mandatory){
                        if($area_mandatory->type == 'Mandatory' && $prog->type == $area_mandatory->program_status){
                            $areas_collection->push([
                                'id' =>  $area->id,
                                'area_name' => $level.' '.$area->area_name
                            ]);
                        }
                    }

                }
            }
            else{
                foreach ($areas as $area){
                    $areas_collection->push([
                        'id' =>  $area->id,
                        'area_name' => $area->area_name
                    ]);
                }
            }
            foreach ($areas_collection as $area){

                $instrumentProgram = InstrumentProgram::where([
                    ['program_id', $programID], ['area_instrument_id',$area['id']]
                ])->first();

                $instrumentParameters = InstrumentParameter::where('area_instrument_id', $area['id'])->get();

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
                $templates = ReportTemplate::where('campus_id', $prog->campus_id)->get();
                foreach ($templates as $template){
                    $temp_tags = TemplateTag::where('report_template_id', $template->id)->get();
                    foreach ($temp_tags as $temp_tag){
                        if($temp_tag->tag == $area['area_name']){
                            $program_report_template = ProgramReportTemplate::where([
                                ['report_template_id', $template->id], ['instrument_program_id', $instrumentProgram->id]
                            ])->first();
                            if(is_null($program_report_template)) {
                                $program_report_template = new ProgramReportTemplate();
                                $program_report_template->report_template_id = $template->id;
                                $program_report_template->instrument_program_id = $instrumentProgram->id;
                                $program_report_template->save();
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

    public function updateSelectedInstrument($programID){
        $instruments = InstrumentProgram::where('program_id', $programID)->get();
        foreach ($instruments as $instrument){
            $instrument_parameters = InstrumentParameter::where('area_instrument_id', $instrument->area_instrument_id)->get();
            foreach ($instrument_parameters as $parameter){
                $program_parameter = ParameterProgram::where([
                    ['program_instrument_id', $instrument->id], ['parameter_id', $parameter->parameter_id]
                ])->first();
                $parameter_statements = InstrumentStatement::where('instrument_parameter_id', $parameter->id)->get();
                foreach ($parameter_statements as $parameter_statement){
                    $program_statements = ProgramStatement::where('program_parameter_id', $program_parameter->id)->get();
                    foreach ($program_statements as $program_statement){
                        if($parameter_statement->benchmark_statement_id == $program_statement->benchmark_statement_id){
                            $statement = ProgramStatement::where([
                                ['program_parameter_id', $program_parameter->id], ['benchmark_statement_id', $program_statement->benchmark_statement_id]
                                ])->first();
                            $statement->parent_statement_id = $parameter_statement->parent_statement_id;
                            $success = $statement->save();
                            if ($success) continue;
                            else return response()->json(['status' => false, 'message' => 'error']);
                        }
                    }
                }

            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully updated instrument!']);
    }

    public function updateSelectedStatement($id, $instrumentParameterID){
        $parameter = ParameterProgram::where('id', $id)->first();
        $program_statements = ProgramStatement::where('program_parameter_id', $parameter->id)->get();
        $instrument_statements = InstrumentStatement::where('instrument_parameter_id', $instrumentParameterID)->get();
        foreach ($instrument_statements as $instrument_statement){
            $check = ProgramStatement::where([
                ['program_parameter_id', $id], ['benchmark_statement_id', $instrument_statement->benchmark_statement_id]
            ])->first();
            if(is_null($check)){
                $new_statement = new ProgramStatement();
                $new_statement->program_parameter_id = $id;
                $new_statement->benchmark_statement_id = $instrument_statement->benchmark_statement_id;
                $new_statement->parent_statement_id = $instrument_statement->parent_statement_id;
                $new_statement->save();
            }
        }
        foreach ($program_statements as $program_statement){
            $check = InstrumentStatement::where([
                ['instrument_parameter_id', $instrumentParameterID], ['benchmark_statement_id', $program_statement->benchmark_statement_id]
            ])->first();
            if(is_null($check)){
                $program_statement->delete();
            }
        }
    }

    public function updateSelectedOBE($id){
        $parameters = ParameterProgram::where('program_instrument_id', $id)->get();
        $assigned_users = AssignedUser::where([
            ['transaction_id', $id], ['role','like','%accreditor%']
        ])->get();
        foreach ($assigned_users as $assigned_user) {
            $check_area_mean = AreaMean::where([['instrument_program_id', $id],['assigned_user_id', $assigned_user->id]])->first();
            echo $check_area_mean;
            if(is_null($check_area_mean)){
                $area_mean = new AreaMean();
                $area_mean->instrument_program_id = $id;
                $area_mean->assigned_user_id = $assigned_user->id;
                $area_mean->area_mean = 0;
                $area_mean->save();
            }
            foreach ($parameters as $parameter) {
                $program_statements = ProgramStatement::where('program_parameter_id', $parameter->id)->get();
                $check_parameter = ParameterMean::where([
                    ['program_parameter_id', $parameter->id], ['assigned_user_id',$assigned_user->id]
                ])->first();
                if(is_null($check_parameter)){
                    $param = new ParameterMean();
                    $param->program_parameter_id = $parameter->id;
                    $param->assigned_user_id = $assigned_user->id;
                    $param->parameter_mean = 0;
                    $param->save();
                }
                foreach ($program_statements as $program_statement) {
                    $check = InstrumentScore::where([
                        ['item_id', $program_statement->id], ['assigned_user_id', $assigned_user->id]
                    ])->first();
                    if(is_null($check)){
                        $item = new InstrumentScore();
                        $item->item_id = $program_statement->id;
                        $item->assigned_user_id = $assigned_user->id;
                        $item->save();
                    }
                }
            }
        }
    }
}
