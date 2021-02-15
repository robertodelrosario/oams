<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AreaInstrument;
use App\AreaMean;
use App\AssignedUser;
use App\AssignedUserHead;
use App\BestPractice;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\ParameterMean;
use App\ParameterProgram;
use App\Program;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
//use phpDocumentor\Reflection\Types\Collection;
use Illuminate\Support\Collection;

class UserController extends Controller
{
    public function showProgram($id){
        $tasks = AssignedUser::where([
            ['user_id', $id], ['status', null]
        ])->get();
        $program_task_force = array();
        $program_internal_accreditor = array();
        $program_external_accreditor = array();
        $index1 = array();
        $index2 = array();
        $index3 = array();
        foreach ($tasks as $task){
            if($task->role == 'accreditation task force'){
                $app_prog = DB::table('applications_programs')
                    ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
                    ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
                    ->where('applications_programs.id', $task->app_program_id)
                    ->select('applications_programs.*', 'programs.program_name', 'campuses.campus_name')
                    ->first();
                if(!(in_array($app_prog->id,$index1)))
                {
                    $program_task_force = Arr::prepend($program_task_force,$app_prog);
                    $index1 = Arr::prepend($index1,$app_prog->id);
                }
            }
            else if($task->role == 'internal accreditor'){
                $app_prog = DB::table('applications_programs')
                    ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
                    ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
                    ->where('applications_programs.id', $task->app_program_id)
                    ->select('applications_programs.*', 'programs.program_name', 'campuses.campus_name')
                    ->first();
                if(!(in_array($app_prog->id,$index2)))
                {
                    $program_internal_accreditor = Arr::prepend($program_internal_accreditor,$app_prog);
                    $index2 = Arr::prepend($index2,$app_prog->id);
                }
            }
            else if(Str::contains($task->role, 'external accreditor')){
                $app_prog = DB::table('applications_programs')
                    ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
                    ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
                    ->where('applications_programs.id', $task->app_program_id)
                    ->select('applications_programs.*', 'programs.program_name', 'campuses.campus_name')
                    ->first();
                if(!(in_array($app_prog->id,$index3)))
                {
                    $program_external_accreditor = Arr::prepend($program_external_accreditor,$app_prog);
                    $index3 = Arr::prepend($index3,$app_prog->id);
                }
            }
        }
        return response()->json(['program_task_force'=>$program_task_force, 'program_internal_accreditor' => $program_internal_accreditor, 'program_external_accreditor' => $program_external_accreditor]);
    }

    public function showInstrument($id, $app_prog){
        $check = ApplicationProgram::where('id', $app_prog)->first();
        $program = Program::where('id', $check->program_id)->first();
        $date = new Carbon;

        $area = AssignedUser::where([
            ['app_program_id', $app_prog], ['user_id', $id]
        ])->first();

        if(Str::contains($area->role, 'external accreditor')){
            if ($check->approved_start_date == null || $check->approved_end_date == null){
                return response()->json(['message'=>'Accreditation for program is not yet approved']);
            }
            else if($check->approved_start_date >= $date){
                return response()->json(['message'=>'Accreditation for program ' .$program->program_name.' will start on ' .$check->approved_start_date ]);
            }
            else if($check->approved_end_date < $date){
                echo $date;
                return response()->json(['message'=>'Accreditation for program ' .$program->program_name.' has been ended last ' .$check->approved_end_date ]);
            }
        }

        $areas = AssignedUser::where([
            ['app_program_id', $app_prog], ['user_id', $id]
        ])->get();
        $instrument_array = array();
        $role = null;
        foreach ($areas as $area){
            $instrument = DB::table('instruments_programs')
                ->join('programs', 'programs.id', '=', 'instruments_programs.program_id')
                ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                ->where('instruments_programs.id', $area->transaction_id)
                ->select('instruments_programs.*', 'programs.program_name', 'area_instruments.intended_program_id', 'area_instruments.area_number', 'area_instruments.area_name')
                ->first();
            $role = $area->role;
            $instrument_array = Arr::prepend($instrument_array,$instrument);
        }

        $instruments = AssignedUser::where('app_program_id', $app_prog)->get();
        $area_mean_external = array();
        $area_mean_internal = array();

        foreach($instruments as $instrument){
            if(Str::contains($instrument->role, 'leader') || Str::contains($instrument->role, 'area 7')){
                $score = AreaMean::where([
                    ['instrument_program_id',$instrument->transaction_id], ['assigned_user_id', $instrument->id]
                ])->first();
                $area_mean_external = Arr::prepend($area_mean_external,$score);
            }
            elseif($instrument->role == 'internal accreditor'){
                $score = AreaMean::where([
                    ['instrument_program_id',$instrument->transaction_id], ['assigned_user_id', $instrument->id]
                ])->first();
                if(!(is_null($score))) $area_mean_internal = Arr::prepend($area_mean_internal,$score);
            }
        }

        $weight = array(0,8,8,8,5,4,5,3,4,5);

        $sar_external = new Collection();
        foreach ($area_mean_external as $area){
            $instrument = InstrumentProgram::where('id', $area->instrument_program_id)->first();
            $area_number = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
            for($x=0;$x < 10; $x++){
                if($area_number->area_number == $x+1){
                    $sar_external->push(['area_number' => $area_number->area_number,'area' => $area_number->area_name, 'weight' => $weight[$x], 'area_mean' => $area->area_mean, 'weighted_mean' => $area->area_mean * $weight[$x]]);
                    break;
                }
            }
        }

        $sar_internal = new Collection();
        foreach ($area_mean_internal as $area){
            $instrument = InstrumentProgram::where('id', $area->instrument_program_id)->first();
            $area_number = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
            for($x=0;$x < 10; $x++){
                if($area_number->area_number == $x+1){
                    $sar_internal->push(['area_number' => $area_number->area_number,'area' => $area_number->area_name, 'weight' => $weight[$x], 'area_mean' => $area->area_mean, 'weighted_mean' => $area->area_mean * $weight[$x]]);
                    break;
                }
            }
        }

        $result_external = new Collection();

        $total_weight = 0;
        $total_area_mean = 0;
        $total_weighted_mean = 0;
        foreach ($sar_external as $sar){
            $total_weight += $sar['weight'];
            $total_area_mean += $sar['area_mean'];
            $total_weighted_mean += $sar['weighted_mean'];
        }
        $grand_mean = 0;
        if(count($sar_external) > 0) $grand_mean  = $total_area_mean/count($sar_external);
        $result_external->push(['total_weight' => $total_weight, 'total_area_mean' => $total_area_mean, 'total_weighted_mean' => $total_weighted_mean, 'grand_mean' => $grand_mean]);

        $result_internal = new Collection();

        $total_weight = 0;
        $total_area_mean = 0;
        $total_weighted_mean = 0;
        foreach ($sar_internal as $sar){
            $total_weight += $sar['weight'];
            $total_area_mean += $sar['area_mean'];
            $total_weighted_mean += $sar['weighted_mean'];
        }

        $grand_mean = 0;
        if(count($sar_internal) > 0) $grand_mean  = $total_area_mean/count($sar_internal);
        $result_internal->push(['total_weight' => $total_weight, 'total_area_mean' => $total_area_mean, 'total_weighted_mean' => $total_weighted_mean, 'grand_mean' => $grand_mean]);

        return response()->json(['task' => $areas,'areas'=>$instrument_array,'role' =>$role, 'area_mean_external' => $sar_external, 'area_mean_internal' => $sar_internal, 'result_external' =>$result_external, 'program_mean_internal' => $result_internal]);
    }

    public function showParameter($id, $app_prog){
        $collections = new Collection();
        $collections_internal = new Collection();
        $parameters = DB::table('parameters')
            ->join('parameters_programs', 'parameters_programs.parameter_id','=','parameters.id')
            ->select('parameters_programs.*', 'parameters.parameter')
            ->where('parameters_programs.program_instrument_id', $id)
            ->get();
        $mean_array = array();
        $mean_array_internal = array();
        $best_practice = array();
        $best_practice_internal = array();
        foreach ($parameters as $parameter){
            $means = DB::table('parameters_means')
                ->join('assigned_users', 'assigned_users.id', '=','parameters_means.assigned_user_id')
                ->join('users', 'users.id', '=', 'assigned_users.user_id')
                ->where('parameters_means.program_parameter_id', $parameter->id)
                ->where('assigned_users.app_program_id', $app_prog)
                ->select('parameters_means.*', 'assigned_users.user_id','assigned_users.role' ,'users.first_name','users.last_name')
                ->get();
            foreach ($means as $mean){
                if(Str::contains($mean->role, 'external accreditor')){
                    $mean_array = Arr::prepend($mean_array,$mean);
                    $best_practices = BestPractice::where([
                        ['program_parameter_id', $mean->program_parameter_id],['assigned_user_id', $mean->assigned_user_id]
                    ])->get();
                    foreach ($best_practices as $practice) $best_practice = Arr::prepend($best_practice,$practice);
                }
                else{
                    $mean_array_internal = Arr::prepend($mean_array_internal,$mean);
                    $best_practices = BestPractice::where([
                        ['program_parameter_id', $mean->program_parameter_id],['assigned_user_id', $mean->assigned_user_id]
                    ])->get();
                    foreach ($best_practices as $practice) $best_practice_internal = Arr::prepend($best_practice_internal,$practice);
                }
            }
        }
        $total = 0;
        $total_internal = 0;
        foreach ($parameters as $parameter) {
            if($parameter->acceptable_score_gap == null) $gap = 0;
            else $gap = $parameter->acceptable_score_gap;
            $diff = 0;
            $sum = 0;
            $count = 0;
            foreach ($mean_array as $mean){
                if($mean->program_parameter_id == $parameter->id){
                    $diff = abs($diff - $mean->parameter_mean);
                    $sum = $sum + $mean->parameter_mean;
                    $count++;
                }
            }
            if($count <= 1) $diff = 0;
            if($count != 0) $average = $sum/$count;
            else $average = $sum;
            if ($diff >= $gap) {
                $collections->push(['program_parameter_id' => $parameter->id, 'average_mean' => $average, 'difference' => $diff, 'status' => 'unaccepted']);
            } else {
                $collections->push(['program_parameter_id' => $parameter->id, 'average_mean' => $average, 'difference' => $diff, 'status' => 'accepted']);
            }
            $total = $total + $average;
            if ($collections->count() != 0) $mean_ext = $total/$collections->count();
            else $mean_ext =0;

            $diff_internal = 0;
            $sum_internal = 0;
            $count = 0;
            foreach ($mean_array_internal as $mean_item){
                if($mean_item->program_parameter_id == $parameter->id){
                    $diff_internal = abs($diff_internal - $mean_item->parameter_mean);
                    $sum_internal = $sum_internal + $mean_item->parameter_mean;
                    $count++;
                }
            }
            if($count <= 1) $diff_internal = 0;
            if($count != 0 ) $average_internal = $sum_internal/$count;
            else $average_internal = $sum_internal;
            if ($diff_internal >= $gap) {
                $collections_internal->push(['program_parameter_id' => $parameter->id, 'average_mean' => $average_internal, 'difference' => $diff_internal, 'status' => 'unaccepted']);
            } else {
                $collections_internal->push(['program_parameter_id' => $parameter->id, 'average_mean' => $average_internal, 'difference' => $diff_internal, 'status' => 'accepted']);
            }
            $total_internal = $total_internal + $average_internal;
            if ($collections_internal->count() != 0) $mean_internal = $total_internal/$collections_internal->count();
            else $mean_internal =0;
        }

        $area_mean = new Collection();
        $area_mean->push(['total' => $total,'area_mean' => $mean_ext]);

        $area_mean_internal = new Collection();
        $area_mean_internal->push(['total' => $total_internal,'area_mean' => $mean_internal]);

        foreach($mean_array as $arr){
            if(Str::contains($arr->role, 'leader') || Str::contains($arr->role, 'area 7')){
                $mean = AreaMean::where([
                    ['instrument_program_id',$id], ['assigned_user_id', $arr->assigned_user_id]
                ])->first();
                if(!(is_null($mean))){
                    $mean->area_mean = $mean_ext;
                    $mean->save();
                }
            }
        }
        foreach($mean_array_internal as $arr){
            $mean = AreaMean::where([
                ['instrument_program_id',$id], ['assigned_user_id', $arr->assigned_user_id]
            ])->first();
            if(!(is_null($mean))){
                $mean->area_mean = $mean_internal;
                $mean->save();
            }
        }

        return response()->json(['parameters'=>$parameters, 'means' => $mean_array, 'result'=> $collections, 'area_mean' => $area_mean,'best_practice' => $best_practice, 'means_internal' => $mean_array_internal, 'result_internal'=> $collections_internal, 'area_mean_internal' => $area_mean_internal, 'best_practice_internal' => $best_practice_internal]);
    }

    public function showProgramHead($id){
        $tasks = AssignedUserHead::where([
            ['user_id', $id], ['status', null]
        ])->get();
        $program_task_force = array();
        $program_internal_accreditor = array();
        $program_external_accreditor = array();
        $index1 = array();
        $index2 = array();
        $index3 = array();
        foreach ($tasks as $task){
            if($task->role == 'accreditation task force head'){
                $app_prog = DB::table('applications_programs')
                    ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
                    ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
                    ->where('applications_programs.id', $task->application_program_id)
                    ->select('applications_programs.*', 'programs.program_name', 'campuses.campus_name')
                    ->first();
                if(!in_array($app_prog->id,$index1))
                {
                    $program_task_force = Arr::prepend($program_task_force,$app_prog);
                    $index1 = Arr::prepend($index1,$app_prog->id);
                }
            }
            else if($task->role == 'internal accreditor head'){
                $app_prog = DB::table('applications_programs')
                    ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
                    ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
                    ->where('applications_programs.id', $task->application_program_id)
                    ->select('applications_programs.*', 'programs.program_name', 'campuses.campus_name')
                    ->first();
                if(!in_array($app_prog->id,$index2))
                {
                    $program_internal_accreditor = Arr::prepend($program_internal_accreditor,$app_prog);
                    $index2 = Arr::prepend($index2,$app_prog->id);
                }
            }
            else if($task->role == 'external accreditor head'){
                $app_prog = DB::table('applications_programs')
                    ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
                    ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
                    ->where('applications_programs.id', $task->application_program_id)
                    ->select('applications_programs.*', 'programs.program_name', 'campuses.campus_name')
                    ->first();
                if(!in_array($app_prog->id,$index3))
                {
                    $program_external_accreditor = Arr::prepend($program_external_accreditor,$app_prog);
                    $index3 = Arr::prepend($index3,$app_prog->id);
                }
            }
        }
        return response()->json(['program_task_force_head'=>$program_task_force, 'program_internal_accreditor_head' => $program_internal_accreditor, 'program_external_accreditor_head' => $program_external_accreditor]);




//        $tasks = DB::table('assigned_user_heads')
//            ->join('applications_programs', 'applications_programs.id', '=', 'assigned_user_heads.application_program_id')
//            ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
//            ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
//            ->where('assigned_user_heads.user_id', $id)
//            ->get();
//        return response()->json(['tasks'=> $tasks]);
    }

    public function showInstrumentHead($app_prog)
    {
        $program = ApplicationProgram::where('id', $app_prog)->first();
        $instrument = DB::table('instruments_programs')
            ->join('programs', 'programs.id', '=', 'instruments_programs.program_id')
            ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
            ->where('instruments_programs.program_id', $program->program_id)
            ->select('instruments_programs.*', 'programs.program_name', 'area_instruments.intended_program', 'area_instruments.area_number', 'area_instruments.area_name')
            ->get();
        return response()->json(['areas' => $instrument]);
    }
}
