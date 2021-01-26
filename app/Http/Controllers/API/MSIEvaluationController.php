<?php

namespace App\Http\Controllers\API;

use App\BestPractice;
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

        if(!(is_null($request->best_practices))){
            foreach($request->best_practices as $best_practice){
                $practice = new BestPractice();
                $practice->program_parameter_id = $id;
                $practice->assigned_user_id = $assignedUserId;
                $practice->best_practice = $best_practice;
                $practice->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully added scores', 'scores' => $request->items, 'mean' => $request->parameter_mean, 'best_practices' => $request->best_practices]);
    }

    public function showBestPractice($id, $assignedUserId){
        $bestPractices = BestPractice::where([
            ['program_parameter_id',$id], ['assigned_user_id', $assignedUserId]
        ])->get();
        return response()->json($bestPractices);
    }

    public function deleteBestPractice($id){
        $bestPractice = BestPractice::where('id', $id);
        $bestPractice->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted practice']);
    }

    public function editBestPractice(request $request, $id){
        $bestPractice = BestPractice::where('id', $id)->first();
        $bestPractice->best_practice = $request->best_practice;
        $bestPractice->save();
        return response()->json(['status' => true, 'message' => 'Successfully deleted practice']);
    }

}
