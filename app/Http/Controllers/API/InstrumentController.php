<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\AreaInstrument;
use App\InstrumentParameter;
use App\InstrumentStatement;
use App\Parameter;
use App\ParameterStatement;
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
             return response()->json(['status' => true, 'message' => 'Successfully added instrument!','instrument' => $instrument]);
         }
         return response()->json(['status' => true, 'message' => 'Instrument already exist!']);
    }

    public function showInstrument(){
        return response()->json(AreaInstrument::all());
    }

    public function deleteInstrument($id){
        $areaInstrument = AreaInstrument::where('id', $id);
        $instrumentParameters = InstrumentParameter::where('area_instrument_id', $id);
        $instrumentParameters->delete();
        $areaInstrument->delete();
        return response()->json(['status' => true, 'message' => 'Instrument successfully deleted!']);
    }

    public function editInstrument(request $request, $id){
         $areaInstrument = AreaInstrument::where('id',$id)->first();
         $areaInstrument->intended_program = $request->intended_program;
         $areaInstrument->save();
         return response()->json(['status' => true, 'message' => 'Successfully updated the instrument!']);
    }

    public function cloneInstrument(request $request,$id){
        $instrument = AreaInstrument::where('id', $id)->first();
        if(is_null($instrument)) return response()->json(['status' => false, 'message' => 'Instrument does not exist']);

        $areaInstrument = new AreaInstrument();
        $areaInstrument->intended_program = $request->intended_program;
        $areaInstrument->area_number = $instrument->area_number;
        $areaInstrument->area_name = $instrument->area_name;
        $areaInstrument->version = $instrument->version;
        $areaInstrument->save();

        $parameters = InstrumentParameter::where('area_instrument_id', $instrument->id)->get();
        foreach ($parameters as $parameter){
            $instrumentParameter = new InstrumentParameter();
            $instrumentParameter->area_instrument_id = $areaInstrument->id;
            $instrumentParameter->parameter_id = $parameter->parameter_id;
            $instrumentParameter->save();

            $statements = InstrumentStatement::where('instrument_parameter_id',$parameter->parameter_id)->get();
            foreach ($statements as $statement){
                $instrumentStatement = new InstrumentStatement();
                $instrumentStatement->instrument_parameter_id = $instrumentParameter->id;
                $instrumentStatement->benchmark_statement_id = $statement->benchmark_statement_id;
                $instrumentStatement->parent_statement_id = $statement->parent_statement_id;
                $instrumentStatement->save();
            }
        }

        return response()->json(['status' => true, 'message' => 'Successfully added instrument!','instrument' => $areaInstrument]);

    }
}
