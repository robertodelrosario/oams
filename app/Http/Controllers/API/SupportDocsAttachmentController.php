<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupportDocsAttachmentController extends Controller
{
    public function attachFile(request $request, $id){
        $validator = Validator::make($request->all(), [
            'document_title' => 'required',
            'link' => 'required',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Required file!']);



    }
}
