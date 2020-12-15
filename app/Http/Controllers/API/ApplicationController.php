<?php

namespace App\Http\Controllers\API;

use App\Application;
use App\ApplicationFile;
use App\Http\Controllers\Controller;
use App\Mail\ApplicationNotification;
use App\SUC;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\Input;

class ApplicationController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth');
    }*/

    public function createApplication($id){
        $application = new Application();
        $application->suc_id=$id;
        $application->save();

        $suc = SUC::where('id', $id)->first();
        $details = [
            'title' => 'Application Notification for Accreditation',
            'body' => 'Please check your AOMS account to view the application',
            'suc' => $suc->institution_name,
            'address' => $suc->address,
            'email' => $suc->email,
            'link' =>'http://online_accreditation_management_system.test/api/v1/aaccup/showApplication'
        ];
        \Mail::to('roberto.delrosario@ustp.edu.ph')->send(new ApplicationNotification($details));

        return response()->json(['status' => true, 'message' => 'Successful', 'application' => $application]);
    }

//    public function application(request $request, $id){
//        $validator = Validator::make($request->all(), [
//            'application_letter' => 'required|mimes:doc,docx,pdf,jpg,png|max:2048'
//        ]);
//        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required ApplicationNotification Letter!']);
//        $application = new Application();
//        $fileName = time().'_'.$request->application_letter->getClientOriginalName();
//        $filePath = $request->file('application_letter')->storeAs('application/files', $fileName);
//        $application->application_title = $fileName;
//        $application->application_letter = $filePath;
//        $application->suc_id = $id;
//        $application->save();
//
//        $suc = SUC::where('id', $id)->first();
//        $details = [
//            'title' => 'Application Notification for Accreditation',
//            'body' => 'Please check your AOMS account to view the application',
//            'suc' => $suc->institution_name,
//            'address' => $suc->address,
//            'email' => $suc->email,
//            'link' =>'http://online_accreditation_management_system.test/api/v1/aaccup/showApplication'
//        ];
//        \Mail::to('roberto.delrosario@ustp.edu.ph')->send(new ApplicationNotification($details));
//        return response()->json(['status' => true, 'message' => 'Successfully added application letter!', 'application' => $application]);
//    }

    public function deleteApplication($id){
        $application = Application::where('id', $id);
        $application->delete();
        return response()->json(['status' => true, 'message' => 'ApplicationNotification successfully deleted!']);
    }

    public function showApplication($id){
        //$applications = Application::where('suc_id', $id)->get();
        $applications = DB::table('applications')
            ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
            ->where('applications.suc_id', $id)
            ->select('applications.*', 'sucs.institution_name', 'sucs.address', 'sucs.email', 'sucs.contact_no')
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

    public function uploadFile(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'filename' => 'required',
            'filename.*' => 'mimes:doc,pdf,docx,zip'
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Application Letter!']);


        if ($request->hasfile('filename')) {
            foreach ($files = $request->file('filename') as $file) {
                $application = new ApplicationFile();
                $fileName = time() . '_' . $file->getClientOriginalName();
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
        $file = ApplicationFile::where('id', $id);
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
