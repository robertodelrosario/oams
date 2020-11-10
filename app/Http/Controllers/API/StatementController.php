<?php

namespace App\Http\Controllers\API;

use App\AreaInstrument;
use App\BenchmarkStatement;
use App\InstrumentStatement;
use App\Parameter;
use App\ParameterStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class StatementController extends Controller
{
    /*public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login', 'register', 'me']]);
    }*/

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
            $benchmarkStatement->save();
            $parameter= Parameter::where('id',$request->parameter_id)->first();
            $benchmarkStatement->parameters()->attach($parameter);

            $instrument = new InstrumentStatement();
            $instrument->area_instrument_id = $request->instrument_id;
            $instrument->benchmark_statement_id = $benchmarkStatement->id;
            $instrument->parent_statement_id = $request->statement_parent;
            $instrument->save();
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
                $instrumentStatement->parent_statement_id = $request->statement_parent;
                $instrumentStatement->save();
            }
        }
        $check = BenchmarkStatement::where('statement', $request->statement)->first();
        return response()->json(['status' => true, 'message' => 'Successfully added benchmark statements!', 'statement' => $check]);
    }


    public function showStatement($id){
        $instrumentStatement = DB::table('instruments_statements')
            ->leftjoin('benchmark_statements','instruments_statements.benchmark_statement_id','=', 'benchmark_statements.id')
            ->leftjoin('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'benchmark_statements.id')
            ->leftjoin('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
            ->leftjoin('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
            ->where('instruments_parameters.area_instrument_id',$id )
            ->where('instruments_statements.area_instrument_id', $id)
            ->select('instruments_statements.area_instrument_id', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','instruments_statements.parent_statement_id', 'parameters_statements.parameter_id', 'parameters.parameter')
            ->orderBy('parameters.parameter')
            ->get();
        return response()->json($instrumentStatement);
    }

    public function editStatement(request $request){
        $validator = Validator::make($request->all(), [
//            'parameter_id' => 'required',
//            'area_instrument_id' => 'required',
//            'id' => 'required',
//            'statement_parent' => 'required',
            'statement' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $statement = BenchmarkStatement::where('statement', $request->statement)->first();

        $parameterStatement= ParameterStatement::where([
            ['parameter_id',$request->parameter_id], ['benchmark_statement_id', $request->id]
        ]);
        if(is_null($parameterStatement)) return response()->json(['status' => true, 'message' => 'Statement in parameter does not exist']);
        $parameterStatement->delete();

        $instruStatement = InstrumentStatement::where([
            ['area_instrument_id',$request->area_instrument_id], ['benchmark_statement_id', $request->id]
        ])->first();
        if(is_null($instruStatement)) return response()->json(['status' => true, 'message' => 'Statement in instrument does not exist']);

        $instruStatement->delete();

        if(is_null($statement)){

            $benchmarkStatement = new BenchmarkStatement();
            $benchmarkStatement->statement = $request->statement;
            $benchmarkStatement->type = $request->type;
            $benchmarkStatement->save();

            $parameter= Parameter::where('id',$request->parameter_id)->first();
            $benchmarkStatement->parameters()->attach($parameter);

            $instrument = new InstrumentStatement();
            $instrument->area_instrument_id = $request->area_instrument_id;
            $instrument->benchmark_statement_id = $benchmarkStatement->id;
            $instrument->parent_statement_id = $request->parent_statement_id;
            $instrument->save();

            $parents = InstrumentStatement::where('parent_statement_id', $request->id)->get();
            foreach ($parents as $parent){
                $parent->parent_statement_id = $benchmarkStatement->id;
                $parent->save();
            }
            return response()->json(['status' => true, 'message' => 'Updated successfully [1]']);
        }

        $parameterStatement= new ParameterStatement();
        $parameterStatement->parameter_id = $request->parameter_id;
        $parameterStatement->benchmark_statement_id = $statement->id;
        $parameterStatement->save();

        $instrumentStatement =new InstrumentStatement();
        $instrumentStatement->area_instrument_id = $request->area_instrument_id;
        $instrumentStatement->benchmark_statement_id = $statement->id;
        $instrumentStatement->parent_statement_id = $request->parent_statement_id;
        $instrumentStatement->save();

        $parents = InstrumentStatement::where('parent_statement_id', $request->id)->get();
        foreach ($parents as $parent){
            $parent->parent_statement_id = $statement->id;
            $parent->save();
        }
        return response()->json(['status' => true, 'message' => 'Updated successfully [2]']);
    }

    public function deleteStatement($instrumentID, $statementID){
        $statement = BenchmarkStatement::where('id', $statementID)->first();
        $check = InstrumentStatement::where('benchmark_statement_id', $statement->id)->get();
        $checkCount = $check->count();
        if($checkCount>1) {
            $instruStatement = InstrumentStatement::where([
                ['benchmark_statement_id', $statement->id], ['area_instrument_id', $instrumentID]
            ]);
            $instruStatement->delete();
            return response()->json(['status' => true, 'message' => 'removed statement from instrument/statement table']);
        }
        else{
            $statement->delete();
            return response()->json(['status' => true, 'message' => 'removed statement from statement table']);
        }
    }
}
