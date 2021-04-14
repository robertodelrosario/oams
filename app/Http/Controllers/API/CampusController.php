<?php

namespace App\Http\Controllers\API;

use App\CampusUser;
use App\Http\Controllers\Controller;
use App\Campus;
use App\OfficeUser;
use App\Role;
use App\User;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;

class CampusController extends Controller
{
    public function addCampus(request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'campus_name' => 'required',
            'address' => 'required',
            'region' => 'required',
            'email' => 'required',
            'contact_no' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $campus = Campus::where([
            ['suc_id', $id], [strtolower('campus_name'), strtolower($request->campus_name)]
        ])->first();
        if(is_null($campus))
        {
            $campus = new Campus();
            $campus->suc_id = $id;
            $campus->campus_name = $request->campus_name;
            $campus->address = $request->address;
            $campus->region = $request->region;
            $campus->province = $request->province;
            $campus->municipality = $request->municipality;
            $campus->email = $request->email;
            $campus->contact_no = $request->contact_no;


            $check = User::where('email', $request->email)->first();
            if(!(is_null($check))){
                $campus->save();
                return response()->json(['status' => false, 'message' => 'User already exist', 'suc' => $campus]);
            }
            $check = User::where([
                ['first_name', $request->first_name],['last_name', $request->last_name], ['name_extension', $request->name_extension]
            ])->first();
            if(!(is_null($check))){
                $campus->save();
                return response()->json(['status' => false, 'message' => 'User already exist', 'suc' => $campus]);
            }
            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->name_extension = $request->name_extension;
            $user->email = $request->user_email;
            $user->contact_no = $request->user_contact_no;
            $user->password = bcrypt($request->input('password'));
            $user->status = 'active';
            $campus->save();
            $user->save();
            $campus= Campus::where('id',$campus->id)->first();
            $user->campuses()->attach($campus);
            $role = Role::where('role', 'QA director')->first();
            $role->users()->attach($user->id);

            return response()->json(['status' => true, 'message' => 'Successfully created campus', 'suc' => $campus]);
        }
        return response()->json(['status' => false, 'message' => 'SUC already exist!']);
    }

    public function showCampus($id){
        $collection = new Collection();
        $campuses = Campus::where('suc_id', $id)->get();
        foreach($campuses as $campus){
            $users = CampusUser::where('campus_id', $campus->id)->get();
            foreach($users as $user){
                $role = UserRole::where([
                    ['user_id', $user->user_id], ['role_id', 5]
                ])->first();
                if(!(is_null($role))){
                    $u = User::where('id', $user->user_id)->first();
                    $collection->push([
                        'id' => $campus->id,
                        'suc_id' => $campus->suc_id,
                        'campus_name' => $campus->campus_name,
                        'address' => $campus->address,
                        'region' => $campus->region,
                        'province' => $campus->province,
                        'municipality' => $campus->municipality,
                        'email' => $campus->email,
                        'contact_no' => $campus->contact_no,
                        'created_at' => $campus->created_at,
                        'created_at' => $campus->created_at,
                        'first_name' => $u->first_name,
                        'last_name' =>$u->last_name,
                        'user_email' =>$u->email
                    ]);
                }
            }
            if(!($collection->contains('id', $campus->id))){
                $collection->push([
                    'id' => $campus->id,
                    'suc_id' => $campus->suc_id,
                    'campus_name' => $campus->campus_name,
                    'address' => $campus->address,
                    'region' => $campus->region,
                    'province' => $campus->province,
                    'municipality' => $campus->municipality,
                    'email' => $campus->email,
                    'contact_no' => $campus->contact_no,
                    'created_at' => $campus->created_at,
                    'created_at' => $campus->created_at,
                    'first_name' => null,
                    'last_name' =>null,
                    'user_email' =>null
                ]);
            }
        }
        return response()->json($collection);
    }

    public function showAllCampus(){
        return response()->json(Campus::all());
    }

    public function deleteCampus($id){
        $campus = Campus::where('id', $id);
        $campus->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted campus']);
    }

    public function editCampus(request $request, $id){
        $campus = Campus::where('id', $id)->first();
        $campus->campus_name = $request->campus_name;
        $campus->address = $request->address;
        $campus->region = $request->region;
        $campus->email = $request->email;
        $campus->region = $request->region;
        $campus->province = $request->province;
        $campus->municipality = $request->municipality;
        $campus->contact_no = $request->contact_no;
        $campus->save();
        return response()->json(['status' => true, 'message' => 'Successfully edited campus', 'suc' => $campus]);
    }

    public function registerQA(request $request, $id){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'contact_no' => 'required',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails())
            return response()->json(['status' => false, 'message' => 'Invalid value inputs!'], 254);

        $check = User::where('email', $request->email)->first();
        if(is_null($check)){
            $check = User::where([
                ['first_name', $request->first_name],['last_name', $request->last_name], ['name_extension', $request->name_extension]
            ])->first();
            if(is_null($check)){
                $user = new User;
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->contact_no = $request->contact_no;
                $user->password = bcrypt($request->input('password'));
                $user->status = 'active';
                $user->middle_initial = $request->middle_initial;
                $user->name_extension = $request->name_extension;
                $user->save();
                $campus= Campus::where('id',$id)->first();
                $user->campuses()->attach($campus);
                $role = Role::where('id', 5)->first();
                $role->users()->attach($user->id);
                $user_office = CampusUser::where('user_id', $user->id)->first();
                $user_office->office_id = $request->office_id;
                $user_office->save();
                return response()->json(['status' => true, 'message' => 'Successfully added to User', 'user' => $user]);
            }

            else{
                $campus_user = CampusUser::where('user_id', $check->id)->first();
                if(is_null($campus_user)){
                    $assign_role = new UserRole();
                    $assign_role->user_id = $check->id;
                    $assign_role->role_id = 5;
                    $assign_role->save();
                    return response()->json(['status' => true, 'message' => 'Successfully assigned as QA Director', 'user' => $check]);
                }
                else{
                    return response()->json(['status' => false, 'message' => 'User is already registered to other campus']);
                }
            }

        }
        else{
            $campus_user = CampusUser::where('user_id', $check->id)->first();
            if(is_null($campus_user)){
                $assign_role = new UserRole();
                $assign_role->user_id = $check->id;
                $assign_role->role_id = 5;
                $assign_role->save();
                return response()->json(['status' => true, 'message' => 'Successfully assigned as QA Director', 'user' => $check]);
            }
            else{
               return response()->json(['status' => false, 'message' => 'User is already registered to other campus']);
            }
        }
    }
    public function addCampusUser(Request $request){
        $campus_user = CampusUser::where([
            ['user_id', $request->user_id], ['campus_id', $request->campus_id]
        ])->first();
        if(is_null($campus_user)){
            $campus_user = new CampusUser();
            $campus_user->campus_id = $request->campus_id;
            $campus_user->user_id = $request->user_id;
            $campus_user->save();

            $role = Role::where('role', $request->role)->first();
            $user_role = UserRole::where([
                ['role_id',$role->id], ['user_id', $request->user_id]
            ])->first();
            if(is_null($user_role)){
                $user_role = new UserRole();
                $user_role->user_id = $request->user_id;
                $user_role->role_id = $role->id;
                $user_role->save();
            }
            $office_user = OfficeUser::where([
                ['office_id', $request->office_id], ['user_role_id', $user_role->id]
            ])->first();
            if(is_null($office_user)){
                $office_user = new OfficeUser();
                $office_user->office_id = $request->office_id;
                $office_user->user_role_id = $user_role->id;
                $office_user->save();
            }
            return response()->json(['status' => true, 'message' => 'Successfully added user.']);
        }
        return response()->json(['status' => false, 'message' => 'User was already registered to this campus']);
    }
    public function showCampusUser($id){
        $collection = new Collection();
        $campus_users = CampusUser::where('campus_id', $id)->get();
        foreach($campus_users as $campus_user){
            $user = User::where('id', $campus_user->user_id)->first();
            $collection->push([
               'campus_id' =>  $campus_user->campus_id,
                'user_id' => $campus_user->user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name
            ]);
        }
        return response()->json($collection);
    }
}
