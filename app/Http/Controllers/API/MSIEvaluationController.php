<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\InstrumentScore;
use Illuminate\Http\Request;

class MSIEvaluationController extends Controller
{
    public function setScore(request $request){
        $count = count($request->items);
        for ($x=1; $x<$count; $x++){
            $statement = InstrumentScore::where([
                ['item_id', $request->items[$x]['id']],['assigned_user_id', $request->items[$x]['user_id']]
            ])->first();
            $statement->item_score = $request->items[$x]['score'];
            $statement->remark = $request->items[$x]['remark'];
            $statement->remark_type = $request->items[$x]['remark_type'];
            $statement->save();
        }
        return response()->json(['status' => true, 'message' => 'Successfully added scores']);
    }

}
