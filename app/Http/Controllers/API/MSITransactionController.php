<?php

namespace App\Http\Controllers\API;

use App\AttachedDocument;
use App\DummyDocument;
use App\Http\Controllers\Controller;
use App\InstrumentStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MSITransactionController extends Controller
{
//    public function selectInstrument(request $request){
//        $validator = Validator::make($request->all(), [
//            'application_program' => 'required',
//            'area_instrument_id' => 'required'
//        ]);
//        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Data!']);
//
//        $check = Transaction::where([
//            ['application_program', $request->application_program], ['area_instrument_id', $request->area_instrument_id]
//        ])->first();
//        if(is_null($check)){
//            $transaction = new Transaction();
//            $transaction->application_program = $request->application_program;
//            $transaction->area_instrument_id = $request->area_instrument_id;
//            $transaction->save();
//            return response()->json(['status' => true, 'message' => 'Instrument Selected!', 'transaction' => $transaction]);
//        }
//        return response()->json(['status' => false, 'message' => 'Already selected']);
//    }

    public function attachSupportDocument(request $request){
        $statement = InstrumentStatement::where([
            ['area_instrument_id', $request->area_instrument_id], ['benchmark_statement_id', $request->benchmark_statement_id]
        ])->first();

        $check = AttachedDocument::where('statement_id', $statement->id)->first();
        if(is_null($check)){
            $supportDocument = new AttachedDocument();
            $supportDocument->statement_id = $statement->id;
            $supportDocument->document_id = $request->document_id;
            $supportDocument->save();
            return response()->json(['status' => true, 'message' => 'Successfully added document', 'document' => $supportDocument]);
        }
        return response()->json(['status' => true, 'message' => 'Already added document']);
    }

    public function removeSupportDocument(){

    }

    public function showTransactionInstrument($id){
        $instrumentStatement = DB::table('instruments_statements')
            ->leftjoin('benchmark_statements','instruments_statements.benchmark_statement_id','=', 'benchmark_statements.id')
            ->leftjoin('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'benchmark_statements.id')
            ->leftjoin('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
            ->leftjoin('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
            ->where('instruments_parameters.area_instrument_id',$id )
            ->where('instruments_statements.area_instrument_id', $id)
            ->select('instruments_statements.area_instrument_id', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','benchmark_statements.statement_parent', 'parameters_statements.parameter_id', 'parameters.parameter')
            ->orderBy('parameters.parameter')
            ->get();

        $statementDocument = DB::table('instruments_statements')
            ->join('attached_documents', 'instruments_statements.id', '=', 'attached_documents.statement_id')
            ->join('dummy_documents', 'dummy_documents.id', '=', 'attached_documents.document_id')
            ->where('instruments_statements.area_instrument_id', $id)
            ->get();
        return response()->json(['statements' => $instrumentStatement, 'documents' => $statementDocument]);
    }


    //dummy, might delete later hahaha
    public function uploadDummyDocument(request $request){
        $validator = Validator::make($request->all(), [
            'document' => 'required|mimes:doc,docx,pdf,jpg,jpeg,png|max:2048'
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Document!']);
        $dummyDocument = new DummyDocument();
        $fileName = time().'_'.$request->document->getClientOriginalName();
        $filePath = $request->file('document')->storeAs('document', $fileName, 'public');
        $dummyDocument->title = $fileName;
        $dummyDocument->location = '/storage/' . $filePath;
        $dummyDocument->save();
        return response()->json(['status' => true, 'message' => 'Successfully added document']);
    }

    public function showDummyDocument(){
        return response()->json(DummyDocument::all());
    }
}
