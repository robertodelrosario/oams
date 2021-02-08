<?php

namespace App\Http\Controllers\API;

use App\Document;
use App\Http\Controllers\Controller;
use App\Office;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RequiredDocumentController extends Controller
{
    public function makeDocumentList(request $request){
        if(is_null($request->lists)) return response()->json(['status' => false, 'message' => 'List is empty']);
      //  dd($request->lists);
        $lists = $request->lists;
        foreach ($lists as $list){
            $document = new Document();
            $document->document_name = $list['document_title'];
            $document->link = 'none';
            $document->type = 'undefined';
            $document->office_id = $list['office_id'];
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

    public function showDocumentList($id){
        $offices = Office::where('campus_id', $id)->get();
        $docs = array();
        $tags = array();
        foreach ($offices as $office){
            $documents = Document::where('office_id', $office->id)->get();
            foreach ($documents as $document) $docs = Arr::prepend($docs, $document);
        }
        foreach ($docs as $doc){
            $taggings = Tag::where('document_id', $doc->id)->get();
            foreach ($taggings as $tagging) $tags = Arr::prepend($tags, $tagging);
        }
        return response()->json(['status' => true, 'message' => 'Successfully added to list', 'documents'=>$docs, 'tags'=>$tags]);

    }
}
