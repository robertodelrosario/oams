<?php

namespace App\Http\Controllers\API;

use App\CampusUser;
use App\Http\Controllers\Controller;
use App\Office;
use App\OfficeUser;
use App\Role;
use App\User;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class UserRoleController extends Controller
{
    public function setRole(request $request,$userID){
        $role = Role::where('role', $request->role)->first();
        $users = OfficeUser::where('office_id',  $request->office_id)->get();
        if(count($users) > 0) {
            if ($role->id == 3) {
                $users = OfficeUser::where('office_id', $request->office_id)->get();
                foreach ($users as $user) {
                    $user_role = UserRole::where('id', $user->user_role_id)->first();
                    if ($user_role->role_id == 3) {
                        $role = Role::where('role', 'support staff')->first();
                        break;
                    }
                }
            } elseif ($role->id == 11) {
                $users = OfficeUser::where('office_id', $request->office_id)->get();
                foreach ($users as $user) {
                    $user_role = UserRole::where('id', $user->user_role_id)->first();
                    if ($user_role->role_id == 11) {
                        $office = Office::where('id', $request->office_id)->first();
                        return response()->json(['status' => false, 'message' => 'Office ' . $office->name . ' has already a College Task Force Head.']);
                    }
                }
            } elseif ($role->id == 2) {
                $users = OfficeUser::where('office_id', $request->office_id)->get();
                foreach ($users as $user) {
                    $user_role = UserRole::where('id', $user->user_role_id)->first();
                    if ($user_role->role_id == 2) {
                        $role = Role::where('role', 'accreditation task force')->first();
                        break;
                    }
                }
            } elseif ($role->id == 5){
                $users = OfficeUser::where('office_id', $request->office_id)->get();
                foreach ($users as $user) {
                    $user_role = UserRole::where('id', $user->user_role_id)->first();
                    if ($user_role->role_id == 5) {
                        $role = Role::where('role', 'QA staff')->first();
                        break;
                    }
                }
            }
        }
        $check = UserRole::where([
            ['user_id', $userID], ['role_id', $role->id]
        ])->first();
        if (is_null($check)){
            $user = User::where('id', $userID)->first();
            if(is_null($user)) return response()->json(['status' => false, 'message' => 'Profile not found']);
            $userRole = new UserRole();
            $userRole->user_id = $userID;
            $userRole->role_id = $role->id;
            $userRole->save();

            if($role->id == 1 || $role->id == 2 || $role->id == 3 || $role->id == 4 || $role->id == 11 || $role->id == 5 || $role->id == 6) {
                $office_user = new OfficeUser();
                $office_user->user_role_id = $userRole->id;
                $office_user->office_id = $request->office_id;
                $office_user->save();
            }
            return response()->json(['status' => true, 'message' => 'Role successfully added to User']);
        }
        $check_office = OfficeUser::where([
            ['user_role_id', $check->id], ['office_id',$request->office_id]
        ])->first();
        if(is_null($check_office)){
            $office_user = new OfficeUser();
            $office_user->user_role_id = $check->id;
            $office_user->office_id = $request->office_id;
            $office_user->save();
            return response()->json(['status' => true, 'message' => 'Office added to User']);
        }
        return response()->json(['status' => false, 'message' => 'Role already added to User']);
    }

    public function deleteSetRole($userID, $roleID, $officeID){
        if($roleID == 5){
            $campus_user = CampusUser::where('user_id', $userID)->first();
            $users = CampusUser::where('campus_id', $campus_user->campus_id)->get();
            $count = 0;
            foreach ($users as $user){
                $role = UserRole::where([
                    ['user_id', $user->user_id], ['role_id', 5]
                ])->first();
                if(!(is_null($role))) $count++;
            }
            if($count == 1) return response()->json(['status' => false, 'message' => 'Cannot delete role. Need to assign another QA Director first.']);
        }
        $role = UserRole::where([
            ['user_id', $userID], ['role_id', $roleID]
        ])->first();
        $office_users = OfficeUser::where('user_role_id', $role->id)->get();
        $count = count($office_users);
        foreach ($office_users as $office_user){
            if($office_user->office_id == $officeID){
                $office_user->delete();
            }
        }
        if($count == 1) $role->delete();
        return response()->json(['status' => true, 'message' => 'Successfully remove role']);
    }

    public function addRole(request $request){
        $check = Role::where('role', $request->role)->first();
        if(is_null($check)){
            $role = new Role();
            $role->role = $request->role;
            $role->save();
            return response()->json(['status' => true, 'message' => 'Successfully added role']);
        }
        return response()->json(['status' => false, 'message' => 'role already added']);
    }

    public function showRole(){
        return response()->json(Role::all());
    }
    public function deleteRole($id){
        $role = Role::where('id', $id);
        $role->delete();
        return response()->json(['status' => true, 'message' => 'Deleted role']);
    }

    public function editRole(request $request, $id){
        $role = Role::where('id', $id)->first();
        $role->role = $request->role;
        $role->save();
        return response()->json(['status' => true, 'message' => 'Successfully edited role']);
    }

    public function deleteOtherRole($userID, $roleID){
        $roles = UserRole::where([
            ['user_id', $userID], ['role_id', $roleID]
        ])->get();
        foreach ($roles as $role) $role->delete();
    }

    public function deleteUserRole($id){
        $role = UserRole::where('id', $id)->first();
        $role->delete();
    }
}
