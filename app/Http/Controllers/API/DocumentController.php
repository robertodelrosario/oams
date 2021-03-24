<?php

namespace App\Http\Controllers\API;

use App\ApplicationFile;
use App\AttachedDocument;
use App\Document;
use App\DocumentContainer;
use App\Http\Controllers\Controller;
use App\Tag;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;

class DocumentController extends Controller
{

    public function makeDocumentList(request $request, $id){
        if(is_null($request->list)) return response()->json(['status' => false, 'message' => 'List is empty']);
        $lists = $request->list;
        foreach ($lists as $list){
            $document = new DocumentContainer();
            $document->container_name = $list['document_title'];
            $document->office_id = $id;
            $document->user_id = null;
            $document->save();
            foreach ($list['area'] as $area){
                $tag = new Tag();
                $tag->container_id = $document->id;
                $tag->tag = $area;
                $tag->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully added to list']);
    }

    public function uploadDocument(request $request, $userID, $id){
        if($request->type == 'file'){
            if ($request->hasfile('documents')) {
                foreach ($files = $request->file('documents') as $file) {
                    $document = new Document();
                    $fileName = $file->getClientOriginalName();
                    $filePath = $file->storeAs('document/files', $fileName);
                    $document->document_name = $fileName;
                    $document->link = $filePath;
                    $document->uploader_id = $userID;
                    $document->type = $request->type;
                    $document->container_id = $id;
                    $document->save();
                }
                return response()->json(['status' => true, 'message' => 'Successfully added files!']);
            }
        }
        elseif($request->type == 'link'){
            dd($request->files);
            foreach($request->files as $file){
                $document = new Document();
                $document->document_name = $file->filename;
                $document->link = $file->link;
                $document->uploader_id = $userID;
                $document->type = $request->type;
                $document->container_id = $id;
                $document->save();
                return response()->json(['status' => true, 'message' => 'Successfully added files!']);
            }
            return response()->json(['status' => false, 'message' => 'Unsuccessfully added files![1]']);
        }
        return response()->json(['status' => false, 'message' => 'Unsuccessfully added files!']);
//
////        foreach ($request->tag as $key){
////            if($key == null){
////                $tag = new Tag();
////                $tag->tag = $key;
////                $tag->document_id = $document->id;
////                $tag->save();
////            }
////        }
//
//        return response()->json(['status' => true, 'message' => 'Successfully added document', 'document' =>$document]);
    }

    public function showContainer($id){
        $collection = new Collection();
        $containers = DocumentContainer::where('office_id', $id)->get();
        foreach ($containers as $container){
            $tags = Tag::where('container_id', $container->id)->get();
            $collection->push(['container' => $container, 'tags' => $tags]);
        }
        return response()->json(['documents' =>$collection]);
    }
    public function showDocument($id){
        $collection = new Collection();
        $documents = Document::where('container_id', $id)->get();
        foreach ($documents as $document){
            $user = User::where('id', $document->uploader_id)->first();
            $first_name = $user->first_name;
            $last_name = $user->last_name;

            $collection->push([
                'id' => $document->id,
                'document_name' => $document->document_name,
                'link' => $document->link,
                'type' => $document->type,
                'uploader_id' => $document->uploader_id,
                'updated_at' => $document->updated_at,
                'office_name' => $document->office_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ]);
        }
//
//
//        $documents = DB::table('documents')
//            ->join('offices', 'offices.id', '=', 'documents.office_id')
//            ->select('documents.*', 'offices.office_name')
//            ->where('offices.id', $id)
//            ->get();
//        foreach ($documents as $document){
//
//            if($document->uploader_id == null){
//                $first_name = null;
//                $last_name = null;
//            }
//            else{
//                $user = User::where('id', $document->uploader_id)->first();
//                $first_name = $user->first_name;
//                $last_name = $user->last_name;
//            }
//            $collection->push([
//                'id' => $document->id,
//                'document_name' => $document->document_name,
//                'link' => $document->link,
//                'type' => $document->type,
//                'office_id' => $document->office_id,
//                'uploader_id' => $document->uploader_id,
//                'uploader_id' => $document->uploader_id,
//                'updated_at' => $document->updated_at,
//                'office_name' => $document->office_name,
//                'first_name' => $first_name,
//                'last_name' => $last_name,
//            ]);
//        }
//        $tag = array();
//        foreach ($documents as $document)
//        {
//            $tags = Tag::where('document_id', $document->id)->get();
//            foreach ($tags as $key){
//                $tag = Arr::prepend($tag, $key);
//            }
//        }
        return response()->json(['documents' =>$collection]);
    }

    public function deleteAllDocument(){
        $documents = Document::all();
        foreach ($documents as $document)
            $document->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted files']);
    }
    public function deleteDocument($id){
        $document = Document::where('id', $id)->first();
        if($document->link != 'none') return response()->json(['status' => false, 'message' => 'Document is attached.']);
        if ($document->type == 'file'){
            File::delete(storage_path("app/".$document->link));
            $document->delete();
        }
        else $document->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted file']);
    }

    public function removeDocument($id){
        $document = Document::where('id', $id)->first();
        $document->link = 'none';
        $document->uploader_id = null;
        $document->save();
        return response()->json(['status' => true, 'message' => 'Successfully removed file']);
    }

    public function addTag(request $request, $id){
        $tags = array();
        foreach ($request->tag as $key){
            $tag = new Tag();
            $tag->tag = $key;
            $tag->container_id = $id;
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

    public function editDocumentName(request $request, $id){
        $document = Document::where('id', $id)->first();
        $document->document_name =$request->document_name;
        $document->save();
        return response()->json(['status' => true, 'message' => 'Successfully edited document name/']);
    }

    public function makeOwnDocumentList(request $request, $id){
        if(is_null($request->list)) return response()->json(['status' => false, 'message' => 'List is empty']);
        $lists = $request->list;
        foreach ($lists as $list){
            $document = new DocumentContainer();
            $document->container_name = $list['document_title'];
            $document->office_id = null;
            $document->user_id = $id;
            $document->save();
            foreach ($list['area'] as $area){
                $tag = new Tag();
                $tag->container_id = $document->id;
                $tag->tag = $area;
                $tag->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully added to list']);
    }

    public function showOwnContainer($id){
        $collection = new Collection();
        $containers = DocumentContainer::where('user_id', $id)->get();
        foreach ($containers as $container){
            $tags = Tag::where('container_id', $container->id)->get();
            $collection->push(['container' => $container, 'tags' => $tags]);
        }
        return response()->json(['documents' =>$collection]);
    }
    public function uploadOwnDocument(request $request, $id, $statementID){
        if($request->type == 'file'){
            $document = new Document();
            $fileName = $request->document->getClientOriginalName();
            $filePath = $request->file('document')->storeAs('document/files', $fileName);
            $document->document_name = $fileName;
            $document->link = $filePath;
            $document->uploader_id = $id;
            $document->type = $request->type;
            $document->container_id = $request->container_id;
            $document->save();
        }
        else{
            $document = new Document();
            $document->document_name = $request->document_name;
            $document->link = $request->link;
            $document->uploader_id = $id;
            $document->type = $request->type;
            $document->container_id = $request->container_id;
            $document->save();
        }

        $supportDocument = new AttachedDocument();
        $supportDocument->statement_id = $statementID;
        $supportDocument->document_id = $document->id;
        $supportDocument->save();

        return response()->json(['status' => true, 'message' => 'Successfully added document', 'document' =>$document]);
    }

    public function showOwnDocument($id){
        $collection = new Collection();
        $documents = Document::where('container_id', $id)->get();
        foreach ($documents as $document){
            $user = User::where('id', $document->uploader_id)->first();
            $first_name = $user->first_name;
            $last_name = $user->last_name;

            $collection->push([
                'id' => $document->id,
                'document_name' => $document->document_name,
                'link' => $document->link,
                'type' => $document->type,
                'uploader_id' => $document->uploader_id,
                'updated_at' => $document->updated_at,
                'office_name' => $document->office_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ]);
        }
        return response()->json(['documents' =>$documents]);
    }
}
