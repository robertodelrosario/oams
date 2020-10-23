<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\AreaInstrument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class InstrumentController extends Controller

{
/*
    public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login', 'register', 'me']]);
    }
*/

    //CREATE INSTRUMENT FUNTION
     public function createInstrument(request $request){
        $validator = Validator::make($request->all(), [
            'intended_program' => 'required',
            'area_number' => 'required',
            'area_name' => 'required',
            'version' => 'required',
        ]);

        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process instrument. Required data']);

        $test = AreaInstrument::where([
            ['intended_program', $request->intended_program], ['area_number', $request->area_number]
        ])->first();
         if(is_null($test)){
             $areaInstrument = new AreaInstrument();
             $areaInstrument->intended_program = $request->intended_program;
             $areaInstrument->area_number = $request->area_number;
             $areaInstrument->area_name = $request->area_name;
             $areaInstrument->version = $request->version;
             $areaInstrument->save();
             $instrument = AreaInstrument::where('intended_program', $areaInstrument->intended_program)
                ->where('area_number', $areaInstrument->area_number)->first();
             return response()->json(['status' => true, 'message' => 'Successfully added instrument!','instrument_id' => $instrument->id]);
         }
         return response()->json(['status' => true, 'message' => 'Instrument already exist!']);

    }

    public function showInstrument(){
        return response()->json(AreaInstrument::all());
    }

    public function deleteInstrument($id){
        $areaInstrument = AreaInstrument::where('id', $id);
        $areaInstrument->delete();
        return response()->json(['status' => true, 'message' => 'Instrument successfully deleted!']);
    }

}
