<?php

namespace App\Http\Controllers\API;

use App\AttachedDocument;
use App\Document;
use App\Http\Controllers\Controller;
use App\Tag;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;

class DocumentController extends Controller
{
    public function uploadDocument(request $request, $userID, $id){
        $document = Document::where('id', $id)->first();
        if($request->type == 'file'){
            $fileName = $request->document->getClientOriginalName();
            $filePath = $request->file('document')->storeAs('document/files', $fileName);
            $document->link = $filePath;
            $document->uploader_id = $userID;
            $document->type = $request->type;
            $document->save();
        }
        else{
            $document->link = $request->link;
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
        $collection = new Collection();
        $documents = DB::table('documents')
            ->join('offices', 'offices.id', '=', 'documents.office_id')
            //->join('users', 'users.id', '=', 'documents.uploader_id')
            //->select('documents.*', 'offices.office_name', 'users.first_name', 'users.last_name', 'users.email')
            ->select('documents.*', 'offices.office_name')
            ->where('offices.id', $id)
            ->get();
        foreach ($documents as $document){

            if($document->uploader_id == null){
                $first_name = null;
                $last_name = null;
            }
            else{
                $user = User::where('id', $document->uploader_id)->first();
                $first_name = $user->first_name;
                $last_name = $user->last_name;
            }
            $collection->push([
                'id' => $document->id,
                'document_name' => $document->document_name,
                'link' => $document->link,
                'type' => $document->type,
                'office_id' => $document->office_id,
                'uploader_id' => $document->uploader_id,
                'uploader_id' => $document->uploader_id,
                'updated_at' => $document->updated_at,
                'office_name' => $document->office_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ]);
        }
        $tag = array();
        foreach ($documents as $document)
        {
            $tags = Tag::where('document_id', $document->id)->get();
            foreach ($tags as $key){
                $tag = Arr::prepend($tag, $key);
            }
        }
        return response()->json(['documents' =>$collection, 'tags' => $tag]);
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
