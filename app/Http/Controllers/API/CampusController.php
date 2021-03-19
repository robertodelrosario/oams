<?php

namespace App\Http\Controllers\API;

use App\CampusUser;
use App\Http\Controllers\Controller;
use App\Campus;
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


            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
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
                        'user_email' =>$u->user_email
                    ]);
                }
            }
        }
        return response()->json($collection);
    }

    public function showAllCampus(){
        return response()->json(Campus::all());
    }

//    public function deleteCampus($id){
//        $campus = Campus::where('id', $id);
//        $campus->delete();
//        return response()->json(['status' => true, 'message' => 'Successfully deleted campus']);
//    }

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
}
