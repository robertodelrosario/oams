<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AreaInstrument;
use App\AreaInstrumentTag;
use App\AreaMandatory;
use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\InstrumentProgram;
use App\InstrumentStatement;
use App\ParameterProgram;
use App\Program;
use App\ProgramReportTemplate;
use App\ProgramStatement;
use App\ReportTemplate;
use App\TemplateTag;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CriteriaForm extends Controller
{
    public function showCriteriaInstrument($id){
        $collection = new Collection();
        $instruments = AreaInstrument::where('intended_program_id', $id)->get();
        $graduate = '';
        $undergraduate = '';
        foreach ($instruments as $instrument){
            $area_mandatories = AreaMandatory::where('area_instrument_id', $instrument->id)->get();
                foreach ($area_mandatories as $area_mandatory){
                    if($area_mandatory->program_status == 'Graduate')
                        $graduate = $area_mandatory->type;
                    elseif ($area_mandatory->program_status == 'Undergraduate')
                        $undergraduate = $area_mandatory->type;
                }
            $tags = AreaInstrumentTag::where('area_instrument_id', $instrument->id)->get();
                $collection->push([
                    'id' => $instrument->id,
                    'intended_program_id' => $instrument->intended_program_id,
                    'area_number' => $instrument->area_number,
                    'area_name' => $instrument->area_name,
                    'version' => $instrument->version,
                    'created_at' => $instrument->created_at,
                    'updated_at' => $instrument->updated_at,
                    'graduate' => $graduate,
                    'undergraduate' => $undergraduate,
                    'tags' => $tags
                ]);
        }
        return response()->json($collection);
    }

    public function addInstrument($id,$program_id){
        $check = InstrumentProgram::where([
            ['program_id', $program_id], ['area_instrument_id',$id]
        ])->first();

        if(!(is_null($check))) return response()->json(['status' => false, 'message'=> 'Already added']);

        $applied_programs = ApplicationProgram::where('program_id', $program_id)->get();
        foreach ($applied_programs as $applied_program){
            $level = $applied_program->level;
        }
        $prog = Program::where('id', $program_id)->first();

        if($level = 'Level III') $level = 'LEVEL III -';
        elseif($level = 'Level IV') $level = 'LEVEL IV -';
        $instrumentProgram = new InstrumentProgram();
        $instrumentProgram->program_id = $program_id;
        $instrumentProgram->area_instrument_id = $id;
        $instrumentProgram->save();
        $area = AreaInstrument::where('id', $id)->first();
        $instrumentParamenters = InstrumentParameter::where('area_instrument_id', $id)->get();
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
        $code = $level.' '.$area->area_name;
        $templates = ReportTemplate::where('campus_id', $prog->campus_id)->get();
        foreach ($templates as $template){
            $temp_tags = TemplateTag::where('report_template_id', $template->id)->get();
            foreach ($temp_tags as $temp_tag){
                if($temp_tag->tag == $code){
                    $program_report_template = new ProgramReportTemplate();
                    $program_report_template->report_template_id = $template->id;
                    $program_report_template->instrument_program_id = $instrumentProgram->id;
                    $program_report_template->save();
                }
            }
        }
        return response()->json(['status' => true, 'message'=> 'Successfully added']);
    }

    public function removeInstrument($id){
        $check = InstrumentProgram::where('id', $id)->first();
        $check->delete();
        return response()->json(['status' => true, 'message'=> 'Successfully removed.']);
    }
}
