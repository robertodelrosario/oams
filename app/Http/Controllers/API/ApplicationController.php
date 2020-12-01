<?php

namespace App\Http\Controllers\API;

use App\Application;
use App\Http\Controllers\Controller;
use App\Mail\ApplicationNotification;
use App\SUC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth');
    }*/

    public function application(request $request, $id){
        $validator = Validator::make($request->all(), [
            'application_letter' => 'required|mimes:doc,docx,pdf,jpg,png|max:2048'
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required ApplicationNotification Letter!']);

        $application = new Application();
        $fileName = time().'_'.$request->application_letter->getClientOriginalName();
        $filePath = $request->file('application_letter')->storeAs('application/files', $fileName);
        $application->application_title = $fileName;
        $application->application_letter = $filePath;
        $application->suc_id = $id;
        $application->save();

        $suc = SUC::where('id', $id)->first();
        $details = [
            'Title' => 'Application Notification for Accreditation',
            'Body' => 'Please check your AOMS account to view the application',
            'suc' => $suc->institution_name,
            'address' => $suc->
        ];
        \Mail::to('roberto.delrosario@ustp.edu.ph')->send(new ApplicationNotification($details));

        return response()->json(['status' => true, 'message' => 'Successfully added application letter!', 'application' => $application]);
    }

//    public function application(request $request, $id){
//        $validator = Validator::make($request->all(), [
//            'application_letter' => 'required',
//            'application_title' => 'required'
//        ]);
//        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required ApplicationNotification Letter!']);
//
//        $application = new ApplicationNotification();
//        $application->application_title = $request->application_title;
//        $application->application_letter = $request->file('application_letter')->store("application/files");
//        $application->suc_id = $id;
//        $application->save();
//        return response()->json(['status' => true, 'message' => 'Successfully added application letter!', 'application' => $application]);
//    }

    public function deleteApplication($id){
        $application = Application::where('id', $id);
        $application->delete();
        return response()->json(['status' => true, 'message' => 'ApplicationNotification successfully deleted!']);
    }

    public function showApplication($id){
        $application = Application::where('suc_id', $id)->get();
        return response()->json($application);
    }
    public function viewFile($id){
        $application = Application::where('id', $id)->first();
        $file = File::get(storage_path("app/".$application->application_letter));
        $type = File::mimeType(storage_path("app/".$application->application_letter));

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }

//    public function viewFile($id){
//        $application = ApplicationNotification::where('id', $id)->first();
////        $path = storage_path($application->application_letter);
//
//        $file = File::get(storage_path($application->application_letter));
//        $type = File::mimeType($application->application_letter);
//        dd($type);
//
//        $response = Response::make($file, 200);
//        $response->header("Content-Type", $type);
//        return $response;
//        //$content = Storage::get($path);
//        //return response()->json(['status' => true, 'message' => 'retrieved file', $content]);
//    }
}
