<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Campus;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            $campus->save();

            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->user_email;
            $user->contact_no = $request->user_contact_no;
            $user->password = bcrypt($request->input('password'));
            $user->status = 'active';
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
        $campus = Campus::where('suc_id', $id)->get();
        return response()->json($campus);
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
