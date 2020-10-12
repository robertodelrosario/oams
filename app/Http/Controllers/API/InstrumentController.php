<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\AreaInstrument;
use App\BenchmarkStatement;
use App\InstrumentStatement;
use App\Parameter;
use App\ParameterStatement;
use App\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstrumentController extends Controller
{

    //CREATE INSTRUMENT FUNTION
     public function createInstrument(request $request){
        $validator = Validator::make($request->all(), [
            'intended_program' => 'required',
            'area_number' => 'required',
            'area_name' => 'required',
            'version' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process instrument. Required data']);

        $areaInstrument = new AreaInstrument();
        $areaInstrument->intended_program = $request->intended_program;
        $areaInstrument->area_number = $request->area_number;
        $areaInstrument->area_name = $request->area_name;
        $areaInstrument->version = $request->version;
        $areaInstrument->save();
        return response()->json(['status' => true, 'message' => 'Successfully added instrument!']);
    }

    //CREATE STATEMENT FUNCTION
    public function createStatement(request $request)
    {
        $validator = Validator::make($request->all(), [
            'statement' => 'required',
            'type' => 'required',
            //'statement_parent' => 'required',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process statement. Required data']);

        $check = BenchmarkStatement::where('statement', $request->statement)->first();
        $benchmarkStatement = new BenchmarkStatement();

        if(is_null($check)){
            $benchmarkStatement->statement = $request->statement;
            $benchmarkStatement->type = $request->type;
            if(is_null($request->statement_parent))
                $benchmarkStatement->statement_parent = -1;
            else
            {
                $statement = BenchmarkStatement::where('statement',$request->statement_parent)->first();
                $benchmarkStatement->statement_parent = $statement->id;
            }
            $benchmarkStatement->save();

            $parameter= Parameter::where('id',$request->parameter_id)->first();
            $benchmarkStatement->parameters()->attach($parameter);
            $instrument= AreaInstrument::where('id',$request->instrument_id)->first();
            $benchmarkStatement->areaInstruments()->attach($instrument);
        }
        else{
            $parameterStatement = new ParameterStatement();
            $parameter= Parameter::where('id',$request->parameter_id)->first();
            $parameterStatement->parameter_id = $parameter->id;
            $parameterStatement->benchmark_statement_id = $check->id;
            $parameterStatement->save();

            $instrumentStatement = new InstrumentStatement();
            $instrument= AreaInstrument::where('id',$request->instrument_id)->first();
            $instrumentStatement->area_instrument_id = $instrument->id;
            $instrumentStatement->benchmark_statement_id = $check->id;
            $instrumentStatement->save();
        }
        return response()->json(['status' => true, 'message' => 'Successfully added benchmark statements!']);
    }

    //CREATE PARAMETER FUNCTION
    public function createParameter(request $request){
        $validator = Validator::make($request->all(), [
            'parameter' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $check = Parameter::where('parameter', $request->parameter)->first();
        if(is_null($check)){
            $parameter = new Parameter();
            $parameter->parameter = $request->parameter;
            $parameter->save();
            $instrument= AreaInstrument::where('id',$request->instrument_id)->first();
            $parameter->areaInstruments()->attach($instrument);
            return response()->json(['status' => true, 'message' => 'Successfully created parameter!', 'parameter_id' => $parameter->id]);
        }
        return response()->json(['status' => true, 'message' => 'Successfully created parameter!', 'parameter_id' => $check->id]);

    }

    public function showParameters(request $request){
        
    }
}
