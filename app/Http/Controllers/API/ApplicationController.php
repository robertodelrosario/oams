<?php

namespace App\Http\Controllers\API;

use App\Application;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    public function application(request $request){
        $validator = Validator::make($request->all(), [
            'application_letter' => 'required|mimes:doc,docx,pdf|max:2048',
            'campus_id' => 'required'
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Application Letter!']);

        $application = new Application();
        $fileName = time().'_'.$request->application_letter->getClientOriginalName();
        $filePath = $request->file('application_letter')->storeAs('uploads', $fileName, 'public');

        $application->application_title = time().'_'.$request->application_letter->getClientOriginalName();
        $application->application_letter = '/storage/' . $filePath;
        $application->campus_id = $request->campus_id;
        $application->save();
        return response()->json(['status' => true, 'message' => 'Successfully added application letter!']);
    }

    public function deleteApplication($id){
        $application = Application::where('id', $id);
        $application->delete();
        return response()->json(['status' => true, 'message' => 'Application successfully deleted!']);
    }

    public function showApplication($id){
        $application = Application::where('campus_id', $id)->get();
        return response()->json($application);
    }
}
