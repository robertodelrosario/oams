<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\AreaInstrument;
use App\BenchmarkStatement;
use App\InstrumentParameter;
use App\InstrumentStatement;
use App\Parameter;
use App\ParameterStatement;
use App\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use mysql_xdevapi\Table;

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

        $test = AreaInstrument::where([
            ['intended_program', $request->intended_program], ['area_number', $request->area_number]
        ])->first();
         if(is_null($test)){
             $areaInstrument = new AreaInstrument();
             $areaInstrument->intended_program = $request->intended_program;
             $areaInstrument->area_number = $request->area_number;
             $areaInstrument->area_name = $request->area_name;
             $areaInstrument->version = $request->version;
             $areaInstrument->save();
             return response()->json(['status' => true, 'message' => 'Successfully added instrument!']);
         }
         return response()->json(['status' => true, 'message' => 'Instrument already exist!']);

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
            $test = ParameterStatement::where([
                ['parameter_id',$parameter->id], ['benchmark_statement_id', $check->id]
            ])->first();

            if(is_null($test)){
                $parameterStatement->parameter_id = $parameter->id;
                $parameterStatement->benchmark_statement_id = $check->id;
                $parameterStatement->save();
            }

            $instrumentStatement = new InstrumentStatement();
            $instrument= AreaInstrument::where('id',$request->instrument_id)->first();
            $test = InstrumentStatement::where([
                ['area_instrument_id', $instrument->id],['benchmark_statement_id', $check->id]
            ])->first();
            if(is_null($test)){
                $instrumentStatement->area_instrument_id = $instrument->id;
                $instrumentStatement->benchmark_statement_id = $check->id;
                $instrumentStatement->save();
            }
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

            $instrument= AreaInstrument::where('id',$request->area_instrument_id)->first();
            $parameter->areaInstruments()->attach($instrument);
            return response()->json(['status' => true, 'message' => 'Successfully created parameter!', 'parameter_id' => $parameter->id]);
        }

        $instrument= AreaInstrument::where('id',$request->area_instrument_id)->first();
        $instrumentParameter = new InstrumentParameter();
        $test = InstrumentParameter::where([
            ['area_instrument_id', $instrument->id],['parameter_id', $check->id]
        ])->first();

        if (is_null($test)){
            $instrumentParameter->area_instrument_id = $instrument->id;
            $instrumentParameter->parameter_id = $check->id;
            $instrumentParameter->save();
            return response()->json(['status' => true, 'message' => 'Successfully created parameter!', 'parameter_id' => $check->id]);
        }
        return response()->json(['status' => true, 'message' => 'Parameter already exist!', 'parameter_id' => $check->id]);

    }

    //SHOW PARAMETER LIST BASED ON INSTRUMENT
    public function showParameter(request $request){
        $parameter = DB::table('instruments_parameters')
            ->join('parameters', 'instruments_parameters.parameter_id','=','parameters.id')
            ->where('instruments_parameters.area_instrument_id', $request->area_instrument_id)
            ->get();
        return response()->json($parameter);
    }

    public function deleteParameter(request $request){
        $parameter = Parameter::where('id', $request->parameter_id);
        $parameter->delete();
        return response()->json(['status' => true, 'message' => 'Parameter successfully deleted!']);
    }

    public function showInstrument(){
        return response()->json(AreaInstrument::all());
    }

    public function deleteInstrument(request $request){
        $areaInstrument = AreaInstrument::where('id', $request->area_instrument_id);
        $areaInstrument->delete();
        return response()->json(['status' => true, 'message' => 'Instrument successfully deleted!']);
    }

    public function showStatement(request $request){
        $instrumentStatement = DB::table('instruments_statements')
            ->leftjoin('benchmark_statements','instruments_statements.benchmark_statement_id','=', 'benchmark_statements.id')
            ->leftjoin('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'benchmark_statements.id')
            ->leftjoin('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
            ->leftjoin('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
            ->where('instruments_parameters.area_instrument_id',$request->area_instrument_id )
            ->where('instruments_statements.area_instrument_id', $request->area_instrument_id)
            ->select('instruments_statements.area_instrument_id', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','benchmark_statements.statement_parent', 'parameters_statements.parameter_id', 'parameters.parameter')
            ->orderBy('parameters.parameter')
            ->get();
        return response()->json($instrumentStatement);
    }
}
