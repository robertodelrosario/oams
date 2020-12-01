<?php

namespace App\Http\Controllers\API;

use App\AccreditorRequest;
use App\ApplicationProgram;
use App\Http\Controllers\Controller;
use App\Mail\ApplicationNotification;
use App\Mail\RequestAccreditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AaccupController extends Controller
{
    public function showAllPrograms(){
        $programs = DB::table('programs')
            ->join('sucs', 'sucs.id', '=', 'programs.suc_id')
            ->get();
        return response()->json(['programs' => $programs]);
    }

    public function showApplication(){
        $applications = DB::table('applications')
            ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
            ->get();
        return response()->json(['applications' => $applications]);
    }

    public function requestAccreditor(request $request,$id){
        $accreditorRequest =new AccreditorRequest();
        $accreditorRequest->application_program_id = $request->application_program_id;
        $accreditorRequest->accreditor_id = $id;
        $accreditorRequest->instrument_program_id = $request->instrument_program_id;
        $accreditorRequest->status = "pending";
        $accreditorRequest->save();

        $req = DB::table('accreditor_requests')
            ->join('applications_programs', 'applications_programs.id', '=', 'accreditor_requests.application_program_id')
            ->join('applications', 'applications.id', '=', 'applications_programs.application_id')
            ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
            ->join('programs', 'programs.id', '=', 'applications_programs.program_id')
            ->join('instruments_programs', 'instruments_programs.id', '=', 'accreditor_requests.instrument_program_id')
            ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
            ->where('accreditor_requests.accreditor_id', $id)
            ->where('accreditor_requests.status', '=', 'pending')
            ->select( 'accreditor_requests.id','sucs.institution_name' ,'programs.program_name','area_instruments.area_name','area_instruments.area_number', 'applications_programs.approved_start_date', 'applications_programs.approved_end_date')
            ->first();

        $details = [
            'title' => 'Request for Accreditation',
            'suc' => $req->institution_name,
            'program' => $req->program_name,
            'area_number' => $req->area_number,
            'area_name' => $req->area_name,
            'start_date' => $req->approved_start_date,
            'start_end' => $req->approved_end_date,
            'link' =>'http://online_accreditation_management_system.test/api/v1/aaccup/showApplication'
        ];
        \Mail::to('roberto.delrosario@ustp.edu.ph')->send(new RequestAccreditor($details));
        return response()->json(['status' => true, 'message' => 'Successfully sent accreditor request ','accreditor' => $accreditorRequest]);
    }

    public function viewAccreditorRequest(){
        $req = DB::table('accreditor_requests')
            ->join('applications_programs', 'applications_programs.id', '=', 'accreditor_requests.application_program_id')
            ->join('programs', 'programs.id', '=', 'applications_programs.program_id')
            ->join('instruments_programs', 'instruments_programs.id', '=', 'accreditor_requests.instrument_program_id')
            ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
            ->join('users', 'users.id', '=', 'accreditor_requests.accreditor_id')
            ->select('users.first_name', 'users.last_name', 'users.email','accreditor_requests.*', 'programs.program_name','area_instruments.area_name','area_instruments.area_number', 'applications_programs.approved_start_date', 'applications_programs.approved_end_date')
            ->get();
        return response()->json(['requests' => $req]);
    }

    public function deleteAccreditorRequest($id){
        $req = AccreditorRequest::where('id', $id);
        $req->delete();
        return response()->json(['status' => true ,'message' => 'Successfully deleted request']);
    }
    public function setDate(request $request, $id){
        $program = ApplicationProgram::where('id', $id)->first();
        $program->approved_start_date = $request->approved_start_date;
        $program->approved_end_date = $request->approved_end_date;
        $program->status = "approved";
        $program->save();
        return response()->json(['status' => true, 'message' => 'Successfully approved program application']);
    }
    public function reject( $id){
        $program = ApplicationProgram::where('id', $id)->first();
        $program->status = "rejected";
        $program->save();
        return response()->json(['status' => true, 'message' => 'Rejected program application']);
    }
}
