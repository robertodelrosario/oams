<?php

namespace App\Http\Controllers\API;

use App\Application;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Application Letter!']);

        $application = new Application();
        $fileName = time().'_'.$request->application_letter->getClientOriginalName();
        $filePath = $request->file('application_letter')->storeAs('application', $fileName, 'public');
        $application->application_title = $fileName;
        $application->application_letter = '/storage/' . $filePath;
        $application->suc_id = $id;
        $application->save();
        return response()->json(['status' => true, 'message' => 'Successfully added application letter!']);
    }
    public function deleteApplication($id){
        $application = Application::where('id', $id);
        $application->delete();
        return response()->json(['status' => true, 'message' => 'Application successfully deleted!']);
    }

    public function showApplication($id){
        $application = Application::where('suc_id', $id)->get();
        return response()->json($application);
    }

    public function viewFile($id){
        $application = Application::where('id', $id)->first();
//        $path = storage_path($application->application_letter);

        $file = File::get($application->application_letter);
        $type = File::mimeType($application->application_letter);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
        //$content = Storage::get($path);
        //return response()->json(['status' => true, 'message' => 'retrieved file', $content]);
    }

}
