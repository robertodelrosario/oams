<?php

namespace App\Http\Controllers\API;

use App\AssignedUser;
use App\Http\Controllers\Controller;
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

    public function showProgramHead(request $request, $id){
        $tasks = AssignedUserHead::where([
            ['user_id', $id], ['status', null], ['role', $request->role]
        ])->get();

        $coordinator = new Collection();
        $program = array();
        $index = array();
        if($request->role == 'accreditation task force head') {
            foreach ($tasks as $task) {
                $app_prog = DB::table('applications_programs')
                    ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
                    ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
                    ->where('applications_programs.id', $task->application_program_id)
                    ->select('applications_programs.*', 'programs.program_name', 'campuses.campus_name')
                    ->first();
                if (!in_array($app_prog->id, $index)) {
                    $program = Arr::prepend($program, $app_prog);
                    $index = Arr::prepend($index, $app_prog->id);
                }
            }
        }
        else{
            foreach ($tasks as $task) {

            }
        }
        return response()->json(['programs'=>$program, 'coordinator' => $coordinator]);
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
