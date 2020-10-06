<?php

namespace App\Http\Controllers;

use App\AreaInstrument;
use App\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstrumentController extends Controller
{

    public function createStatement(request $request)
    {
        $validator = Validator::make($request->all(), [
            'statement' => 'required',
            'type' => 'required',
            'statement_parent' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        return 'hi';
    }

    public function createParameter(request $request){
        $validator = Validator::make($request->all(), [
            'parameter' => 'required',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $parameter = new Parameter();
        $parameter->parameter = $request->parameter;
        $parameter->save();
        return response()->json(['status' => true, 'message' => 'Successfully created parameter!']);
    }
}
