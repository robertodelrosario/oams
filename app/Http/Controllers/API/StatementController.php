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
//    public function createStatement(request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'statement' => 'required',
//            'type' => 'required',
//        ]);
//        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process statement. Required data']);
//
//        $check = BenchmarkStatement::where('statement', $request->statement)->first();
//
//        $benchmarkStatement = new BenchmarkStatement();
//
//        if(is_null($check)){
//            $benchmarkStatement->statement = $request->statement;
//            $benchmarkStatement->type = $request->type;
//            $benchmarkStatement->save();
//            $parameter= Parameter::where('id',$request->parameter_id)->first();
//            $benchmarkStatement->parameters()->attach($parameter);
//
//            $instrument = new InstrumentStatement();
//            $instrument->area_instrument_id = $request->instrument_id;
//            $instrument->benchmark_statement_id = $benchmarkStatement->id;
//            $instrument->parent_statement_id = $request->statement_parent;
//            $instrument->save();
//        }
//        else{
//            $parameterStatement = new ParameterStatement();
//            $parameter= Parameter::where('id',$request->parameter_id)->first();
//            $test = ParameterStatement::where([
//                ['parameter_id',$parameter->id], ['benchmark_statement_id', $check->id]
//            ])->first();
//
//            if(is_null($test)){
//                $parameterStatement->parameter_id = $parameter->id;
//                $parameterStatement->benchmark_statement_id = $check->id;
//                $parameterStatement->save();
//            }
//
//            $instrumentStatement = new InstrumentStatement();
//            $instrument= AreaInstrument::where('id',$request->instrument_id)->first();
//            $test = InstrumentStatement::where([
//                ['area_instrument_id', $instrument->id],['benchmark_statement_id', $check->id]
//            ])->first();
//            if(is_null($test)){
//                $instrumentStatement->area_instrument_id = $instrument->id;
//                $instrumentStatement->benchmark_statement_id = $check->id;
//                $instrumentStatement->parent_statement_id = $request->statement_parent;
//                $instrumentStatement->save();
//            }
//        }
//        $check = BenchmarkStatement::where('statement', $request->statement)->first();
//        return response()->json(['status' => true, 'message' => 'Successfully added benchmark statements!', 'statement' => $check]);
//    }


    public function createStatement(request $request, $id)
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

            $instrumentStatement = new InstrumentStatement();
            $instrumentStatement->instrument_parameter_id = $id;
            $instrumentStatement->benchmark_statement_id = $benchmarkStatement->id;
            $instrumentStatement->parent_statement_id = $request->statement_parent;
            $instrumentStatement->save();
        }

        else{
            $test = InstrumentStatement::where([
                ['instrument_parameter_id', $id], ['benchmark_statement_id', $check->id]
            ])->first();

            if(is_null($test)){
                $instrumentStatement = new InstrumentStatement();
                $instrumentStatement->instrument_parameter_id = $id;
                $instrumentStatement->benchmark_statement_id = $check->id;
                $instrumentStatement->parent_statement_id = $request->statement_parent;
                $instrumentStatement->save();
            }
        }

        $check = BenchmarkStatement::where('statement', $request->statement)->first();
        return response()->json(['status' => true, 'message' => 'Successfully added benchmark statements!', 'statement' => $check]);
    }

//    public function showStatement($id){
//        $instrumentStatement = DB::table('instruments_statements')
//            ->leftjoin('benchmark_statements','instruments_statements.benchmark_statement_id','=', 'benchmark_statements.id')
//            ->leftjoin('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'benchmark_statements.id')
//            ->leftjoin('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
//            ->leftjoin('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
//            ->where('instruments_parameters.area_instrument_id',$id )
//            ->where('instruments_statements.area_instrument_id', $id)
//            ->select('instruments_statements.area_instrument_id', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','instruments_statements.parent_statement_id', 'parameters_statements.parameter_id', 'parameters.parameter')
//            ->orderBy('parameters.parameter')
//            ->get();
//        return response()->json($instrumentStatement);
//    }

    public function showStatement($id){
        $instrumentStatement = DB::table('instruments_statements')
            ->join('instruments_parameters', 'instruments_parameters.id', '=', 'instruments_statements.instrument_parameter_id' )
            ->join('parameters', 'parameters.id', '=' , 'instruments_parameters.parameter_id')
            ->join('benchmark_statements','instruments_statements.benchmark_statement_id','=', 'benchmark_statements.id')
            ->where('instruments_parameters.area_instrument_id',$id )
            ->select('instruments_statements.instrument_parameter_id', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','instruments_statements.parent_statement_id', 'instruments_parameters.parameter_id', 'parameters.parameter')
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

        $instruStatement = InstrumentStatement::where([
            ['instrument_parameter_id',$request->instrument_parameter_id], ['benchmark_statement_id', $request->id]
        ])->first();
        if(is_null($instruStatement)) return response()->json(['status' => true, 'message' => 'Statement in instrument does not exist']);
        $instruStatement->delete();

        if(is_null($statement)){
            $benchmarkStatement = new BenchmarkStatement();
            $benchmarkStatement->statement = $request->statement;
            $benchmarkStatement->type = $request->type;
            $benchmarkStatement->save();

            $instrumentStatement = new InstrumentStatement();
            $instrumentStatement->instrument_parameter_id = $request->instrument_parameter_id;
            $instrumentStatement->benchmark_statement_id = $benchmarkStatement->id;
            $instrumentStatement->parent_statement_id = $request->statement_parent;
            $instrumentStatement->save();

            $parents = InstrumentStatement::where('parent_statement_id', $request->id)->get();
            foreach ($parents as $parent){
                $parent->parent_statement_id = $statement->id;
                $parent->save();
            }
            return response()->json(['status' => true, 'message' => 'Updated successfully [1]']);
        }

        $instrumentStatement = new InstrumentStatement();
        $instrumentStatement->instrument_parameter_id = $request->instrument_parameter_id;
        $instrumentStatement->benchmark_statement_id = $statement->id;
        $instrumentStatement->parent_statement_id = $request->statement_parent;
        $instrumentStatement->save();

        $parents = InstrumentStatement::where('parent_statement_id', $request->id)->get();
        foreach ($parents as $parent){
            $parent->parent_statement_id = $statement->id;
            $parent->save();
        }
        return response()->json(['status' => true, 'message' => 'Updated successfully [2]']);
    }

    public function deleteStatement($instrumentID, $statementID){
        $check = InstrumentStatement::where('benchmark_statement_id', $statementID)->get();
        $checkCount = $check->count();
        if($checkCount>1) {
            $instruStatement = InstrumentStatement::where([
                ['benchmark_statement_id', $statementID], ['instrument_parameter_id', $instrumentID]
            ]);
            $instruStatement->delete();
            return response()->json(['status' => true, 'message' => 'removed statement from instrument/statement table']);
        }
        else{
            $statement = BenchmarkStatement::where('id', $statementID)->first();
            $statement->delete();
            return response()->json(['status' => true, 'message' => 'removed statement from statement table']);
        }
    }
}
