<?php

namespace App\Http\Controllers\API;

use App\AreaInstrument;
use App\AreaMandatory;
use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\InstrumentProgram;
use App\InstrumentStatement;
use App\ParameterProgram;
use App\ProgramStatement;
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
                $collection->push([
                    'id' => $instrument->id,
                    'intended_program_id' => $instrument->intended_program_id,
                    'area_number' => $instrument->area_number,
                    'area_name' => $instrument->area_name,
                    'version' => $instrument->version,
                    'created_at' => $instrument->created_at,
                    'updated_at' => $instrument->updated_at,
                    'graduate' => $graduate,
                    'undergraduate' => $undergraduate
                ]);
        }
        return response()->json($collection);
    }

    public function addInstrument($id,$program_id){
        $check = InstrumentProgram::where([
            ['program_id', $program_id], ['area_instrument_id',$id]
        ])->first();

        if(!(is_null($check))) return response()->json(['status' => false, 'message'=> 'Already added']);

        $instrumentProgram = new InstrumentProgram();
        $instrumentProgram->program_id = $program_id;
        $instrumentProgram->area_instrument_id = $id;
        $instrumentProgram->save();

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
        return response()->json(['status' => true, 'message'=> 'Successfully added']);
    }

    public function removeInstrument($id){
        $check = InstrumentProgram::where('id', $id)->first();
        $check->delete();
        return response()->json(['status' => true, 'message'=> 'Successfully removed.']);
    }
}
