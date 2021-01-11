<?php

namespace App\Http\Controllers\API;

use App\AccreditorRequest;
use App\ApplicationProgram;
use App\AssignedUser;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentScore;
use App\ProgramStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;

class AccreditorController extends Controller
{
    public function viewRequest($id){
        $req = DB::table('accreditor_requests')
            ->join('applications_programs', 'applications_programs.id', '=', 'accreditor_requests.application_program_id')
            ->join('applications', 'applications.id', '=', 'applications_programs.application_id')
            ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
            ->join('programs', 'programs.id', '=', 'applications_programs.program_id')
            ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
            ->where('accreditor_requests.accreditor_id', $id)
            ->select( 'accreditor_requests.id','sucs.institution_name' ,'campuses.campus_name', 'programs.program_name', 'applications_programs.approved_start_date', 'applications_programs.approved_end_date', 'accreditor_requests.status', 'accreditor_requests.role')
            ->get();
        return response()->json(['requests' => $req]);
    }
    public function acceptRequest($id){
        $req = AccreditorRequest::where('id', $id)->first();
        $application_program = ApplicationProgram::where('id', $req->application_program_id)->first();
        $req->status = 'accepted';
        $req->save();

        if($req->role == "[leader] external accreditor - area 7" || $req->role == "external accreditor - area 7")
        {
            $area = DB::table('instruments_programs')
                ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                ->where([
                    ['instruments_programs.program_id', $application_program->program_id], ['area_instruments.area_number', 7]
                ])
                ->select('instruments_programs.*')
                ->first();
            $assignUser = new AssignedUser();
            $assignUser->transaction_id = $area->id;
            $assignUser->user_id = $req->accreditor_id;
            $assignUser->app_program_id = $req->application_program_id;
            $assignUser->role = $req->role;
            $assignUser->save();

            $statements = ProgramStatement::where('program_instrument_id', $area->id)->get();
            foreach ($statements as $statement){
                $item = new InstrumentScore();
                $item->item_id = $statement->id;
                $item->assigned_user_id = $req->accreditor_id;
                $item->save();
            }
        }
        else if($req->role == "[leader] external accreditor" || $req->role == "external accreditor"){
            $areas = DB::table('instruments_programs')
                ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                ->where([
                    ['instruments_programs.program_id', $application_program->program_id], ['area_instruments.area_number','!=', 7]
                ])
                ->select('instruments_programs.*')
                ->get();
            foreach ($areas as $area){
                $assignUser = new AssignedUser();
                $assignUser->transaction_id = $area->id;
                $assignUser->user_id = $req->accreditor_id;
                $assignUser->app_program_id = $req->application_program_id;
                $assignUser->role = $req->role;
                $assignUser->save();

                $statements = ProgramStatement::where('program_instrument_id', $area->id)->get();
                foreach ($statements as $statement){
                    $item = new InstrumentScore();
                    $item->item_id = $statement->id;
                    $item->assigned_user_id = $req->accreditor_id;
                    $item->save();
                }
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully accepted request']);
    }

    public function rejectRequest(request $request, $id){
        $req = AccreditorRequest::where('id', $id)->first();
        $req->status = 'rejected';
        $req->remark = $request->remark;
        $req->save();
        return response()->json(['status' => true, 'message' => 'Successfully rejected request']);
    }

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
                ->join('sucs', 'sucs.id', '=', 'campuses.suc_id')
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
                ->select('instruments_programs.*', 'area_instruments.area_number', 'area_instruments.area_name')
                ->where('instruments_programs.id', $area->transaction_id)
                ->first();
            $instrument_array = Arr::prepend($instrument_array,$instrument);
        }
        return response()->json(['areas'=>$instrument_array]);
    }
    public function showProgramHead(request $request,$id){
        $tasks = AssignedUserHead::where([
            ['user_id', $id], ['status', null], ['role', $request->role]
        ])->get();
        $program = array();
        $index = array();
        foreach ($tasks as $task){
            $app_prog = DB::table('applications_programs')
                ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
                ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
                ->where('applications_programs.id', $task->application_program_id)
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

    public function saveParameterMean($id){
        $instrument = InstrumentProgram::where('id', $id)->first();

    }
}
