<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AssignedUser;
use App\AssignedUserHead;
use App\Campus;
use App\Http\Controllers\Controller;
use App\Program;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TaskForceController extends Controller
{
    public function showProgram(request $request, $id){
        $tasks = AssignedUser::where([
            ['user_id', $id], ['status', null], ['role', $request->role]
        ])->get();
        $program = array();
        $index = array();
        foreach ($tasks as $task){
            $app_prog = DB::table('applications_programs')
                ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
                ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
                ->where('applications_programs.id', $task->app_program_id)
                ->select('applications_programs.*', 'programs.program_name', 'campuses.campus_name')
                ->first();
            if(!in_array($app_prog->id,$index))
            {
                $program = Arr::prepend($program,$app_prog);
                $index = Arr::prepend($index,$app_prog->id);
            }
        }
        return response()->json(['programs'=>$program]);
    }

    public function showInstrument($id, $app_prog){
        $areas = AssignedUser::where([
            ['app_program_id', $app_prog], ['user_id', $id]
        ])->get();
        $instrument_array = array();
        foreach ($areas as $area){
            $instrument = DB::table('instruments_programs')
                ->join('programs', 'programs.id', '=', 'instruments_programs.program_id')
                ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                ->where('instruments_programs.id', $area->transaction_id)
                ->get();
            $instrument_array = Arr::prepend($instrument_array,$instrument);
        }
        return response()->json(['areas'=>$instrument_array]);
    }

    public function showCollegeCoordinator($id){
        $coordinator = new Collection();
        $tasks = AssignedUserHead::where([
            ['user_id', $id], ['status', null], ['role', 'college task force head']
        ])->get();
        foreach ($tasks as $task) {
            $applied_program = ApplicationProgram::where('id', $task->application_program_id)->first();
            $program = Program::where('id', $applied_program->program_id)->first();
            $campus = Campus::where('id', $program->campus_id)->first();
            $task_force_head = AssignedUserHead::where([
                ['application_program_id', $task->application_program_id], ['role', 'program task force chair']
            ])->first();
            if(is_null($task_force_head))
            {
                $user_id = null;
                $first_name = null;
                $last_name = null;
            }
            else{
                $user = User::where('id',$task_force_head->user_id)->first();
                $user_id = $user->id;
                $first_name = $user->first_name;
                $last_name = $user->last_name;
            }
            $coordinator->push([
                'id' =>  $applied_program->id,
                'application_id' =>  $applied_program->application_id,
                'program_id' =>  $applied_program->program_id,
                'level' =>  $applied_program->level,
                'preferred_start_date' =>  $applied_program->preferred_start_date,
                'preferred_end_date' =>  $applied_program->preferred_end_date,
                'approved_start_date' =>  $applied_program->approved_start_date,
                'approved_end_date' =>  $applied_program->approved_end_date,
                'status' =>  $applied_program->status,
                'result' =>  $applied_program->result,
                'date_granted' =>  $applied_program->date_granted,
                'certificate' =>  $applied_program->certificate,
                'created_at' =>  $applied_program->created_at,
                'updated_at' =>  $applied_program->updated_at,
                'self_survey_status' =>  $applied_program->self_survey_status,
                'program_name' =>  $program->program_name,
                'campus_name' =>  $campus->campus_name,
                'user_id' =>  $user_id,
                'first_name' =>  $first_name,
                'last_name' =>  $last_name,
                ]);
            }
        return response()->json(['tasks' => $coordinator]);
    }

    public function showInstrumentHead($id, $app_prog){
        $areas = AssignedUserhead::where([
            ['app_program_id', $app_prog], ['user_id', $id]
        ])->get();
        $instrument_array = array();
        foreach ($areas as $area){
            $instrument = DB::table('instruments_programs')
                ->join('programs', 'programs.id', '=', 'instruments_programs.program_id')
                ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                ->where('instruments_programs.id', $area->transaction_id)
                ->get();
            $instrument_array = Arr::prepend($instrument_array,$instrument);
        }
        return response()->json(['areas'=>$instrument_array]);
    }
}
