<?php

namespace App\Http\Controllers\API;

use App\AreaInstrument;
use App\Http\Controllers\Controller;
use App\SUC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SUCController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login', 'register', 'me']]);
    }

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
            $suc->save();
            return response()->json(['status' => true, 'message' => 'Successfully created SUC']);
        }
        return response()->json(['status' => false, 'message' => 'SUC already exist!']);
    }

    public function showSuc(){
        return response()->json(SUC::all());
    }

    public function deleteSuc($id){
        $suc = SUC::where('id', $id);
        $suc->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted SUC']);
    }
}
