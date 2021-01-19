<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\InstrumentProgram;
use App\InstrumentScore;
use App\ParameterMean;
use App\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MSIEvaluationController extends Controller
{
    public function setScore(request $request, $id, $assignedUserId){
        $count = count($request->items);
        for ($x=0; $x<$count; $x++){
            $statement = InstrumentScore::where([
                ['item_id', $request->items[$x]['id']],['assigned_user_id', $assignedUserId]
            ])->first();
            $statement->item_score = $request->items[$x]['score'];
            $statement->remark = $request->items[$x]['remark'];
            $statement->remark_type = $request->items[$x]['remark_type'];
            $statement->save();
        }

        $parameter_mean = ParameterMean::where([
            ['program_parameter_id', $id], ['assigned_user_id', $assignedUserId]
        ])->first();
        $parameter_mean->parameter_mean = $request->parameter_mean;
        $parameter_mean->save();

        if(!(is_null($request->recommendations))){
            foreach($request->recommendations as $recommendation){
                $recom = new Recommendation();
                $recom->program_parameter_id = $id;
                $recom->assigned_user_id = $assignedUserId;
                $recom->recommendation = $recommendation;
                $recom->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully added scores', 'scores' => $request->items, 'mean' => $request->parameter_mean, 'recommendations' => $request->recommendations]);
    }

//    public function scoreComparison($id){
//        $instrument = InstrumentProgram::where('id', $id)->first();
//        $instrumentStatements = DB::table('programs_statements')
//            ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
//            ->join('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'programs_statements.benchmark_statement_id')
//            ->join('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
//            ->join('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
//            ->where('instruments_parameters.area_instrument_id',$instrument->area_instrument_id)
//            ->where('programs_statements.program_instrument_id', $instrument->id)
//            ->select('programs_statements.*', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','programs_statements.parent_statement_id', 'parameters_statements.parameter_id', 'parameters.parameter')
//            ->orderBy('parameters.parameter')
//            ->get();
//        return response()->json(['statements' => $instrumentStatements]);
//    }

}
