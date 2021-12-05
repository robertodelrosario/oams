<?php

namespace App\Http\Controllers\API;


use App\Campus;
use App\Http\Controllers\Controller;
use App\SUC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SUCController extends Controller
{
    /*public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login', 'register', 'me']]);
    }*/

    public function addSuc(request $request)
    {
        $validator = Validator::make($request->all(), [
            'institution_name' => 'required',
            'address' => 'required',
            'email' => 'required',
            'contact_no' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $suc = SUC::where(strtolower('institution_name'), strtolower($request->institution_name))->first();
        if(is_null($suc))
        {
            $suc = new SUC();
            $suc->institution_name = $request->institution_name;
            $suc->address = $request->address;
            $suc->email = $request->email;
            $suc->contact_no = $request->contact_no;
            if(is_null($request->suc_level)) $suc->suc_level = 'unposted';
            else $suc->suc_level = $request->suc_level;
            $suc->save();
            return response()->json(['status' => true, 'message' => 'Successfully created SUC', 'suc' => $suc]);
        }
        return response()->json(['status' => false, 'message' => 'SUC already exist!']);
    }

    public function showSuc(){
        return response()->json(SUC::all());
    }

    public function deleteSuc($id){
        $campuses = Campus::where('suc_id', $id)->get();
        if ($campuses->count() > 0) return response()->json(['status' => false, 'message' => 'SUC has an existing Campuses.']);
        $suc = SUC::where('id', $id);
        $suc->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted SUC']);
    }

    public function editSuc(request $request, $id){
        $suc = SUC::where('id', $id)->first();
        $suc->institution_name = $request->institution_name;
        $suc->address = $request->address;
        $suc->email = $request->email;
        $suc->contact_no = $request->contact_no;
        $suc->suc_level = $request->suc_level;
        $suc->save();
        return response()->json(['status' => true, 'message' => 'Successfully created SUC', 'suc' => $suc]);
    }
}
