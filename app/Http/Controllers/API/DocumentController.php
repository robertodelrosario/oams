<?php

namespace App\Http\Controllers\API;

use App\ApplicationFile;
use App\AttachedDocument;
use App\Campus;
use App\CampusOffice;
use App\CampusUser;
use App\Document;
use App\DocumentContainer;
use App\Http\Controllers\Controller;
use App\Office;
use App\OfficeUser;
use App\Tag;
use App\User;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            $document->type = $request->type;
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
            $validator = Validator::make($request->all(), [
                'documents' => 'required',
                'documents.*' => 'max:100000 | mimes:pdf,png,jpg'
            ]);
            if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Acceptable file size is below or equal to 100mb and file type: pdf,png,jpg']);

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
            foreach($request->file as $file){
                $document = new Document();
                $document->document_name = $file['filename'];
                $document->link = $file['link'];
                $document->uploader_id = $userID;
                $document->type = $request->type;
                $document->container_id = $id;
                $document->save();
            }
            return response()->json(['status' => true, 'message' => 'Successfully added files!']);
        }
        return response()->json(['status' => false, 'message' => 'Unsuccessfully added files!']);
    }

    public function showContainer($id){
        $collection = new Collection();
        $containers = DocumentContainer::where('office_id', $id)->get();
        foreach ($containers as $container){
            $documents = Document::where('container_id', $container->id)->get();
            $tags = Tag::where('container_id', $container->id)->get();
            $collection->push(['container' => $container, 'tags' => $tags, 'number' => count($documents)]);
        }
        return response()->json(['documents' =>$collection]);
    }

    public function showAllContainer($userID,$id){
        $collection = new Collection();
        $campus = Campus::where('id', $id)->first();

        $offices = Office::where([
            ['campus_id', $campus->id], ['parent_office_id', null]
        ])->get();
        foreach ($offices as $office){
            $containers = DocumentContainer::where('office_id', $office->id)->get();
            foreach ($containers as $container){
                $office = Office::where('id', $container->office_id)->first();
                $campus =Campus::where('id', $office->campus_id)->first();
                $tags = Tag::where('container_id', $container->id)->get();
                $documents = Document::where('container_id', $container->id)->get();
                $collection->push(['container' => $container, 'tags' => $tags, 'type' => 'main', 'number' => count($documents), 'campus_name' =>$campus->campus_name, 'office_name' => $office->office_name]);
            }
        }
        $user_roles = UserRole::where('user_id', $userID)->get();
        $office_collection = new Collection();
        foreach ($user_roles as $user_role) {
            $office_users = OfficeUser::where('user_role_id', $user_role->id)->get();
            foreach ($office_users as $office_user) $office_collection->push(['office_id' => $office_user->office_id]);
        }
        foreach ($office_collection as $off) {
            $containers = DocumentContainer::where('office_id', $off['office_id'])->get();
            foreach ($containers as $container) {
                $office = Office::where('id', $container->office_id)->first();
                $campus = Campus::where('id', $office->campus_id)->first();
                $tags = Tag::where('container_id', $container->id)->get();
                $documents = Document::where('container_id', $container->id)->get();
                $collection->push(['container' => $container, 'tags' => $tags, 'type' => 'department', 'number' => count($documents), 'campus_name' => $campus->campus_name, 'office_name' => $office->office_name]);
            }
        }
        return response()->json(['documents' =>$collection]);
    }

    public function deleteContainer($id){
        $documents = Document::where('container_id', $id)->get();
        if(count($documents) == 0){
            $container = DocumentContainer::where('id', $id);
            $container->delete();
            return response()->json(['status' => true, 'message' => 'Successfully deleted container']);
        }
        else return response()->json(['status' => false, 'message' => 'Container has document/s, unable to delete.']);
    }

    public function editContainer(request $request, $id){
        $container = DocumentContainer::where('id', $id)->first();
        $container->container_name = $request->container_name;
        $container->type = $request->type;
        $container->save();
        return response()->json(['status' => true, 'message' => 'Successfully edited container']);
    }

    public function showDocument($id){
        $collection = new Collection();
        $documents = Document::where('container_id', $id)->get();
        foreach ($documents as $document){
            if($document->uploader_id != null)
            {
                $user = User::where('id', $document->uploader_id)->first();
                $first_name = $user->first_name;
                $last_name = $user->last_name;
            }
            else{
                $first_name = null;
                $last_name = null;
            }

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
//    public function deleteDocument($id){
//        $document = AttachedDocument::where('document_id', $id)->first();
//        if(!(is_null($document))) return response()->json(['status' => false, 'message' => 'Document was already used.']);
//        if ($document->type == 'file'){
//            File::delete(storage_path("app/".$document->link));
//            $document->delete();
//        }
//        else $document->delete();
//        return response()->json(['status' => true, 'message' => 'Successfully deleted file']);
//    }

    public function removeDocument($id){
        $attached_document = AttachedDocument::where('document_id', $id)->get();
        if(count($attached_document) == 0){
            $document = Document::where('id', $id)->first();
            $container = DocumentContainer::where('id', $document->container_id)->first();
            if($document->uploader_id != auth()->user()->id) {
                $user_roles = UserRole::where('user_id', auth()->user()->id)->get();
                foreach ($user_roles as $user_role){
                    if($user_role->role_id == 2 ||$user_role->role_id == 3){
                        $office_user = OfficeUser::where('user_role_id',$user_role->id)->first();
                        if($container->office_id == $office_user->office_id){
                            if ($document->type == 'file'){
                                File::delete(storage_path("app/".$document->link));
                                $document->delete();
                            }
                            else $document->delete();
                            return response()->json(['status' => true, 'message' => 'Successfully removed file']);
                        }
                    }
                }
                return response()->json(['status' => false, 'message' => 'Cannot delete file. You are not the uploader nor the Head of the office.']);
            }
            if ($document->type == 'file'){
                File::delete(storage_path("app/".$document->link));
                $document->delete();
            }
            else $document->delete();
            return response()->json(['status' => true, 'message' => 'Successfully removed file']);
        }
        return response()->json(['status' => false, 'message' => 'Document was already used from a transaction.']);
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

    public function viewLang($id){
        $file_link = Document::where('id', $id)->first();
        $file = File::get(storage_path("app/".$file_link->link));
        $type = File::mimeType(storage_path("app/".$file_link->link));
        $url = Storage::path("app/".$file_link->link);
        return response()->json(['url' => $url, 'type' => $type]);
//        $response = Response::make($file, 200);
//        $response->header("Content-Type", $type);
//        return $response;
    }
    public function view($id){
//        $data = Document::where('id', $id)->first();
//        return view('view_file', compact('data'));
        $file_link = Document::where('id', $id)->first();
        $file = File::get(storage_path("app/".$file_link->link));
        $type = File::mimeType(storage_path("app/".$file_link->link));
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        $url = Storage::path("app/".$file_link->link);
        return view('view_file', compact('url'));
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

    public function showAllDocument(){
        return response()->json(Document::all());
    }

    public function deleteDoc($id){
        $document = Document::where('id', $id);
        $document->delete();
    }

    public function deleteCon($id){
        $container = DocumentContainer::where('id', $id);
        $container->delete();
    }

    public function showAllDocumentPerCampus($id){
        $collection = new Collection();
        $campus_offices = CampusOffice::where('campus_id', $id)->get();
        foreach ($campus_offices as $campus_office){
            $containers = DocumentContainer::where('office_id', $campus_office->office_id)->get();
            foreach($containers as $container){
                $documents = Document::where('container_id', $container->id)->get();
                foreach ($documents as $document) {
                    $collection->push([
                        'id' => $document->id,
                        'document_name' => $document->document_name,
                        'link' => $document->link,
                        'type' => $document->type,
                        'uploader_id' => $document->uploader_id,
                        'updated_at' => $document->updated_at,
                    ]);
                }
            }
        }
        return response()->json(['documents' =>$collection, 'count' => $collection->count()]);
    }
}
