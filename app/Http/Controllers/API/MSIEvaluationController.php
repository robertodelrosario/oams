<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AreaMean;
use App\AssignedUser;
use App\BestPractice;
use App\GraduatePerformance;
use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\InstrumentProgram;
use App\InstrumentScore;
use App\ParameterMean;
use App\ParameterProgram;
use App\Program;
use App\Recommendation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

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
        if($request->parameter_mean != null) $parameter_mean->parameter_mean = $request->parameter_mean;
        else $parameter_mean->parameter_mean = 0;
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

        $parameter = ParameterProgram::where('id', $id)->first();
        $program_parameters = ParameterProgram::where('program_instrument_id', $parameter->program_instrument_id)->get();
        $score = 0;
        $count = 0;
        foreach ($program_parameters as $program_parameter){
            $para_mean = ParameterMean::where([
                ['program_parameter_id', $program_parameter->id], ['assigned_user_id', $assignedUserId]
            ])->first();
            if(!(is_null($para_mean))){
                $score = $score + $parameter_mean->parameter_mean;
                $count++;
            }
        }
        $mean = AreaMean::where([
            ['instrument_program_id',$parameter->program_instrument_id], ['assigned_user_id', $assignedUserId]
        ])->first();
        if(!(is_null($mean))){
            $mean->area_mean = $score;
            $mean->save();
        }
        return response()->json(['status' => true, 'message' => 'Successfully added scores', 'scores' => $request->items, 'mean' => $request->parameter_mean, 'best_practices' => $request->best_practices]);

    }

    public function showBestPractice($id, $assignedUserId){
        $bestPractices = BestPractice::where([
            ['program_parameter_id',$id], ['assigned_user_id', $assignedUserId]
        ])->get();
        return response()->json($bestPractices);
    }

    public function showSFRData($id, $role){
        $assignedUsers = AssignedUser::where('app_program_id', $id)->get();
        if($role == 0) $role_str = 'internal accreditor';
        elseif($role == 1) $role_str = 'external accreditor';
        $transactions = array();
        foreach ($assignedUsers as $assignedUser){
            $tran = DB::table('area_instruments')
                ->join('instruments_programs', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                ->where('instruments_programs.id', $assignedUser->transaction_id)
                ->first();
            if(!(in_array($tran, $transactions))) $transactions= Arr::prepend($transactions,$tran);
        }
        $program = "";
        $bestpractice_array = array();
        $remark_strength_array = array();
        $remark_weakness_array = array();
        $recommendation_array = array();
        $empty = array();

        $before_compliance = new Collection();
        $after_compliance = new Collection();

        $collection_user = new Collection();
        $test = array();
        $applied_program = ApplicationProgram::where('id', $id)->first();
        foreach ($transactions as $transaction){
            $tasks = AssignedUser::where([
                ['transaction_id', $transaction->id], ['app_program_id', $id]
            ])->get();

            $area = DB::table('area_instruments')
                ->join('instruments_programs', 'area_instruments.id','=', 'instruments_programs.area_instrument_id')
                ->where('instruments_programs.id',$transaction->id)
                ->first();

            $program = Program::where('id', $area->program_id)->first();
            foreach ($tasks as $task){
                if(Str::contains($task->role, $role_str))
                {
                    $user = User::where('id', $task->user_id)->first();
                    if(Str::contains($applied_program->level, 'Level III') || Str::contains($applied_program->level, 'Level IV')){
                        $remarks = InstrumentScore::where('assigned_user_id', $task->id)->get();
                        foreach ($remarks as $remark){
                            if(!(is_null($remark->remark))){
                                $before_compliance->push($remark->remark);
                            }
                            if(!(is_null($remark->remark_2))){
                                $after_compliance->push($remark->remark_2);
                            }
                        }
                        $collection_user->push([
                            'instrument_program_id' => $area->id,
                            'area_name' => $area->area_name,
                            'user_name' => $user->first_name. " ".$user->last_name,
                            'best_practices' => $bestpractice_array,
                            'strength_remarks' => $remark_strength_array,
                            'weakness_remarks' => $remark_weakness_array,
                            'recommendations' => $recommendation_array,
                            'before_compliance' => $before_compliance,
                            'after_compliance' => $after_compliance,
                        ]);
                    }
                    else{
                        $test= Arr::prepend($test,$task);
                        $user = User::where('id', $task->user_id)->first();
                        $bestpractices = DB::table('assigned_users')
                            ->join('best_practices', 'best_practices.assigned_user_id','=', 'assigned_users.id')
                            ->where('assigned_users.id', $task->id)
                            ->get();
                        foreach ($bestpractices as $bestpractice) $bestpractice_array = Arr::prepend($bestpractice_array,$bestpractice->best_practice);

                        $remarks = DB::table('assigned_users')
                            ->join('instruments_scores', 'instruments_scores.assigned_user_id', '=', 'assigned_users.id')
                            ->where('assigned_users.id', $task->id)
                            ->where('instruments_scores.remark', '!=', null)
                            ->get();
                        if(count($remarks) > 0) {
                            foreach ($remarks as $remark) {
                                if ($remark->remark_type == 'Strength') $remark_strength_array = Arr::prepend($remark_strength_array, $remark->remark);
                                elseif ($remark->remark_type == 'Weakness') $remark_weakness_array = Arr::prepend($remark_weakness_array, $remark->remark);
                            }
                        }

                        $recommendations = DB::table('assigned_users')
                            ->join('recommendations', 'assigned_users.id', '=', 'recommendations.assigned_user_id')
                            ->where('assigned_users.id', $task->id)
                            ->get();
                        foreach ($recommendations as $recommendation) $recommendation_array = Arr::prepend($recommendation_array,$recommendation->recommendation);

                        $collection_user->push([
                            'instrument_program_id' => $area->id,
                            'area_name' => $area->area_name,
                            'user_name' => $user->first_name. " ".$user->last_name,
                            'best_practices' => $bestpractice_array,
                            'strength_remarks' => $remark_strength_array,
                            'weakness_remarks' => $remark_weakness_array,
                            'recommendations' => $recommendation_array,
                            'before_compliance' => null,
                            'after_compliance' => null,
                        ]);
                        $bestpractice_array = $empty;
                        $remark_strength_array = $empty;
                        $remark_weakness_array= $empty;
                        $recommendation_array= $empty;
                    }
                }
            }
        }
        return response()->json(['program' => $program, 'instrument_programs' => $transactions, 'collection' => $collection_user]);
    }

    public function showPersonalSFRData($id, $role){
        $assignedUsers = AssignedUser::where([
            ['app_program_id', $id], ['user_id', auth()->user()->id]
        ])->get();
        if($role == 0) $role_str = 'internal accreditor';
        elseif($role == 1) $role_str = 'external accreditor';

        $bestpractice_array = array();
        $remark_strength_array = array();
        $remark_weakness_array = array();
        $recommendation_array = array();
        $empty = array();

        $transactions = array();

        $before_compliance = new Collection();
        $after_compliance = new Collection();
        $collection_user = new Collection();
        $applied_program = ApplicationProgram::where('id', $id)->first();
        $program = Program::where('id', $applied_program->program_id)->first();
        foreach ($assignedUsers as $assignedUser){
            $area = DB::table('area_instruments')
                ->join('instruments_programs', 'area_instruments.id','=', 'instruments_programs.area_instrument_id')
                ->where('instruments_programs.id',$assignedUser->transaction_id)
                ->first();
            $transactions= Arr::prepend($transactions,$area);
            if(Str::contains($assignedUser->role, $role_str))
            {
                $user = User::where('id', $assignedUser->user_id)->first();
                if(Str::contains($applied_program->level, 'Level III') || Str::contains($applied_program->level, 'Level IV')){
                    $remarks = InstrumentScore::where('assigned_user_id', $assignedUser->id)->get();
                    foreach ($remarks as $remark){
                        if(!(is_null($remark->remark))){
                            $before_compliance->push($remark->remark);
                        }
                        if(!(is_null($remark->remark_2))){
                            $after_compliance->push($remark->remark_2);
                        }
                    }
                    $collection_user->push([
                        'instrument_program_id' => $area->id,
                        'area_name' => $area->area_name,
                        'user_name' => $user->first_name. " ".$user->last_name,
                        'best_practices' => $bestpractice_array,
                        'strength_remarks' => $remark_strength_array,
                        'weakness_remarks' => $remark_weakness_array,
                        'recommendations' => $recommendation_array,
                        'before_compliance' => $before_compliance,
                        'after_compliance' => $after_compliance,
                    ]);
                }
                else{
                    $user = User::where('id', $assignedUser->user_id)->first();
                    $bestpractices = DB::table('assigned_users')
                        ->join('best_practices', 'best_practices.assigned_user_id','=', 'assigned_users.id')
                        ->where('assigned_users.id', $assignedUser->id)
                        ->get();
                    foreach ($bestpractices as $bestpractice) $bestpractice_array = Arr::prepend($bestpractice_array,$bestpractice->best_practice);

                    $remarks = DB::table('assigned_users')
                        ->join('instruments_scores', 'instruments_scores.assigned_user_id', '=', 'assigned_users.id')
                        ->where('assigned_users.id', $assignedUser->id)
                        ->where('instruments_scores.remark', '!=', null)
                        ->get();
                    if(count($remarks) > 0) {
                        foreach ($remarks as $remark) {
                            if ($remark->remark_type == 'Strength') $remark_strength_array = Arr::prepend($remark_strength_array, $remark->remark);
                            elseif ($remark->remark_type == 'Weakness') $remark_weakness_array = Arr::prepend($remark_weakness_array, $remark->remark);
                        }
                    }

                    $recommendations = DB::table('assigned_users')
                        ->join('recommendations', 'assigned_users.id', '=', 'recommendations.assigned_user_id')
                        ->where('assigned_users.id', $assignedUser->id)
                        ->get();
                    foreach ($recommendations as $recommendation) $recommendation_array = Arr::prepend($recommendation_array,$recommendation->recommendation);

                    $collection_user->push([
                        'instrument_program_id' => $area->id,
                        'area_name' => $area->area_name,
                        'user_name' => $user->first_name. " ".$user->last_name,
                        'best_practices' => $bestpractice_array,
                        'strength_remarks' => $remark_strength_array,
                        'weakness_remarks' => $remark_weakness_array,
                        'recommendations' => $recommendation_array,
                        'before_compliance' => null,
                        'after_compliance' => null,
                    ]);
                    $bestpractice_array = $empty;
                    $remark_strength_array = $empty;
                    $remark_weakness_array= $empty;
                    $recommendation_array= $empty;
                }
            }
        }
        return response()->json(['program' => $program, 'instrument_programs' => $transactions, 'collection' => $collection_user]);
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

    public function saveAreaScore(request $request, $id, $assigned_user_id){
        $area_mean = AreaMean::where([
            ['assigned_user_id', $assigned_user_id],['instrument_program_id', $id]
            ])->first();
//        foreach ($area_mean as $item) $item->delete();
        if(is_null($area_mean)){
            $area_mean = new AreaMean();
            $area_mean->assigned_user_id = $assigned_user_id;
            $area_mean->instrument_program_id = $id;
            $area_mean->area_mean = $request->score;
            $area_mean->save();
        }
        else{
            $area_mean->area_mean = $request->score;
            $area_mean->save();
        }

        $remarks = $request->remarks;
        foreach ($remarks as $remark){
            $statement = InstrumentScore::where([
                ['item_id', $remark['id']],
                ['assigned_user_id', $assigned_user_id]
            ])->first();
            $statement->remark = $remark['remark'];
            $statement->remark_type = $remark['remark_type'];
            $statement->remark_2 = $remark['remark_2'];
            $statement->remark_2_type = $remark['remark_2_type'];
            $statement->save();
        }

        if(count($request->graduate_performances) != 0){
            $graduate_performances = $request->graduate_performances;
            foreach ($graduate_performances as $graduate_performance){
                $grad_perf = GraduatePerformance::where('program_statement_id', $graduate_performance['id'])->get();
                foreach($grad_perf as $gp) $gp->delete();
                $performance = new GraduatePerformance();
                $performance->program_statement_id =  $graduate_performance['id'];
                $performance->year = $graduate_performance['year_1'];
                $performance->rating = $graduate_performance['rating_1'];
                $performance->save();

                $performance2 = new GraduatePerformance();
                $performance2->program_statement_id =  $graduate_performance['id'];
                $performance2->year = $graduate_performance['year_2'];
                $performance2->rating = $graduate_performance['rating_2'];
                $performance2->save();

                $performance3 = new GraduatePerformance();
                $performance3->program_statement_id =  $graduate_performance['id'];
                $performance3->year = $graduate_performance['year_3'];
                $performance3->rating = $graduate_performance['rating_3'];
                $performance3->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully saved.']);
    }

    public function deleteAreaMean($id){
        $area_mean = AreaMean::where('id', $id)->first();
        $area_mean->delete();
    }

    public function showMeanScore($id){
        $assigned_users = AssignedUser::where('app_program_id', $id)->get();
        foreach ($assigned_users as $assigned_user){
            $score = AreaMean::where('assigned_user_id', $assigned_user->id)->get();
            echo $score;
        }
    }
}
