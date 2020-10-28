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
        $instrumentParameters = InstrumentParameter::where('area_instrument_id', $id)->get();

        foreach ($instrumentParameters as $instrumentParameter){
            $parameter = Parameter::where('id', $instrumentParameter->parameter_id);
            $parameter->delete();
        }
        $areaInstrument->delete();
        return response()->json(['status' => true, 'message' => 'Instrument successfully deleted!']);
    }

    public function editInstrument(request $request, $id){
         $areaInstrument = AreaInstrument::where('id',$id)->first();
         $areaInstrument->intended_program = $request->intended_program;
         $areaInstrument->save();
         return response()->json(['status' => true, 'message' => 'Successfully updated the instrument!']);
    }

    public function cloneInstrument(request $request){
        $validator = Validator::make($request->all(), [
            'new_intended_program' => 'required',
            'intended_program' => 'required',
            'area_number' => 'required',
            'area_name' => 'required',
            'version' => 'required',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process instrument. Required data']);
        $test = AreaInstrument::where([
            ['intended_program', $request->new_intended_program], ['area_number', $request->area_number]
        ])->first();
        if(is_null($test)){
            $areaInstrument = new AreaInstrument();
            $areaInstrument->intended_program = $request->new_intended_program;
            $areaInstrument->area_number = $request->area_number;
            $areaInstrument->area_name = $request->area_name;
            $areaInstrument->version = $request->version;
            $areaInstrument->save();

            $instrument = AreaInstrument::where([
               ['intended_program', $request->intended_program], ['area_number', $request->area_number]
            ])->first();

            $new_instrument = AreaInstrument::where([
                ['intended_program', $request->new_intended_program], ['area_number', $request->area_number]
            ])->first();

//            $instrumentParameters = InstrumentParameter::where('area_instrument_id', $instrument->id)->get();

//            foreach($instrumentParameters as $instrumentParameter){
//                $param = Parameter::where('id', $instrumentParameter->parameter_id)->first();
//                $parameter = new Parameter();
//                $parameter->parameter = $param->parameter;
//                $parameter->save();
//                $instrument= AreaInstrument::where('id',$instrument->id)->first();
//                $parameter->areaInstruments()->attach($new_instrument);
//
//                $parameterStatements = ParameterStatement::where('parameter_id', $instrumentParameter->parameter_id)->get();
//                foreach ($parameterStatements as $parameterStatement){
//                    $paramState = new ParameterStatement();
//                    $paramState->parameter_id = $parameter->id;
//                    $paramState->benchmark_statement_id = $parameterStatement->benchmark_statement_id;
//                    $paramState->save();
//                }
//
//            }
//            $instrumentParameters = InstrumentParameter::where('area_instrument_id', $instrumentID->id)->get();
//            foreach ($instrumentParameters as $instrumentParameter){
//                $parameterStatements = ParameterStatement::where('parameter_id', $instrumentParameter->parameter_id)->get();
//                foreach ($parameterStatements as $parameterStatement){
//                    $paramState = new ParameterStatement();
//                    $paramState->parameter_id = $parameterStatement->parameter_id;
//                    $paramState->benchmark_statement_id = $parameterStatement->benchmark_statement_id;
//                    $paramState->save();
//                }
//            }

            $instrumentStatements = InstrumentStatement::where('area_instrument_id', $instrument->id)->get();
            foreach ($instrumentStatements as $instrumentStatement){
                $instruState = new InstrumentStatement();
                $instruState->area_instrument_id = $new_instrument->id;
                $instruState->benchmark_statement_id = $instrumentStatement->benchmark_statement_id;
                $instruState->save();
            }

            $instrumentParameters = InstrumentParameter::where('area_instrument_id', $instrument->id)->get();
            $instrumentID = AreaInstrument::where('intended_program', $areaInstrument->intended_program)
                ->where('area_number', $areaInstrument->area_number)->first();
            foreach($instrumentParameters as $instrumentParameter){
                $instruParam = new InstrumentParameter();
                $instruParam->area_instrument_id = $instrumentID->id;
                $instruParam->parameter_id = $instrumentParameter->parameter_id;
                $instruParam->save();
            }
            $instrument = AreaInstrument::where('intended_program', $areaInstrument->intended_program)
                ->where('area_number', $areaInstrument->area_number)->first();
            return response()->json(['status' => true, 'message' => 'Successfully added instrument!','instrument' => $instrument]);
        }
        return response()->json(['status' => true, 'message' => 'Instrument already exist!']);
    }
}
