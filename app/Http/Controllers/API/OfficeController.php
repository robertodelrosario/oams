<?php

namespace App\Http\Controllers\API;

use App\CampusOffice;
use App\CampusUser;
use App\Document;
use App\DocumentContainer;
use App\Http\Controllers\Controller;
use App\Office;
use App\OfficeUser;
use App\Program;
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

//        $office = Office::where([
//            ['campus_id', $id], [strtolower('office_name'), strtolower($request->office_name)]
//        ])->first();
        $office = Office::where('office_name', $request->office_name)->first();
        if(is_null($office)){
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
            return response()->json(['status' => true, 'message' => 'Successfully created office [1]', 'office' => $office]);
        }
        else{
            $campus_office = CampusOffice::where([
                ['campus_id', $id], ['office_id', $office->id]
            ])->first();
            if(is_null($campus_office)){
                $cam_off = new CampusOffice();
                $cam_off->office_id = $office->id;
                $cam_off->campus_id = $id;
                $cam_off->save();
                return response()->json(['status' => true, 'message' => 'Successfully created office [2]', 'office' => $office]);
            }
            return response()->json(['status' => false, 'message' => 'Office already exist!']);
        }
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
                    $collection->push([
                        'id' => $office->id,
                        'office_name'=> $office->office_name,
                        'contact' => $office->contact,
                        'email' => $office->email,
                        'parent_office_id' => $parent_office_id,
                        'parent_office_name' => $office_name,
                        'user_id' => $user_credentials->id,
                        'first_name' => $user_credentials->first_name,
                        'last_name'=> $user_credentials->last_name
                    ]);
                }
            }
            if($collection->contains('id', $office->id)){
            }
            else{
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

    function showAllOffice(){

    }
}
