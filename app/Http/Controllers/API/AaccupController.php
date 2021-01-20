<?php

namespace App\Http\Controllers\API;

use App\AccreditorRequest;
use App\Application;
use App\ApplicationFile;
use App\ApplicationProgram;
use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\Mail\ApplicationNotification;
use App\Mail\RequestAccreditor;
use App\Notification;
use App\NotificationContent;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AaccupController extends Controller
{
    public function showAllProgram(){
        $programs = DB::table('programs')
            ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
            ->join('sucs', 'sucs.id', '=', 'campuses.suc_id')
            ->get();
        return response()->json(['programs' => $programs]);
    }

    public function showApplication(){
        $applications = DB::table('applications')
            ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
            ->join('users', 'users.id', '=','applications.sender_id')
            ->where('applications.status','!=' ,'under preparation')
            ->select('applications.*', 'sucs.institution_name','sucs.address', 'sucs.email','sucs.contact_no', 'users.first_name', 'users.last_name')
            ->get();
        $file_arr = array();
        foreach ($applications as $application){
            $files = ApplicationFile::where('application_id',$application->id)->get();
            foreach ($files as $file){
                $file_arr = Arr::prepend($file_arr,$file);
            }
        }
        return response()->json(['applications' => $applications, 'files' => $file_arr]);
    }

    public function showProgram($id){
        $programs = DB::table('applications_programs')
            ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
            ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
            ->where('applications_programs.application_id', $id)
            ->select('applications_programs.*', 'programs.program_name', 'campuses.campus_name')
            ->get();
        $users = array();
        foreach ($programs as $program){
            $request_users = DB::table('users')
                ->join('accreditor_requests', 'users.id', '=', 'accreditor_requests.accreditor_id')
                ->where('accreditor_requests.application_program_id', $program->id)
                ->get();
            foreach ($request_users as $user)
                if ($user != null) $users = Arr::prepend($users, $user);
        }

        return response()->json(['programs' =>$programs, 'users' => $users]);
    }

    public function request(request $request,$userID,$id){
        $count = count($request->taskRequests);
        for($x=0; $x<$count; $x++) {
            $accreditorRequest =new AccreditorRequest();
            $accreditorRequest->application_program_id = $id;
            $accreditorRequest->accreditor_id = $request->taskRequests[$x]['user_id'];
            if($x == 0 && $request->taskRequests[$x]['type'] == 0) $accreditorRequest->role = '[leader] external accreditor - area 7';
            else if($x == 0 && $request->taskRequests[$x]['type'] == 1) $accreditorRequest->role = '[leader] external accreditor';
            else if($x > 0 && $request->taskRequests[$x]['type'] == 0) $accreditorRequest->role = 'external accreditor - area 7';
            else if($x > 0 && $request->taskRequests[$x]['type'] == 1) $accreditorRequest->role = 'external accreditor';
            $accreditorRequest->sender_id = $userID;
            $accreditorRequest->status = "pending";
            $accreditorRequest->save();

            $req = DB::table('accreditor_requests')
                ->join('applications_programs', 'applications_programs.id', '=', 'accreditor_requests.application_program_id')
                ->join('applications', 'applications.id', '=', 'applications_programs.application_id')
                ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
                ->join('programs', 'programs.id', '=', 'applications_programs.program_id')
                ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
                ->where('accreditor_requests.id', $accreditorRequest->id)
                ->where('accreditor_requests.status', '=', 'pending')
                ->select( 'accreditor_requests.id','sucs.institution_name','campuses.campus_name','programs.program_name', 'applications_programs.approved_start_date', 'applications_programs.approved_end_date')
                ->first();

            $title = 'Request for Accreditation - '.$accreditorRequest->role;
            $details = [
                'title' => $title,
                'suc' => $req->institution_name,
                'campus' => $req->campus_name,
                'program' => $req->program_name,
                'start_date' => $req->approved_start_date,
                'start_end' => $req->approved_end_date,
                'link' =>'http://online_accreditation_management_system.test/api/v1/auth/login'
            ];
            \Mail::to('roberto.delrosario@ustp.edu.ph')->send(new RequestAccreditor($details));
        }
        return response()->json(['status' => true, 'message' => 'Successfully sent accreditor requests']);
    }

    public function addRequest(request $request,$userID,$id){
        $count = count($request->taskRequests);
        for($x=0; $x<$count; $x++) {
            $accreditorRequest =new AccreditorRequest();
            $accreditorRequest->application_program_id = $id;
            $accreditorRequest->accreditor_id = $request->taskRequests[$x]['user_id'];
            if($request->taskRequests[$x]['type'] == 0) $accreditorRequest->role = 'external accreditor - area 7';
            else if($request->taskRequests[$x]['type'] == 1) $accreditorRequest->role = 'external accreditor';
            $accreditorRequest->sender_id = $userID;
            $accreditorRequest->status = "pending";
            $accreditorRequest->save();

            $req = DB::table('accreditor_requests')
                ->join('applications_programs', 'applications_programs.id', '=', 'accreditor_requests.application_program_id')
                ->join('applications', 'applications.id', '=', 'applications_programs.application_id')
                ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
                ->join('programs', 'programs.id', '=', 'applications_programs.program_id')
                ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
                ->where('accreditor_requests.id', $accreditorRequest->id)
                ->where('accreditor_requests.status', '=', 'pending')
                ->select( 'accreditor_requests.id','sucs.institution_name','campuses.campus_name','programs.program_name', 'applications_programs.approved_start_date', 'applications_programs.approved_end_date')
                ->first();

            $title = 'Request for Accreditation - '.$accreditorRequest->role;
            $details = [
                'title' => $title,
                'suc' => $req->institution_name,
                'campus' => $req->campus_name,
                'program' => $req->program_name,
                'start_date' => $req->approved_start_date,
                'start_end' => $req->approved_end_date,
                'link' =>'http://online_accreditation_management_system.test/api/v1/auth/login'
            ];
            \Mail::to('roberto.delrosario@ustp.edu.ph')->send(new RequestAccreditor($details));
        }
        return response()->json(['status' => true, 'message' => 'Successfully sent accreditor requests']);

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

    public function editRequest(request $request, $id){
        $accreditorRequest = AccreditorRequest::where('id', $id)->first();
        if($accreditorRequest->status == 'pending'){
            if(Str::contains($accreditorRequest->role, 'leader')){
                if($request->type == 0) $accreditorRequest->role = '[leader] external accreditor - area 7';
                else if($request->type == 1) $accreditorRequest->role = '[leader] external accreditor';
            }
            else{
                if($request->type == 0) $accreditorRequest->role = 'external accreditor - area 7';
                else if($request->type == 1) $accreditorRequest->role = 'external accreditor';
            }
            $accreditorRequest->save();
            return response()->json(['status' => true ,'message' => 'Successfully edited request']);
        }
        else return response()->json(['status' => false ,'message' => 'Request already is already '.$accreditorRequest->status]);
    }

    public function approve(request $request, $id){
        $program = ApplicationProgram::where('id', $id)->first();
        $program->approved_start_date = $request->approved_start_date;
        $program->approved_end_date = $request->approved_end_date;
        $program->status = "approved";
        $program->save();

        $application = Application::where('id', $program->application_id)->first();
        $application->status = 'on going';
        $application->save();
        return response()->json(['status' => true, 'message' => 'Successfully approved program application', 'program' => $program]);
    }

    public function declineSchedule(request $request, $id, $userID){
        $validator = Validator::make($request->all(), [
            'message' => 'required'
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Message Letter!']);

        $program = ApplicationProgram::where('id', $id)->first();
        $application = Application::where('id', $program->application_id)->first();

        $content = new NotificationContent();
        $content->content = $request->message;
        $content->notif_type = 'decline schedule';
        $content->save();

        $notification = new Notification();
        $notification->recipient_id = $application->sender_id;
        $notification->sender_id = $userID;
        $notification->notification_id = $content->id;
        $notification->save();

        $program->status = "schedule unavailable";
        $program->save();

        return response()->json(['status' => true, 'message' => 'Successfully Sent message']);
    }
    public function reject( $id){
        $program = ApplicationProgram::where('id', $id)->first();
        $program->status = "rejected";
        $program->save();
        return response()->json(['status' => true, 'message' => 'Rejected program application']);
    }

    public function setAcceptableScoreGap(request $request, $id){
        $parameters = InstrumentParameter::where('area_instrument_id', $id)->get();
        foreach ($parameters as $parameter){
            $parameter->acceptable_score_gap = $request->gap;
            $parameter->save();
        }
        return response()->json(['status' => true, 'message' => 'Successful', 'gap' => $parameters]);
    }

    public function editAcceptableScoreGap(request $request, $id){
        $parameter = InstrumentParameter::where('id', $id)->first();
        $parameter->acceptable_score_gap = $request->gap;
        $parameter->save();
        return response()->json(['status' => true, 'message' => 'Successful', 'gap' => $parameter]);
    }
}
