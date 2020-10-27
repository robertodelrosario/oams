<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MSITransactionController extends Controller
{
    public function selectInstrument(request $request){
        $validator = Validator::make($request->all(), [
            'application_program' => 'required',
            'area_instrument_id' => 'required'
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Data!']);

        $check = Transaction::where([
            ['application_program', $request->application_program], ['area_instrument_id', $request->area_instrument_id]
        ])->first();
        if(is_null($check)){
            $transaction = new Transaction();
            $transaction->application_program = $request->application_program;
            $transaction->area_instrument_id = $request->area_instrument_id;
            $transaction->save();
            return response()->json(['status' => true, 'message' => 'Instrument Selected!', 'transaction' => $transaction]);
        }
        return response()->json(['status' => false, 'message' => 'Already selected']);
    }

    public function showInstrumentList($id){

    }
}
