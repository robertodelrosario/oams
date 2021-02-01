<?php

namespace App\Http\Controllers\API;

use App\AreaInstrument;
use App\InstrumentParameter;
use App\InstrumentStatement;
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
        $parameter = DB::table('parameters')
            ->join('instruments_parameters', 'instruments_parameters.parameter_id','=','parameters.id')
            ->where('instruments_parameters.area_instrument_id', $id)
            ->get();
        return response()->json($parameter);
    }

    public function deleteParameter($id){
        $parameter = InstrumentParameter::where('id', $id)->first();
        $check = InstrumentParameter::where('parameter_id', $parameter->parameter_id)->get();
        if(count($check)<=1){
            $param = Parameter::where('id', $parameter->parameter_id)->first();
            $param->delete();
        }
        $parameter->delete();
        return response()->json(['status' => true, 'message' => 'Parameter successfully deleted!']);
    }

    public function editParameter(request $request, $id){
        $parameter = InstrumentParameter::where('id', $id)->first();
        $check = Parameter::where('parameter', $request->parameter)->first();
        if(is_null($check)){
            $param = new Parameter();
            $param->parameter = $request->parameter;
            $param->save();

            $par = new InstrumentParameter();
            $par->area_instrument_id = $parameter->area_instrument_id;
            $par->parameter_id = $param->id;
            $par->save();

            $statements = InstrumentStatement::where('instrument_parameter_id', $parameter->id)->get();
            foreach ($statements as $statement){
                $instrumentStatement = new InstrumentStatement();
                $instrumentStatement->instrument_parameter_id = $par->id;
                $instrumentStatement->benchmark_statement_id = $statement->benchmark_statement_id;
                $instrumentStatement->parent_statement_id = $statement->parent_statement_id;
                $instrumentStatement->save();
            }
            $parameter->delete();
        }
        else{
            $parameter->parameter_id = $check->id;
            $parameter->save();
        }
        return response()->json(['status' => true, 'message' => 'Parameter successfully updated!']);
    }
}
