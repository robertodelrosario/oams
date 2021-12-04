<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Role;
use App\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function createRole(request $request){
        $check = Role::where('role', $request->role)->first();
        if(is_null($check)){
            $role = new Role();
            $role->role = $request->role;
            $role->save();
            return response()->json(['status' => true, 'message' => 'Successfully added role.']);
        }
    }

    public function editRole(request $request, $id){
        $role = Role::where('id', $id)->first();
        $role->role = $request->role;
        $role->save();
        return response()->json(['status' => true, 'message' => 'Successfully edited role.']);
    }

    public function deleteRole($id){
        $role = Role::where('id', $id)->first();
        $role->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted role.']);
    }
}
