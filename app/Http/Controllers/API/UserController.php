<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AssignedUser;
use App\AssignedUserHead;
use App\Http\Controllers\Controller;
use App\ParameterMean;
use App\Program;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
                if(!in_array($app_prog->id,$index1))
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
                if(!in_array($app_prog->id,$index2))
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
                if(!in_array($app_prog->id,$index3))
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

        if($check->approved_start_date >= $date){
            return response()->json(['message'=>'Accreditation for program ' .$program->program_name.' will start on ' .$check->approved_start_date ]);
        }
        else if($check->approved_end_date < $date){
            echo $date;
            return response()->json(['message'=>'Accreditation for program ' .$program->program_name.' has been ended last ' .$check->approved_end_date ]);
        }

        $areas = AssignedUser::where([
            ['app_program_id', $app_prog], ['user_id', $id]
        ])->get();
        $instrument_array = array();
        foreach ($areas as $area){
            $instrument = DB::table('instruments_programs')
                ->join('programs', 'programs.id', '=', 'instruments_programs.program_id')
                ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                ->where('instruments_programs.id', $area->transaction_id)
                ->select('instruments_programs.*', 'programs.program_name', 'area_instruments.intended_program', 'area_instruments.area_number', 'area_instruments.area_name')
                ->first();
            $instrument_array = Arr::prepend($instrument_array,$instrument);
        }
        return response()->json(['areas'=>$instrument_array]);
    }

    public function showParameter($id){
        $parameters = DB::table('parameters')
            ->join('parameters_programs', 'parameters_programs.parameter_id','=','parameters.id')
            //->join('parameters_means', 'parameters_means.program_parameter_id', '=', 'parameters_programs.id')
            ->select('parameters_programs.*', 'parameters.parameter')
//            ->select('parameters_programs.*', 'parameters.parameter', 'parameters_means.program_parameter_id', 'parameters_means.assigned_user_id', 'parameters_means.parameter_mean')
            ->where('parameters_programs.program_instrument_id', $id)
            ->get();
        $mean_array = array();
        foreach ($parameters as $parameter){
            $means = DB::table('parameters_means')
                ->join('assigned_users', 'assigned_users.id', '=','parameters_means.assigned_user_id')
                ->join('users', 'users.id', '=', 'assigned_users.user_id')
                ->where('program_parameter_id', $parameter->id)
                ->select('parameters_means.*', 'assigned_users.user_id' ,'users.first_name','users.last_name')
                ->get();
            foreach ($means as $mean){
                $mean_array = Arr::prepend($mean_array,$mean);
            }


        }
        return response()->json(['parameters'=>$parameters, 'means' => $mean_array]);
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
