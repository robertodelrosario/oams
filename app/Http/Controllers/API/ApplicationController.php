<?php

namespace App\Http\Controllers\API;

use App\Application;
use App\ApplicationFile;
use App\ApplicationProgram;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\Mail\ApplicationNotification;
use App\Program;
use App\SUC;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth');
    }*/

    public function createApplication(request $request, $sucID, $userID){
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $application = new Application();
        $application->suc_id=$sucID;
        $application->sender_id = $userID;
        $application->title = $request->title;
        $application->status = 'under preparation';
        $application->save();
        $message = 0;
        $count = count($request->programs);
        for($x=0; $x<$count; $x++){
            $check = ApplicationProgram::where([
                ['application_id', $application->id], ['program_id', $request->programs[$x]['program_id']]
            ])->first();
            if(is_null($check)){
                $program = new ApplicationProgram();
                $program->application_id = $application->id;
                $program->program_id = $request->programs[$x]['program_id'];
                $program->level = $request->programs[$x]['level'];
                $program->preferred_start_date = \Carbon\Carbon::parse($request->programs[$x]['preferred_start_date'])->format('Y-m-d');
                $program->preferred_end_date = \Carbon\Carbon::parse($request->programs[$x]['preferred_end_date'])->format('Y-m-d');
                $program->status = "pending";
                $program->save();
            }
            else $message=1;
        }
        if($message == 0) return response()->json(['status' => true, 'message' => 'Successful', 'application' => $application]);
        else return response()->json(['status' => true, 'message' => 'Successfully added programs with no duplication.', 'application' => $application]);
    }

    public function submitApplication($id, $sucID){
        $application = Application::where('id', $id)->first();
        $files = ApplicationFile::where('application_id', $application->id)->first();

        $messages = null;
        if(is_null($files)){
            $messages = $messages. 'Need to attach application files.';
        }
        $programs = ApplicationProgram::where('application_id', $id)->get();
        if(!(is_null($programs))){
            $messages = $messages . ' Applied program ID number ';
            $count = count($programs);
            for ($x=0; $x<$count; $x++){

                $instrument = InstrumentProgram::where('program_id', $programs[$x]['program_id'])->first();
                if(is_null($instrument)){
                    if($x+1 == $count){
                        $messages = $messages .$programs[$x]['id']. ' ';
                    }
                    else $messages = $messages .$programs[$x]['id']. ', ';
                }
            }
            $messages = $messages . 'has a missing attached instruments.';
        }

        if (!(is_null($messages))) return response()->json(['status' => false, 'message' => $messages]);
        $application->status = 'pending';
        $application->save();

//        $suc = SUC::where('id', $sucID)->first();
//        $details = [
//            'title' => 'Application Notification for Accreditation',
//            'body' => 'Please check your AOMS account to view the application',
//            'suc' => $suc->institution_name,
//            'address' => $suc->address,
//            'email' => $suc->email,
//            'link' =>'http://online_accreditation_management_system.test/api/v1/aaccup/showApplication'
//        ];
//        \Mail::to('roberto.delrosario@ustp.edu.ph')->send(new ApplicationNotification($details));
        return response()->json(['status' => true, 'message' => 'Successful', 'application' => $application]);
    }

    public function deleteApplication($id){
        $application = Application::where('id', $id);
        $application->delete();
        return response()->json(['status' => true, 'message' => 'Application successfully deleted!']);
    }

    public function showApplication($id){
        //$applications = Application::where('suc_id', $id)->get();
        $applications = DB::table('applications')
            ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
            ->join('users', 'users.id', '=','applications.sender_id')
            ->where('applications.suc_id', $id)
            ->select('applications.*', 'sucs.institution_name', 'sucs.address', 'sucs.email', 'sucs.contact_no', 'users.first_name', 'users.last_name')
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

//    public function showSubmittedApplication($id){
//        $applications = DB::table('applications')
//            ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
//            ->join('users', 'users.id', '=','applications.sender_id')
//            ->where('applications.status','!=' ,'under preparation')
//            ->where('applications.suc_id', $id)
//            ->select('applications.*', 'sucs.institution_name','sucs.address', 'sucs.email','sucs.contact_no', 'users.first_name', 'users.last_name')
//            ->get();
//        $file_arr = array();
//        foreach ($applications as $application){
//            $files = ApplicationFile::where('application_id',$application->id)->get();
//            foreach ($files as $file){
//                $file_arr = Arr::prepend($file_arr,$file);
//            }
//        }
//        return response()->json(['applications' => $applications, 'files' => $file_arr]);
//    }

    public function uploadFile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required',
            'filename.*' => 'mimes:doc,pdf,docx,zip'
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Acceptable file types are .doc,.pdf,.docx, and .zip']);


        if ($request->hasfile('filename')) {
            foreach ($files = $request->file('filename') as $file) {
                $application = new ApplicationFile();
                $fileName = $file->getClientOriginalName();
                $filePath = $file->storeAs('application/files', $fileName);
                $application->file_title = $fileName;
                $application->file = $filePath;
                $application->application_id = $id;
                $application->save();

            }
            return response()->json(['status' => true, 'message' => 'Successfully added files!']);
        }
        return response()->json(['status' => false, 'message' => 'Unsuccessfully added files!']);
    }

    public function deleteFile($id){
        $file = ApplicationFile::where('id', $id)->first();
        File::delete(storage_path("app/".$file->file));
        $file->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted file!']);
    }

    public function viewFile($id){
        $file_link = ApplicationFile::where('id', $id)->first();
        $file = File::get(storage_path("app/".$file_link->file));
        $type = File::mimeType(storage_path("app/".$file_link->file));

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }
}
