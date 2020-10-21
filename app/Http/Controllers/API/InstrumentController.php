<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\AreaInstrument;
use App\BenchmarkStatement;
use App\InstrumentParameter;
use App\InstrumentStatement;
use App\Parameter;
use App\ParameterStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
             $instrument = AreaInstrument::where('intended_program', $areaInstrument->intended_program)
                ->where('area_number', $areaInstrument->area_number)->first();
             return response()->json(['status' => true, 'message' => 'Successfully added instrument!','instrument_id' => $instrument->id]);
         }
         return response()->json(['status' => true, 'message' => 'Instrument already exist!']);

    }

    //CREATE STATEMENT FUNCTION
    public function createStatement(request $request)
    {
        $validator = Validator::make($request->all(), [
            'statement' => 'required',
            'type' => 'required',
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
    public function showParameter($id){
        $parameter = DB::table('instruments_parameters')
            ->join('parameters', 'instruments_parameters.parameter_id','=','parameters.id')
            ->where('instruments_parameters.area_instrument_id', $id)
            ->get();
        return response()->json($parameter);
    }

    public function deleteParameter($id){
        $parameter = Parameter::where('id', $id);
        $parameter->delete();
        return response()->json(['status' => true, 'message' => 'Parameter successfully deleted!']);
    }

    public function showInstrument(){
        return response()->json(AreaInstrument::all());
    }

    public function deleteInstrument($id){
        $areaInstrument = AreaInstrument::where('id', $id);
        $areaInstrument->delete();
        return response()->json(['status' => true, 'message' => 'Instrument successfully deleted!']);
    }

    public function showStatement($id){
        $instrumentStatement = DB::table('instruments_statements')
            ->leftjoin('benchmark_statements','instruments_statements.benchmark_statement_id','=', 'benchmark_statements.id')
            ->leftjoin('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'benchmark_statements.id')
            ->leftjoin('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
            ->leftjoin('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
            ->where('instruments_parameters.area_instrument_id',$id )
            ->where('instruments_statements.area_instrument_id', $id)
            ->select('instruments_statements.area_instrument_id', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','benchmark_statements.statement_parent', 'parameters_statements.parameter_id', 'parameters.parameter')
            ->orderBy('parameters.parameter')
            ->get();
        return response()->json($instrumentStatement);
    }

    public function editStatement(request $request){
        $validator = Validator::make($request->all(), [
            'parameter_id' => 'required',
            'instrument_id' => 'required',
            'id' => 'required',
            'statement' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $statement = BenchmarkStatement::where('statement', $request->statement)->first();

        $parameterStatement= ParameterStatement::where([
            ['parameter_id',$request->parameter_id], ['benchmark_statement_id', $request->id]
        ]);
        if(is_null($parameterStatement)) return response()->json(['status' => true, 'message' => 'Statement in parameter does not exist']);
        $parameterStatement->delete();

        $instrumentStatement = InstrumentStatement::where([
            ['area_instrument_id',$request->instrument_id], ['benchmark_statement_id', $request->id]
        ]);
        if(is_null($parameterStatement)) return response()->json(['status' => true, 'message' => 'Statement in instrument does not exist']);
        $instrumentStatement->delete();

        if(is_null($statement)){

            $benchmarkStatement = new BenchmarkStatement();
            $benchmarkStatement->statement = $request->statement;
            $benchmarkStatement->type = $request->type;
            $benchmarkStatement->statement_parent = $request->statement_parent;
            $benchmarkStatement->save();

            $parameter= Parameter::where('id',$request->parameter_id)->first();
            $benchmarkStatement->parameters()->attach($parameter);
            $instrument= AreaInstrument::where('id',$request->instrument_id)->first();
            $benchmarkStatement->areaInstruments()->attach($instrument);

            return response()->json(['status' => true, 'message' => 'Updated successfully [1]']);
        }

        $parameterStatement= new ParameterStatement();
        $parameterStatement->parameter_id = $request->parameter_id;
        $parameterStatement->benchmark_statement_id = $statement->id;
        $parameterStatement->save();

        $instrumentStatement =new InstrumentStatement();
        $instrumentStatement->area_instrument_id = $request->instrument_id;
        $instrumentStatement->benchmark_statement_id = $statement->id;
        $instrumentStatement->save();

        return response()->json(['status' => true, 'message' => 'Updated successfully [2]']);
    }
}
