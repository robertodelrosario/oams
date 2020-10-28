<?php

namespace App\Http\Controllers\API;

use App\AreaInstrument;
use App\InstrumentParameter;
use App\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ParameterController extends Controller
{
   /* public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login', 'register', 'me']]);
    }*/

    //CREATE PARAMETER FUNCTION
    public function createParameter(request $request){

        $validator = Validator::make($request->all(), [
            'parameter' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $check = Parameter::where('parameter', $request->parameter)->first();

        if(is_null($check)){
            $parameter = new Parameter();
            $parameter->parameter = $request->parameter;
            $parameter->save();

            $instrument= AreaInstrument::where('id',$request->area_instrument_id)->first();
            $parameter->areaInstruments()->attach($instrument);
            return response()->json(['status' => true, 'message' => 'Successfully created parameter!', 'parameter' => $parameter]);
        }

//        $parameter = new Parameter();
//        $parameter->parameter = $request->parameter;
//        $parameter->save();
//        $instrument= AreaInstrument::where('id',$request->area_instrument_id)->first();
//        $parameter->areaInstruments()->attach($instrument);
//        return response()->json(['status' => true, 'message' => 'Successfully created parameter!', 'parameter' => $parameter]);

        $instrument= AreaInstrument::where('id',$request->area_instrument_id)->first();
        $instrumentParameter = new InstrumentParameter();
        $test = InstrumentParameter::where([
            ['area_instrument_id', $instrument->id],['parameter_id', $check->id]
        ])->first();

        if (is_null($test)){
            $instrumentParameter->area_instrument_id = $instrument->id;
            $instrumentParameter->parameter_id = $check->id;
            $instrumentParameter->save();
            return response()->json(['status' => true, 'message' => 'Successfully created parameter!', 'parameter' => $check]);
        }
        return response()->json(['status' => false, 'message' => 'Parameter already exist!']);
    }

    public function showParameter($id){
        $parameter = DB::table('instruments_parameters')
            ->join('parameters', 'instruments_parameters.parameter_id','=','parameters.id')
            ->where('instruments_parameters.area_instrument_id', $id)
            ->get();
        return response()->json($parameter);
    }

    public function deleteParameter($id){
        $parameter = Parameter::where('id', $id);
        $parameter->delete();
        return response()->json(['status' => true, 'message' => 'Parameter successfully deleted!']);
    }

    public function editParameter(request $request, $id){
        $parameter = Parameter::where('id', $id)->first();
        $parameter->parameter = $request->parameter;
        $parameter->save();
        return response()->json(['status' => true, 'message' => 'Parameter successfully updated!']);
    }
}
