<?php

namespace App\Http\Controllers\API;

use App\AttachedDocument;
use App\DummyDocument;
use App\Http\Controllers\Controller;
use App\InstrumentStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MSITransactionController extends Controller
{
    public function uploadDummyDocument(request $request){
        $validator = Validator::make($request->all(), [
            'document' => 'required|mimes:doc,docx,pdf,jpg,jpeg,png|max:2048'
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Document!']);
        $dummyDocument = new DummyDocument();
        $fileName = time().'_'.$request->document->getClientOriginalName();
        $filePath = $request->file('document')->storeAs('application/files', $fileName);
        $dummyDocument->title = $fileName;
        $dummyDocument->location = $filePath;
        $dummyDocument->save();
        return response()->json(['status' => true, 'message' => 'Successfully added document']);
    }

    public function showDummyDocument(){
        return response()->json(DummyDocument::all());
    }
}
