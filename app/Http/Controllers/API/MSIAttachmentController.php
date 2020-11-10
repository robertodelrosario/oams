<?php

namespace App\Http\Controllers\API;

use App\AttachedDocument;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentStatement;
use App\ProgramStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MSIAttachmentController extends Controller
{
    public function attachSupportDocument(request $request){
        $statement = ProgramStatement::where([
            ['program_instrument_id', $request->program_instrument_id], ['benchmark_statement_id', $request->benchmark_statement_id]
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

    public function removeSupportDocument($id){
        $document = AttachedDocument::where('id', $id)->get();
        $document->delete();
        return response()->json(['status' => true, 'message' => 'Successfully removed document']);
    }

    public function showStatementDocument($id, $instrumentID){
        $instrumentStatement = DB::table('programs_statements')
            ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
            ->join('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'programs_statements.benchmark_statement_id')
            ->join('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
            ->join('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
            ->where('instruments_parameters.area_instrument_id',$instrumentID)
            ->where('programs_statements.program_instrument_id', $id)
            ->select('programs_statements.program_instrument_id', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','programs_statements.parent_statement_id', 'parameters_statements.parameter_id', 'parameters.parameter')
            ->orderBy('parameters.parameter')
            ->get();

        $statementDocument = DB::table('programs_statements')
            ->join('attached_documents', 'programs_statements.id', '=', 'attached_documents.statement_id')
            ->join('dummy_documents', 'dummy_documents.id', '=', 'attached_documents.document_id')
            ->where('programs_statements.program_instrument_id', $id)
            ->get();
        return response()->json(['statements' => $instrumentStatement, 'documents' => $statementDocument]);
    }
}
