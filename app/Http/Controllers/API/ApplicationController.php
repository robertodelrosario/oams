<?php

namespace App\Http\Controllers\API;

use App\Application;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    public function uploadFile(request $request){
        $validator = Validator::make($request->all(), [
            'application_title' => 'required',
            'application_letter' => 'required|mimes:doc,docx,pdf|max:2048',
            'campus_id' => 'required'
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Application Letter!']);

        $application = new Application;
        $fileName = time().'_'.$request->application_letter->getClientOriginalName();
        $filePath = $request->file('application_letter')->storeAs('uploads', $fileName, 'public');

        $application->application_title = time().'_'.$request->application_letter->getClientOriginalName();
        $application->application_letter = '/storage/' . $filePath;
        $application->campus_id = $request->campus_id;
        $application->save();
        return response()->json(['status' => true, 'message' => 'Successfully added application letter!']);
    }
}
