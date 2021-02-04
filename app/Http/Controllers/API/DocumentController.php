<?php

namespace App\Http\Controllers\API;

use App\AttachedDocument;
use App\Document;
use App\Http\Controllers\Controller;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class DocumentController extends Controller
{
    public function uploadDocument(request $request, $userID, $officeID){
        $document = new Document();
        if($request->type == 'file'){
            $fileName = $request->document->getClientOriginalName();
            $filePath = $request->file('document')->storeAs('document/files', $fileName);
            $document->document_name = $fileName;
            $document->link = $filePath;
            $document->office_id = $officeID;
            $document->uploader_id = $userID;
            $document->type = $request->type;
            $document->save();
        }
        else{
            $document->document_name = $request->document_name;
            $document->link = $request->link;
            $document->office_id = $officeID;
            $document->uploader_id = $userID;
            $document->type = $request->type;
            $document->save();
        }
        foreach ($request->tag as $key){
            $tag = new Tag();
            $tag->tag = $key;
            $tag->document_id = $document->id;
            $tag->save();
        }
        return response()->json(['status' => true, 'message' => 'Successfully added document', 'document' =>$document]);
    }

    public function showDocument($id){
        $documents = DB::table('documents')
            ->join('offices', 'offices.id', '=', 'documents.office_id')
            ->join('users', 'users.id', '=', 'documents.uploader_id')
            ->select('documents.*', 'offices.office_name', 'users.first_name', 'users.last_name', 'users.email')
            ->where('offices.id', $id)
            ->get();

        $tag = array();
        foreach ($documents as $document)
        {
            $tags = Tag::where('document_id', $document->id)->get();
            foreach ($tags as $key){
                $tag = Arr::prepend($tag, $key);
            }
        }
        return response()->json(['documents' =>$documents, 'tags' => $tag]);
    }

    public function deleteDocument($id){
        $document = Document::where('id', $id)->first();
        $check = AttachedDocument::where('document_id', $document->id)->get();
        if($check->count() > 0 ) return response()->json(['status' => false, 'message' => 'Document is being used.']);
        if ($document->type == 'file'){
            File::delete(storage_path("app/".$document->link));
            $document->delete();
        }
        else $document->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted file']);
    }

    public function addTag(request $request, $id){
        $tags = array();
        foreach ($request->tag as $key){
            $tag = new Tag();
            $tag->tag = $key;
            $tag->document_id = $id;
            $tag->save();
            $tags = Arr::prepend($tags,$tag);
        }
        return response()->json(['status' => true, 'message' => 'Successfully added tags', 'tags' =>$tags]);
    }

    public function deleteTag($id){
        $tag = Tag::where('id', $id);
        $tag->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted tag']);
    }

    public function viewFile($id){
        $file_link = Document::where('id', $id)->first();
        $file = File::get(storage_path("app/".$file_link->link));
        $type = File::mimeType(storage_path("app/".$file_link->link));

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }
}
