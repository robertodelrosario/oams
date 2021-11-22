<?php

namespace App\Http\Controllers\API;

use App\BestPracticeDocument;
use App\BestPracticeOffice;
use App\BestPracticeTag;
use App\Campus;
use App\CampusOffice;
use App\CampusUser;
use App\Document;
use App\DocumentContainer;
use App\Http\Controllers\Controller;
use App\Office;
use App\OfficeUser;
use App\Program;
use App\SUC;
use App\User;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpParser\Comment\Doc;
use Illuminate\Support\Collection;

class OfficeController extends Controller
{
    public function createOffice(request $request, $id){
        $validator = Validator::make($request->all(), [
            'office_name' => 'required',
            'email' => 'required',
            'contact' => 'required',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        if($request->type){
            $office = Office::where([
                ['office_name', $request->office_name], ['type', 'system-wide']
                ])->first();
            if(is_null($office)){
                $office = new Office();
                $office->office_name = $request->office_name;
                $office->email = $request->email;
                $office->contact = $request->contact;
                $office->campus_id = $id;
                $office->type = 'system-wide';
                if($request->office_id != null) $office->parent_office_id = $request->office_id;
                else $office->parent_office_id == null;
                $office->save();

                $campus = Campus::where('id', $id)->first();
                $suc = SUC::where('id', $campus->suc_id)->first();
                $campuses = Campus::where('suc_id', $suc->id)->get();
                foreach ($campuses as $campus) {
                    $cam_off = new CampusOffice();
                    $cam_off->office_id = $office->id;
                    $cam_off->campus_id = $campus->id;
                    $cam_off->save();
                }
                return response()->json(['status' => true, 'message' => 'Successfully created office [1]', 'office' => $office]);
            }
            else{
                $check = CampusOffice::where([
                    ['office_id', $office->id], ['campus_id',$id]
                ])->first();
                if(!(is_null($check))) return response()->json(['status' => false, 'message' => 'Already exist!']);

                $campus = Campus::where('id', $id)->first();
                $suc = SUC::where('id', $campus->suc_id)->first();
                $campus_offices = CampusOffice::where('office_id', $office->id)->get();

                foreach ($campus_offices as $campus_office){
                    $campus_temp = Campus::where('id', $campus_office->campus_id)->first();
                    $suc_temp = SUC::where('id', $campus_temp->suc_id)->first();
                    if($suc->id == $suc_temp->id){
                        $cam_off = new CampusOffice();
                        $cam_off->office_id = $office->id;
                        $cam_off->campus_id = $id;
                        $cam_off->save();
                        return response()->json(['status' => true, 'message' => 'Successfully created office [2]', 'office' => $office]);
                    }
                }
                return response()->json(['status' => false, 'message' => 'Unsuccessfully created office [3]']);
            }
        }
        else{
            $office = new Office();
            $office->office_name = $request->office_name;
            $office->email = $request->email;
            $office->contact = $request->contact;
            $office->campus_id = $id;
            if($request->office_id != null) $office->parent_office_id = $request->office_id;
            else $office->parent_office_id == null;
            $office->save();

            $cam_off = new CampusOffice();
            $cam_off->office_id = $office->id;
            $cam_off->campus_id = $id;
            $cam_off->save();
            return response()->json(['status' => true, 'message' => 'Successfully created office [4]', 'office' => $office]);
        }
//        $office = Office::where('office_name', $request->office_name)->first();
//        if(is_null($office)){
//            $office = new Office();
//            $office->office_name = $request->office_name;
//            $office->email = $request->email;
//            $office->contact = $request->contact;
//            $office->campus_id = $id;
//            if($request->office_id != null) $office->parent_office_id = $request->office_id;
//            else $office->parent_office_id == null;
//            $office->save();
//
//            $cam_off = new CampusOffice();
//            $cam_off->office_id = $office->id;
//            $cam_off->campus_id = $id;
//            $cam_off->save();
//            return response()->json(['status' => true, 'message' => 'Successfully created office [1]', 'office' => $office]);
//        }
//        else{
//            $campus_office = CampusOffice::where([
//                ['campus_id', $id], ['office_id', $office->id]
//            ])->first();
//            if(is_null($campus_office)){
//                $cam_off = new CampusOffice();
//                $cam_off->office_id = $office->id;
//                $cam_off->campus_id = $id;
//                $cam_off->save();
//                return response()->json(['status' => true, 'message' => 'Successfully created office [2]', 'office' => $office]);
//            }
//            return response()->json(['status' => false, 'message' => 'Office already exist!']);
//        }
    }

    public function showOffice($id){
        $collection = new Collection();
        $campus_offices = CampusOffice::where('campus_id', $id)->get();
        foreach($campus_offices as $campus_office){
            $office = Office::where('id', $campus_office->office_id)->first();
            if ($office->parent_office_id != null){
                $parent_office = Office::where('id', $office->parent_office_id)->first();
                $parent_office_id = $office->parent_office_id;
                $office_name = $parent_office->office_name;
            }
            else{
                $parent_office_id = null;
                $office_name = null;
            }
            $office_users = OfficeUser::where('office_id', $campus_office->office_id)->get();
            foreach ($office_users as $office_user){
                $user_role = UserRole::where('id', $office_user->user_role_id)->first();
                if($user_role->role_id == 3){
                    $user_credentials = User::where('id', $user_role->user_id)->first();
                    if(!($collection->contains('id', $office->id))) {
                        $collection->push([
                            'id' => $office->id,
                            'office_name' => $office->office_name,
                            'contact' => $office->contact,
                            'email' => $office->email,
                            'parent_office_id' => $parent_office_id,
                            'type' => $office->type,
                            'parent_office_name' => $office_name,
                            'user_id' => $user_credentials->id,
                            'first_name' => $user_credentials->first_name,
                            'last_name' => $user_credentials->last_name
                        ]);
                    }
                }
            }
            if(!($collection->contains('id', $office->id))){
                $collection->push([
                    'id' => $office->id,
                    'office_name'=> $office->office_name,
                    'contact' => $office->contact,
                    'email' => $office->email,
                    'parent_office_id' => $parent_office_id,
                    'parent_office_name' => $office_name,
                    'user_id' => null,
                    'first_name' => null,
                    'last_name'=> null
                ]);
            }
        }
//        foreach ($offices as $o){
//            $campus_users = CampusUser::where('office_id', $o->office_id)->get();
//            $office = Office::where('id', $o->office_id)->first();
//            if ($office->parent_office_id != null){
//                $parent_office = Office::where('id', $office->parent_office_id)->first();
//                $parent_office_id = $office->parent_office_id;
//                $office_name = $parent_office->office_name;
//            }
//            else{
//                $parent_office_id = null;
//                $office_name = null;
//            }
//            foreach($campus_users as $campus_user){
//                $user = UserRole::where([
//                    ['user_id', $campus_user->user_id], ['role_id', 3]
//                ])->first();
//                if(!(is_null($user))){
//                    $user_credentials = User::where('id', $user->user_id)->first();
//                    $collection->push([
//                        'id' => $office->id,
//                        'office_name'=> $office->office_name,
//                        'contact' => $office->contact,
//                        'email' => $office->email,
//                        'parent_office_id' => $parent_office_id,
//                        'parent_office_name' => $office_name,
//                        'user_id' => $user->user_id,
//                        'first_name' => $user_credentials->first_name,
//                        'last_name'=> $user_credentials->last_name
//                    ]);
//                }
//            }
//            if(!($collection->contains('id', $office->id))){
//                $collection->push([
//                    'id' => $office->id,
//                    'office_name'=> $office->office_name,
//                    'contact' => $office->contact,
//                    'email' => $office->email,
//                    'parent_office_id' => $parent_office_id,
//                    'parent_office_name' => $office_name,
//                    'user_id' => null,
//                    'first_name' => null,
//                    'last_name'=> null]);
//            }
//        }

        return response()->json(['office' => $collection]);
    }

    public function deleteOffice($id){
        $office = Office::where('id', $id)->first();
        $containers = DocumentContainer::where('office_id', $office->id)->get();
        foreach ($containers as $container){
            $docs = Document::where('container_id', $container->id)->get();{
                if(count($docs) > 0) return response()->json(['status' => false, 'message' => 'Office contains document/s.']);
            }
        }
        $campus_users = CampusUser::where('office_id',$id)->get();
        foreach ($campus_users as $campus_user){
            $campus_user->office_id = null;
            $campus_user->save();
        }
        $office->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted!']);
    }

    public function editOffice(request $request, $id){
        $office = Office::where('id', $id)->first();
        $office->office_name = $request->office_name;
        $office->email = $request->email;
        $office->contact = $request->contact;
        if($request->type){
         $office->type = 'system-wide';
        }
        if($request->office_id != null) $office->parent_office_id = $request->office_id;
        $office->save();
        return response()->json(['status' => true, 'message' => 'Successfully Edited!', 'office' => $office]);
    }

    public function showDepartment($id){
        $offices = Office::where('parent_office_id', $id)->get();
        return response()->json($offices);
    }

    public function transferOffice($id){
        $offices = Office::where('campus_id', $id)->get();
        foreach ($offices as $office){
            $campus_office = CampusOffice::where([
                ['campus_id', $id], ['office_id', $office->id]
            ])->first();
            if(is_null($campus_office)) {
                $campus_office = new CampusOffice();
                $campus_office->office_id = $office->id;
                $campus_office->campus_id = $id;
                $campus_office->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully transfered offices']);
    }

    public function showAllOffice(){
        return response()->json(Office::all());
    }

    public function addBestPractice(request $request, $id){

        $best_practices = $request->best_practices;
        foreach ($best_practices as $best_practice) {
            $best_prac = new BestPracticeOffice();
            $best_prac->best_practice = $best_practice['best_practice'];
            $best_prac->office_id = $id;
            $best_prac->user_id = auth()->user()->id;
            $best_prac->save();

            foreach($best_practice['tags'] as $tag){
                $best_practice_tag = new BestPracticeTag();
                $best_practice_tag->best_practice_office_id = $best_prac->id;
                $best_practice_tag->tag = $tag;
                $best_practice_tag->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully added best practice']);
    }

    public function editBestPractice(request $request, $id){
        $best_practice = BestPracticeOffice::where('id', $id)->first();
        $best_practice->best_practice = $request->best_practice;
        $best_practice->user_id = auth()->user()->id;
        $best_practice->save();
        return response()->json(['status' => true, 'message' => 'Successfully edited best practice']);
    }

    public function deleteBestPractice($id){
        $best_practice = BestPracticeOffice::where('id', $id)->first();
        $best_practice->delete();
        return response()->json(['status' => true, 'message' => 'Successfully removed best practice']);
    }

    public function showBestPractice($id){
        $collection = new Collection();
        $best_practices = BestPracticeOffice::where('office_id', $id)->get();
        foreach ($best_practices as $best_practice){
            $user = User::where('id', $best_practice->user_id)->first();
            $best_practice_documents = BestPracticeDocument::where('best_practice_office_id', $best_practice->id)->get();
            $document_collection = new Collection();
            foreach ($best_practice_documents as $best_practice_document){
                $document = Document::where('id', $best_practice_document->document_id)->first();
                $document_collection->push([
                    'best_practice_document_id' => $best_practice_document->id,
                    'document_id' => $best_practice_document->document_id,
                    'document_name' => $document->document_name,
                    'document_name' => $document->document_name,
                    'link' => $document->link,
                    'type' => $document->type,
                ]);
            }
            $best_practice_tags = BestPracticeTag::where('best_practice_office_id', $best_practice->id)->get();
            $collection->push([
                'best_practice_id' =>  $best_practice->id,
                'best_practice' =>  $best_practice->best_practice,
                'office_id' =>  $best_practice->office_id,
                'user_id' =>  $user->id,
                'first_name' =>  $user->first_name,
                'last_name' =>  $user->last_name,
                'updated_at' => $best_practice->updated_at,
                'files' => $document_collection,
                'tags' => $best_practice_tags
            ]);
        }
        return response()->json(['best_practices' => $collection]);
    }

    public function attachDocument($practice_office_id, $document_id){
        $best_practice_document = BestPracticeDocument::where([
            ['document_id', $document_id], ['best_practice_office_id', $practice_office_id]
        ])->first();
        if(is_null($best_practice_document)) {
            $best_practice_document = new BestPracticeDocument();
            $best_practice_document->document_id = $document_id;
            $best_practice_document->best_practice_office_id = $practice_office_id;
            $best_practice_document->save();
            return response()->json(['status' => true, 'message' => 'Successfully attached document to best practice']);
        }
        else return response()->json(['status' => false, 'message' => 'Document was already attached.']);
    }

    public function removeAttachDocument($id){
        $best_practice_document = BestPracticeDocument::where('id', $id)->first();
        $best_practice_document->delete();
        return response()->json(['status' => true, 'message' => 'Successfully removed attached document from best practice']);
    }

    public function addTag(request $request, $id){
        foreach ($request->tags as $tag){
            $best_practice_tag = new BestPracticeTag();
            $best_practice_tag->best_practice_office_id = $id;
            $best_practice_tag->tag = $tag;
            $best_practice_tag->save();
        }
        return response()->json(['status' => true, 'message' => 'Successfully tags']);
    }

    public function removeTag($id){
        $best_practice_tag = BestPracticeTag::where('id', $id)->first();
        if(!(is_null($best_practice_tag))){
            $best_practice_tag->delete();
            return response()->json(['status' => true, 'message' => 'Successfully removed tag.']);
        }
        return response()->json(['status' => false, 'message' => 'ID does not exist.']);
    }

    public function removeOfficeFromCampus($officeID, $campusID){
        $check = CampusOffice::where([
            ['office_id', $officeID], ['campus_id',$campusID]
        ])->first();
        $success = $check->delete();
        if($success)return response()->json(['status' => true, 'message' => 'Removed']);
    }
}
