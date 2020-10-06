<?php

namespace App\Http\Controllers;

use App\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampusController extends Controller
{
    public function addCampus(request $request)
    {
        $validator = Validator::make($request->all(), [
            'institution_name' => 'required',
            'campus_name' => 'required',
            'address' => 'required',
            'email' => 'required',
            'contact_no' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $campus = new Campus();
        $campus->institution_id = $request->institution_id;
        $campus->campus_name = $request->campus_name;
        $campus->address = $request->address;
        $campus->email = $request->email;
        $campus->contact_no = $request->contact_no;
        $campus->save();

        return response()->json(['status' => true, 'message' => 'Successfully created campus']);
    }
}
