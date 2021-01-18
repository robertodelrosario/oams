<?php

namespace App\Http\Controllers\API;

use App\AssignedUser;
use App\AttachedDocument;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MSIController extends Controller
{
    public function showStatementDocument($id, $transactionID){
        $task = AssignedUser::where([
            ['transaction_id', $transactionID], ['user_id', $id]
        ])->first();
        if($task->role == 'internal accreditor' || Str::contains($task->role, 'external accreditor')){
            $area = InstrumentProgram::where('id', $task->transaction_id)->first();
            $instrumentStatements = DB::table('programs_statements')
                ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
                ->join('parameters_programs', 'parameters_programs.id', '=', 'programs_statements.program_parameter_id')
                ->join('instruments_scores', 'instruments_scores.item_id', '=', 'programs_statements.id')
                ->where('parameters_programs.program_instrument_id', $area->id)
                ->where('instruments_scores.assigned_user_id', $task->id)
                ->select('programs_statements.id','programs_statements.program_parameter_id','parameters_programs.parameter_id','benchmark_statements.statement','benchmark_statements.type','programs_statements.parent_statement_id', 'instruments_scores.*')
                ->get();

            $attachedDocument = array();
            foreach ($instrumentStatements as $instrumentStatement){
                $documents = AttachedDocument::where('statement_id', $instrumentStatement->id)->get();
                foreach ($documents as $document){
                    $attachedDocument = Arr::prepend($attachedDocument, $document);
                }
            }

            return response()->json(['statements' => $instrumentStatements, 'documents' => $attachedDocument]);
        }
        else{
            $area = InstrumentProgram::where('id', $task->transaction_id)->first();
            $instrumentStatements = DB::table('programs_statements')
                ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
                ->join('parameters_programs', 'parameters_programs.id', '=', 'programs_statements.program_parameter_id')
                ->where('parameters_programs.program_instrument_id', $area->id)
                ->select('programs_statements.*', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','programs_statements.parent_statement_id')
                ->get();

            $attachedDocument = array();
            foreach ($instrumentStatements as $instrumentStatement){
                $documents = AttachedDocument::where('statement_id', $instrumentStatement->id)->get();
                foreach ($documents as $document){
                    $attachedDocument = Arr::prepend($attachedDocument, $document);
                }
            }

            $remarks = DB::table('programs_statements')
                ->join('parameters_programs', 'parameters_programs.id', '=', 'programs_statements.program_parameter_id')
                ->join('instruments_scores', 'programs_statements.id', '=', 'instruments_scores.item_id')
                ->join('assigned_users', 'assigned_users.id', '=', 'instruments_scores.assigned_user_id')
                ->join('users', 'users.id', '=', 'assigned_users.user_id')
                ->where('parameters_programs.program_instrument_id', $area->id)
                ->select('programs_statements.*', 'instruments_scores.remark', 'instruments_scores.remark_type','users.first_name','users.last_name', 'users.email' ,'assigned_users.role' )
                ->orderBy('users.id')
                ->get();

            return response()->json(['statements' => $instrumentStatements, 'documents' => $attachedDocument, 'remarks' => $remarks]);
        }
    }
}
