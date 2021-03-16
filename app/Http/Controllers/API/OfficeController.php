<?php

namespace App\Http\Controllers\API;

use App\CampusUser;
use App\Document;
use App\Http\Controllers\Controller;
use App\Office;
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

        $office = Office::where([
            ['campus_id', $id], [strtolower('office_name'), strtolower($request->office_name)]
        ])->first();
        if(is_null($office)){
            $office = new Office();
            $office->office_name = $request->office_name;
            $office->email = $request->email;
            $office->contact = $request->contact;
            $office->campus_id = $id;
            $office->save();
            return response()->json(['status' => true, 'message' => 'Successfully created office', 'office' => $office]);
        }
        return response()->json(['status' => false, 'message' => 'Office already exist!']);
    }

    public function showOffice($id){
        $collection = new Collection();
        $offices = Office::where('campus_id', $id)->get();
        foreach ($offices as $office){
            $campus_users = CampusUser::where('office_id', $office->id)->get();
            foreach($campus_users as $campus_user){
                $user = UserRole::where([
                    ['user_id', $campus_user->user_id], ['role_id', 3]
                ])->first();
                if(!(is_null($user))){
                    $user_credentials = User::where('id', $user->user_id)->first();
                    $collection->push(['id' => $office->id, 'office_name'=> $office->office_name, 'contact' => $office->contact, 'email' => $office->email, 'user_id' => $user->user_id, 'first_name' => $user_credentials->first_name, 'last_name'=> $user_credentials->last_name]);
                }
            }
            if(!($collection->contains('id', $office->id))){
                $collection->push(['id' => $office->id, 'office_name'=> $office->office_name, 'contact' => $office->contact, 'email' => $office->email, 'user_id' => null, 'first_name' => null, 'last_name'=> null]);
            }
        }

        return response()->json(['office' => $collection]);
    }

    public function deleteOffice($id){
        $office = Office::where('id', $id)->first();
        $docs = Document::where('office_id', $office->id)->get();
        if(count($docs) > 0) return response()->json(['status' => false, 'message' => 'Office contains document/s.']);
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
        $office->save();
        return response()->json(['status' => true, 'message' => 'Successfully Edited!', 'office' => $office]);
    }
}
