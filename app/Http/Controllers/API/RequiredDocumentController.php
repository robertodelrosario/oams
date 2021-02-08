<?php

namespace App\Http\Controllers\API;

use App\Document;
use App\Http\Controllers\Controller;
use App\Office;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RequiredDocumentController extends Controller
{
    public function makeDocumentList(request $request, $id){
        if(is_null($request->list)) return response()->json(['status' => false, 'message' => 'List is empty']);
        $lists = $request->list;
        foreach ($lists as $list){
            $document = new Document();
            $document->document_name = $list['document_title'];
            $document->link = 'none';
            $document->type = 'undefined';
            $document->office_id = $id;
            $document->save();
            foreach ($list['area'] as $area){
                $tag = new Tag();
                $tag->document_id = $document->id;
                $tag->tag = $area;
                $tag->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully added to list']);
    }

}
