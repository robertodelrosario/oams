<?php

namespace App\Http\Controllers\API;

use App\AssignedUser;
use App\BestPractice;
use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\InstrumentProgram;
use App\InstrumentScore;
use App\ParameterMean;
use App\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function showSFRData($id){
        $users = AssignedUser::where('app_program_id', $id)->get();
        $bestpractice_array_external = array();
        $remark_array_external = array();
        $recommendation_array_external = array();
        $bestpractice_array_internal = array();
        $remark_array_internal = array();
        $recommendation_array_internal = array();
        foreach ($users as $user){
            if(Str::contains($user->role, 'external accreditor'))
            {
                $bestpractices = DB::table('assigned_users')
                    ->join('users', 'users.id', '=', 'assigned_users.user_id')
                    ->join('best_practices', 'best_practices.assigned_user_id','=', 'assigned_users.id')
                    ->where('assigned_users.id', $user->id)
                    ->get();
                foreach ($bestpractices as $bestpractice) $bestpractice_array_external = Arr::prepend($bestpractice_array_external,$bestpractice);

                $remarks = DB::table('assigned_users')
                    ->join('users', 'users.id', '=', 'assigned_users.user_id')
                    ->join('instruments_scores', 'instruments_scores.assigned_user_id', '=', 'assigned_users.id')
                    ->where('assigned_users.id', $user->id)
                    ->where('instruments_scores.remark', '!=', null)
                    ->get();
                foreach ($remarks as $remark) $remark_array_external = Arr::prepend($remark_array_external, $remark);

                $recommendations = DB::table('assigned_users')
                    ->join('users', 'users.id', '=', 'assigned_users.user_id')
                    ->join('recommendations', 'assigned_users.id', '=', 'recommendations.assigned_user_id')
                    ->where('assigned_users.id', $user->id)
                    ->get();
                foreach ($recommendations as $recommendation) $recommendation_array_external = Arr::prepend($recommendation_array_external,$recommendation);
            }
            elseif($user->role == 'internal accreditor'){
                $bestpractices = DB::table('assigned_users')
                    ->join('users', 'users.id', '=', 'assigned_users.user_id')
                    ->join('best_practices', 'best_practices.assigned_user_id','=', 'assigned_users.id')
                    ->where('assigned_users.id', $user->id)
                    ->get();
                foreach ($bestpractices as $bestpractice) $bestpractice_array_internal = Arr::prepend($bestpractice_array_internal,$bestpractice);

                $remarks = DB::table('assigned_users')
                    ->join('users', 'users.id', '=', 'assigned_users.user_id')
                    ->join('instruments_scores', 'instruments_scores.assigned_user_id', '=', 'assigned_users.id')
                    ->where('assigned_users.id', $user->id)
                    ->where('instruments_scores.remark', '!=', null)
                    ->get();
                foreach ($remarks as $remark) $remark_array_internal = Arr::prepend($remark_array_internal, $remark);

                $recommendations = DB::table('assigned_users')
                    ->join('users', 'users.id', '=', 'assigned_users.user_id')
                    ->join('recommendations', 'assigned_users.id', '=', 'recommendations.assigned_user_id')
                    ->where('assigned_users.id', $user->id)
                    ->get();
                foreach ($recommendations as $recommendation) $recommendation_array_internal = Arr::prepend($recommendation_array_internal,$recommendation);
            }
        }
        return response()->json(['bestpractice_external' => $bestpractice_array_external, 'bestpractice_internal' => $bestpractice_array_internal, 'remark_external' => $remark_array_external, 'remark_internal' => $remark_array_internal, 'recommendation_internal' => $recommendation_array_internal, 'recommendation_external' => $recommendation_array_external]);
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

    public function saveRecommendation(request $request, $id){
        foreach($request->recommendations as $recommendation){
            $recom = new Recommendation();
            $recom->recommendation = $recommendation;
            $recom->assigned_user_id = $id;
            $recom->save();
        }
        return response()->json(['status' => true, 'message' => 'Successfully added recommendations.']);
    }

    public function editRecommendation(request $request,$id){
        $recommendation = Recommendation::where('id', $id)->first();
        $recommendation->recommendation = $request->recommendation;
        $recommendation->save();
        return response()->json(['status' => true, 'message' => 'Successfully edited recommendation.']);

    }

    public function showRecommendation($id){
        $recommendation = Recommendation::where('assigned_user_id', $id)->get();
        return response()->json($recommendation);
    }

    public function deleteRecommendation($id){
        $recommendation = Recommendation::where('id', $id)->first();
        $recommendation->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted recommendation.']);
    }

}
