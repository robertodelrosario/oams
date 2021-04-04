<?php

namespace App\Http\Controllers\API;

use App\CampusUser;
use App\Http\Controllers\Controller;
use App\Office;
use App\OfficeUser;
use App\Role;
use App\User;
use App\UserRole;
use Illuminate\Support\Collection;

class OfficeUserController extends Controller
{
    public function addToOffice($id,$role_id,$office_id){
        $user = CampusUser::where('id', $id)->first();
        $user_role = UserRole::where([
            ['user_id', $user->user_id], ['role_id', $role_id]
        ])->first();
        if(is_null($user_role)){
            $user_role = new UserRole();
            $user_role->user_id = $user->user_id;
            $user_role->role_id = $role_id;
            $user_role->save();

            $office_user = new OfficeUser();
            $office_user->user_role_id = $user_role->id;
            $office_user->office_id = $office_id;
            $office_user->save();

            $office = Office::where('id', $office_id)->first();
            $role = Role::where('id', $role_id)->first();
            $collection = new Collection();
            $collection->push([
                'id' => $user->id,
                'user_id' => $user->user_id,
                'office_id' => $office->id,
                'office_name' => $office->office_name,
                'role_id' => $role_id,
                'role' => $role->role,
                'office_user_id' => $office_user->id
            ]);
            return response()->json(['status' => true, 'message' => 'Successfully added to office', 'office' => $collection]);
        }
        else{
            $check_office_user = OfficeUser::where([
                ['office_id', $office_id], ['user_role', $user_role->id]
            ])->first();
            if(is_null($check_office_user)){
                $office_user = new OfficeUser();
                $office_user->user_role_id = $user_role->id;
                $office_user->office_id = $office_id;
                $office_user->save();

                $office = Office::where('id', $office_id)->first();
                $role = Role::where('id', $role_id)->first();
                $collection = new Collection();
                $collection->push([
                    'id' => $user->id,
                    'user_id' => $user->user_id,
                    'office_id' => $office->id,
                    'office_name' => $office->office_name,
                    'role_id' => $role_id,
                    'role' => $role->role,
                    'office_user_id' => $office_user->id
                ]);
                return response()->json(['status' => true, 'message' => 'Successfully added to office', 'office' => $collection]);
            }
            else return response()->json(['status' => false, 'message' => 'Already added to office']);
        }
    }

    public function removeFromOffice($id){
        $office_user = OfficeUser::where('id', $id)->first();
        if (is_null($office_user)) return response()->json(['status' => false, 'message' => 'ID does not exist!']);
        else{
            $user_role = UserRole::where('id', $office_user->user_role_id);
            $office_user->delete();
            $user_role->delete();
            return response()->json(['status' => true, 'message' => 'Successfully remove from office']);
        }
    }
}
