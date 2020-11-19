<?php

namespace App\Http\Controllers\API;

use App\AssignedUser;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MSIController extends Controller
{
    public function showStatementDocument($id, $transactionID){
        $task = AssignedUser::where([
            ['transaction_id', $transactionID], ['user_id', $id]
        ])->first();
        if($task->role == 'internal accreditor' || $task->role == 'external accreditor'){
            $area = InstrumentProgram::where('id', $task->transaction_id)->first();
            $instrumentStatement = DB::table('programs_statements')
                ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
                ->join('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'programs_statements.benchmark_statement_id')
                ->join('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
                ->join('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
                ->join('instruments_scores', 'instruments_scores.item_id', '=', 'programs_statements.id')
                ->where('instruments_parameters.area_instrument_id',$area->area_instrument_id)
                ->where('programs_statements.program_instrument_id', $area->id)
                ->where('instruments_scores.assigned_user_id', $task->id)
                ->select('programs_statements.program_instrument_id', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','programs_statements.parent_statement_id', 'parameters_statements.parameter_id', 'parameters.parameter', 'instruments_scores.*')
                ->orderBy('parameters.parameter')
                ->get();

            $statementDocument = DB::table('programs_statements')
                ->join('attached_documents', 'programs_statements.id', '=', 'attached_documents.statement_id')
                ->join('dummy_documents', 'dummy_documents.id', '=', 'attached_documents.document_id')
                ->where('programs_statements.program_instrument_id', $area->id)
                ->get();

            return response()->json(['statements' => $instrumentStatement, 'documents' => $statementDocument]);
        }
        else{
            $area = InstrumentProgram::where('id', $task->transaction_id)->first();
            $instrumentStatement = DB::table('programs_statements')
                ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
                ->join('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'programs_statements.benchmark_statement_id')
                ->join('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
                ->join('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
                ->where('instruments_parameters.area_instrument_id',$area->area_instrument_id)
                ->where('programs_statements.program_instrument_id', $area->id)
                ->select('programs_statements.*', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','programs_statements.parent_statement_id', 'parameters_statements.parameter_id', 'parameters.parameter')
                ->orderBy('parameters.parameter')
                ->get();

            $statementDocument = DB::table('programs_statements')
                ->join('attached_documents', 'programs_statements.id', '=', 'attached_documents.statement_id')
                ->join('dummy_documents', 'dummy_documents.id', '=', 'attached_documents.document_id')
                ->where('programs_statements.program_instrument_id', $area->id)
                ->get();

            $remarks = DB::table('programs_statements')
                ->join('instruments_scores', 'programs_statements.id', '=', 'instruments_scores.item_id')
                ->join('assigned_users', 'assigned_users.id', '=', 'instruments_scores.assigned_user_id')
                ->join('users', 'users.id', '=', 'assigned_users.user_id')
                ->where('programs_statements.program_instrument_id', $area->id)
                ->select('programs_statements.*', 'instruments_scores.remark', 'instruments_scores.remark_type','users.name', 'users.email' ,'assigned_users.role' )
                ->orderBy('users.id')
                ->get();

            return response()->json(['statements' => $instrumentStatement, 'documents' => $statementDocument, 'remarks' => $remarks]);
        }
    }

//    public function showStatementDocument($id, $instrumentID){
//        $instrumentStatement = DB::table('programs_statements')
//            ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
//            ->join('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'programs_statements.benchmark_statement_id')
//            ->join('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
//            ->join('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
//            ->where('instruments_parameters.area_instrument_id',$instrumentID)
//            ->where('programs_statements.program_instrument_id', $id)
//            ->select('programs_statements.program_instrument_id', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','programs_statements.parent_statement_id', 'parameters_statements.parameter_id', 'parameters.parameter')
//            ->orderBy('parameters.parameter')
//            ->get();
//
//        $statementDocument = DB::table('programs_statements')
//            ->join('attached_documents', 'programs_statements.id', '=', 'attached_documents.statement_id')
//            ->join('dummy_documents', 'dummy_documents.id', '=', 'attached_documents.document_id')
//            ->where('programs_statements.program_instrument_id', $id)
//            ->get();
//        return response()->json(['statements' => $instrumentStatement, 'documents' => $statementDocument]);
//    }
}
