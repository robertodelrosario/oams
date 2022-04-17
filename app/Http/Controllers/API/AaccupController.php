<?php

namespace App\Http\Controllers\API;

use App\AccreditorRequest;
use App\Application;
use App\ApplicationCoordinator;
use App\ApplicationFile;
use App\ApplicationProgram;
use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\Mail\ApplicationNotification;
use App\Mail\RequestAccreditor;
use App\Notification;
use App\NotificationContent;
use App\NotificationProgram;
use App\ParameterProgram;
use App\RequiredRating;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

//        $coordinators = new Collection();
//        $application_coordinators = ApplicationCoordinator::where('application_id', $id)->get();
//        foreach ($application_coordinators as $application_coordinator){
//            $user = User::where('id', $application_coordinator->user_id)->first();
//            $coordinators->push([
//                'id' => $application_coordinator->id,
//                'user_id' => $application_coordinator->user_id,
//                'first_name' => $user->first_name,
//                'last_name' => $user->last_name,
//                'status' => $application_coordinator->status,
//                'date_requested' => $application_coordinator->created_at,
//                'date_updated' => $application_coordinator->updated_at
//            ]);
//        }
        return response()->json(['programs' =>$programs, 'users' => $users]);
//        return response()->json(['programs' =>$programs, 'users' => $users, 'coordinators' => $coordinators]);
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

            $notif = NotificationContent::where('content', 'Request for Accreditation - '.$accreditorRequest->role)->first();
            $notification = new NotificationContent();
            $send_notification = new Notification();
            if(is_null($notif)){
                $notification->content = 'Request for Accreditation - '.$accreditorRequest->role;
                $notification->notif_type = 'request for accreditation';
                $notification->save();
                $send_notification->notification_id = $notification->id;
            }
            else $send_notification->notification_id =$notif->id;
            $send_notification->recipient_id = $request->taskRequests[$x]['user_id'];
            $send_notification->sender_id = $userID;
            $send_notification->status = 0;
            $send_notification->save();

            $notification_program = new NotificationProgram();
            $notification_program->notification_id = $send_notification->id;
            $notification_program->applied_program_id = $id;
            $notification_program->save();

//            $req = DB::table('accreditor_requests')
//                ->join('applications_programs', 'applications_programs.id', '=', 'accreditor_requests.application_program_id')
//                ->join('applications', 'applications.id', '=', 'applications_programs.application_id')
//                ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
//                ->join('programs', 'programs.id', '=', 'applications_programs.program_id')
//                ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
//                ->where('accreditor_requests.id', $accreditorRequest->id)
//                ->where('accreditor_requests.status', '=', 'pending')
//                ->select( 'accreditor_requests.id','sucs.institution_name','campuses.campus_name','programs.program_name', 'applications_programs.approved_start_date', 'applications_programs.approved_end_date')
//                ->first();
//
//            $title = 'Request for Accreditation - '.$accreditorRequest->role;
//            $details = [
//                'title' => $title,
//                'suc' => $req->institution_name,
//                'campus' => $req->campus_name,
//                'program' => $req->program_name,
//                'start_date' => $req->approved_start_date,
//                'start_end' => $req->approved_end_date,
//                'link' =>'http://online_accreditation_management_system.test/api/v1/auth/login'
//            ];
//            \Mail::to('roberto.delrosario@ustp.edu.ph')->send(new RequestAccreditor($details));
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

//            $title = 'Request for Accreditation - '.$accreditorRequest->role;
//            $details = [
//                'title' => $title,
//                'suc' => $req->institution_name,
//                'campus' => $req->campus_name,
//                'program' => $req->program_name,
//                'start_date' => $req->approved_start_date,
//                'start_end' => $req->approved_end_date,
//                'link' =>'http://online_accreditation_management_system.test/api/v1/auth/login'
//            ];
//            \Mail::to('roberto.delrosario@ustp.edu.ph')->send(new RequestAccreditor($details));
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
        $req = AccreditorRequest::where('id', $id)->first();
        // if($req->status == 'accepted') return response()->json(['status' => false ,'message' => 'Request already accepted.']);
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

    public function setAccreditorLead($id){
        $accreditorRequest = AccreditorRequest::where('id', $id)->first();
        $programs = AccreditorRequest::where('application_program_id', $accreditorRequest->application_program_id)->get();
        foreach ($programs as $program){
            if(Str::contains($program->role, 'leader')) return response()->json(['status' => false ,'message' => 'The team has already a leader.']);
        }
        if($accreditorRequest->status == 'accepted') {

        }

        $accreditorRequest->role = '[leader] '.$accreditorRequest->role;
        $accreditorRequest->save();
        return response()->json(['status' => true ,'message' => 'Successfully assigned the leader.', 'user'=>$accreditorRequest]);
    }

    public function approve($id){
        $program = ApplicationProgram::where('id', $id)->first();
        $program->approved_start_date = $program->preferred_start_date;
        $program->approved_end_date = $program->preferred_end_date;
        $program->status = "approved";
        $program->save();

        $application = Application::where('id', $program->application_id)->first();
        $application->status = 'on going';
        $application->save();
        return response()->json(['status' => true, 'message' => 'Successfully approved program application', 'program' => $program]);
    }

    public function rechedule(request $request, $id, $userID){
        $validator = Validator::make($request->all(), [
            'message' => 'required'
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Message Letter!']);

        $program = ApplicationProgram::where('id', $id)->first();
        $application = Application::where('id', $program->application_id)->first();

        $content = new NotificationContent();
        $content->content = $request->message;
        $content->notif_type = 'declined schedule';
        $content->save();

        $notification = new Notification();
        $notification->recipient_id = $application->sender_id;
        $notification->sender_id = $userID;
        $notification->notification_id = $content->id;
        $notification->status = 0;
        $notification->save();

        $notifProgram = new NotificationProgram();
        $notifProgram->notification_id = $notification->id;
        $notifProgram->applied_program_id = $program->id;
        $notifProgram->save();

        $program->status = "schedule unavailable";
        $program->preferred_start_date = $request->preferred_start_date;
        $program->preferred_end_date = $request->preferred_end_date;
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
        $parameters = ParameterProgram::where('program_instrument_id', $id)->get();
        foreach ($parameters as $parameter){
            $parameter->acceptable_score_gap = $request->gap;
            $parameter->save();
        }
        return response()->json(['status' => true, 'message' => 'Successful', 'gap' => $parameters]);
    }

    public function showAcceptableScoreGap($id){
        $parameters = ParameterProgram::where('program_instrument_id', $id)->get();
        return response()->json(['gap' => $parameters]);
    }

    public function editAcceptableScoreGap(request $request){
        foreach ($request->gaps as $gap){
            $parameter = ParameterProgram::where('id', $gap['id'])->first();
            $parameter->acceptable_score_gap = $gap['gap'];
            $parameter->save();
        }
        return response()->json(['status' => true, 'message' => 'Successful', 'gap' => $parameter]);
    }

    public function removeAcceptableScoreGap($id){
        $parameter = ParameterProgram::where('id', $id)->first();
        $parameter->acceptable_score_gap = null;
        $parameter->save();
        return response()->json(['status' => true, 'message' => 'Successful', 'gap' => $parameter]);
    }

    public function saveRequiredRating(request $request){
        foreach ($request->ratings as $rating){
            $required_rating = RequiredRating::where('id', $rating['id'])->first();
            $required_rating->accreditation_status = $rating['accreditation_status'];
            $required_rating->grand_mean = $rating['grand_mean'];
            $required_rating->area_mean = $rating['area_mean'];
            $required_rating->save();
        }
        return response()->json(['status' => true, 'message' => 'Successfully saved required ratings per accreditation status' ]);
    }

    public function setRequiredRating(request $request){
        foreach ($request->ratings as $rating){
            $required_rating = new RequiredRating();
            $required_rating->accreditation_status = $rating['accreditation_status'];
            $required_rating->grand_mean = $rating['grand_mean'];
            $required_rating->area_mean = $rating['area_mean'];
            $required_rating->save();
        }
        return response()->json(['status' => true, 'message' => 'Successfully saved required ratings per accreditation status' ]);
    }
    public function showRequiredRating(){
        return response()->json(RequiredRating::all());
    }

    public function showRequiredProgramRating($id){
        $program = ApplicationProgram::where('id', $id)->first();
        if(is_null($program)) return response()->json(['status' => false, 'message' => 'Invalid ID.' ]);
        if($program->level == 'Candidate'){
            $required_rating = RequiredRating::where('accreditation_status', 'Candidate')->first();
        }
        elseif ($program->level == 'Level I')
        {
            $required_rating = RequiredRating::where('accreditation_status', 'Accredited Level I')->first();
        }
        elseif ($program->level == 'Level II')
        {
            $required_rating = RequiredRating::where('accreditation_status', 'Accredited Level II')->first();
        }
        elseif ($program->level == 'Level III')
        {
            $required_rating = RequiredRating::where('accreditation_status', 'Accredited Level III')->first();
        }
        elseif ($program->level == 'Level IV')
        {
            $required_rating = RequiredRating::where('accreditation_status', 'Accredited Level IV')->first();
        }

        return response()->json($required_rating);
    }

    public function requestCoordinator($application_id, $user_id){
        $check = ApplicationCoordinator::where('application_id', $application_id)->first();
        if(is_null($check)){
            $coordinator = new ApplicationCoordinator();
            $coordinator->application_id = $application_id;
            $coordinator->user_id = $user_id;
            $coordinator->status = "pending";
            $success = $coordinator->save();
            if($success) return response()->json(['status' => true, 'message' => 'Successfully requested coordinator.']);
            else return response()->json(['status' => false, 'message' => 'unsuccessfully requested coordinator.']);
        }
        else return response()->json(['status' => false, 'message' => 'Already requested coordinator']);
    }

    public function showCoordinatorRequest($id){
        $collection = new Collection();
        $coordinator = ApplicationCoordinator::where('application_id', $id)->first();
        if(is_null($coordinator)) return response()->json([]);
        $user = User::where('id',$coordinator->user_id)->first();
        $collection->push([
            'id' => $coordinator->id,
            'application_id' => $coordinator->id,
            'user_id' => $coordinator->user_id,
            'status' => $coordinator->status,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ]);
        return response()->json($collection);
    }

    public function removeCoordinatorRequest($id){
        $coordinator = ApplicationCoordinator::where('id', $id)->first();
        $coordinator->delete();
        return response()->json(['status' => true, 'message' => 'Successfully removed requested coordinator.']);
    }
}
