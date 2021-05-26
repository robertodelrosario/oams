<?php

namespace App\Http\Controllers\API;

use App\AreaMean;
use App\AssignedUser;
use App\AttachedDocument;
use App\BenchmarkStatement;
use App\Document;
use App\GraduatePerformance;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentScore;
use App\ParameterProgram;
use App\ProgramInstrument;
use App\ProgramStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MSIController extends Controller
{
    public function showStatementDocument($id, $transactionID){
        $task = AssignedUser::where([
            ['transaction_id', $transactionID], ['user_id', auth()->user()->id], ['app_program_id', $id]
        ])->first();
        dd($task);
        if(!(is_null($task))){
            if(Str::contains($task->role, 'internal accreditor') || Str::contains($task->role, 'external accreditor')){
                $statement_collection = new Collection();
                $area = InstrumentProgram::where('id', $task->transaction_id)->first();
                $parameters = ParameterProgram::where('program_instrument_id', $area->id)->get();
                foreach($parameters as $parameter){
                    $statements = ProgramStatement::where('program_parameter_id', $parameter->id)->get();
                    foreach ($statements as $statement){
                        $ratings = new Collection();
                        $graduate_perfomances =  GraduatePerformance::where('program_statement_id', $statement->id)->get();
                        foreach ($graduate_perfomances as $graduate_perfomance){
                            $ratings->push([
                                'year' =>  $graduate_perfomance->year,
                                'rating' => $graduate_perfomance->rating
                            ]);
                        }
                        $benchmark_statement = BenchmarkStatement::where('id', $statement->benchmark_statement_id)->first();
                        $score = InstrumentScore::where([
                            ['item_id', $statement->id], ['assigned_user_id', $task->id]
                        ])->first();

                        $statement_collection->push([
                            'id' => $statement->id,
                            'program_parameter_id' => $statement->program_parameter_id,
                            'benchmark_statement_id' => $statement->benchmark_statement_id,
                            'parent_statement_id' => $statement->parent_statement_id,
                            'parameter_id' => $parameter->id,
                            'statement' => $benchmark_statement->statement,
                            'type' => $benchmark_statement->type,
                            'item_id' => $score->item_id,
                            'assigned_user_id' => $score->assigned_user_id,
                            'item_score' => $score->item_score,
                            'remark' => $score->remark,
                            'remark_type' => $score->remark_type,
                            'remark_2' => $score->remark_2,
                            'remark_2_type' => $score->remark_2_type,
                            'ratings' => $ratings
                        ]);
                    }
                }

                $documents = new Collection();
                foreach ($statement_collection as $statement){
                    $statement_documents = AttachedDocument::where('statement_id', $statement['id'])->get();
                    foreach ($statement_documents as $statement_document){
                        $doc = Document::where('id', $statement_document->document_id)->first();
                        $documents->push([
                            'id' => $statement_document->id,
                            'statement_id' => $statement_document->statement_id,
                            'document_id' => $statement_document->document_id,
                            'document_name' => $doc->document_name,
                            'link' => $doc->link,
                            'type' => $doc->type,
                            'uploader_id' => $doc->uploader_id,
                            'container_id' => $doc->container_id
                        ]);
                    }
                }

                $area_mean = AreaMean::where([
                    ['instrument_program_id', $task->transaction_id], ['assigned_user_id', $task->id]
                ])->first();

//                $instrumentStatements = DB::table('programs_statements')
//                    ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
//                    ->join('parameters_programs', 'parameters_programs.id', '=', 'programs_statements.program_parameter_id')
//                    ->join('instruments_scores', 'instruments_scores.item_id', '=', 'programs_statements.id')
//                    ->where('parameters_programs.program_instrument_id', $area->id)
//                    ->where('instruments_scores.assigned_user_id', $task->id)
//                    ->select('programs_statements.*',
//                        'parameters_programs.parameter_id',
//                        'benchmark_statements.statement',
//                        'benchmark_statements.type',
//                        'programs_statements.parent_statement_id',
//                        'instruments_scores.item_id',
//                        'instruments_scores.assigned_user_id',
//                        'instruments_scores.item_score',
//                        'instruments_scores.remark',
//                        'instruments_scores.remark_2',
//                        'instruments_scores.remark_type',
//                        'instruments_scores.remark_2_type')
//                    ->get();


//                $attachedDocument = array();
//                foreach ($instrumentStatements as $instrumentStatement){
//                    $documents = DB::table('documents')
//                        ->join('attached_documents', 'documents.id', '=', 'attached_documents.document_id')
//                        ->where('statement_id', $instrumentStatement->id)
//                        ->get();
//                    foreach ($documents as $document){
//                        $attachedDocument = Arr::prepend($attachedDocument, $document);
//                    }
//                }

                return response()->json(['statements' => $statement_collection, 'documents' => $documents, 'area_mean' => $area_mean]);
            }
            else{

                $statement_collection = new Collection();
                $area = InstrumentProgram::where('id', $task->transaction_id)->first();
                $parameters = ParameterProgram::where('program_instrument_id', $area->id)->get();
                foreach($parameters as $parameter){
                    $statements = ProgramStatement::where('program_parameter_id', $parameter->id)->get();
                    foreach ($statements as $statement){
                        $ratings = new Collection();
                        $graduate_perfomances =  GraduatePerformance::where('program_statement_id', $statement->id)->get();
                        foreach ($graduate_perfomances as $graduate_perfomance){
                            $ratings->push([
                                'year' =>  $graduate_perfomance->year,
                                'rating' => $graduate_perfomance->rating
                            ]);
                        }
                        $benchmark_statement = BenchmarkStatement::where('id', $statement->benchmark_statement_id)->first();

                        $statement_collection->push([
                            'id' => $statement->id,
                            'program_parameter_id' => $statement->program_parameter_id,
                            'benchmark_statement_id' => $statement->benchmark_statement_id,
                            'parent_statement_id' => $statement->parent_statement_id,
                            'parameter_id' => $parameter->id,
                            'statement' => $benchmark_statement->statement,
                            'type' => $benchmark_statement->type,
                            'ratings' => $ratings
                        ]);
                    }
                }

                $documents = new Collection();
                foreach ($statement_collection as $statement){
                    $statement_documents = AttachedDocument::where('statement_id', $statement['id'])->get();
                    foreach ($statement_documents as $statement_document){
                        $doc = Document::where('id', $statement_document->document_id)->first();
                        $documents->push([
                            'id' => $statement_document->id,
                            'statement_id' => $statement_document->statement_id,
                            'document_id' => $statement_document->document_id,
                            'document_name' => $doc->document_name,
                            'link' => $doc->link,
                            'type' => $doc->type,
                            'uploader_id' => $doc->uploader_id,
                            'container_id' => $doc->container_id
                        ]);
                    }
                }

//                $area = InstrumentProgram::where('id', $task->transaction_id)->first();
//                $instrumentStatements = DB::table('programs_statements')
//                    ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
//                    ->join('parameters_programs', 'parameters_programs.id', '=', 'programs_statements.program_parameter_id')
//                    ->where('parameters_programs.program_instrument_id', $area->id)
//                    ->select('programs_statements.*','benchmark_statements.statement','benchmark_statements.type','programs_statements.parent_statement_id')
//                    ->get();

//                $attachedDocument = array();
//                foreach ($instrumentStatements as $instrumentStatement){
//                    $documents = DB::table('documents')
//                        ->join('attached_documents', 'documents.id', '=', 'attached_documents.document_id')
//                        ->where('statement_id', $instrumentStatement->id)
//                        ->get();
//                    foreach ($documents as $document){
//                        $attachedDocument = Arr::prepend($attachedDocument, $document);
//                    }
//                }

                $remarks = DB::table('programs_statements')
                    ->join('parameters_programs', 'parameters_programs.id', '=', 'programs_statements.program_parameter_id')
                    ->join('instruments_scores', 'programs_statements.id', '=', 'instruments_scores.item_id')
                    ->join('assigned_users', 'assigned_users.id', '=', 'instruments_scores.assigned_user_id')
                    ->join('users', 'users.id', '=', 'assigned_users.user_id')
                    ->where('parameters_programs.program_instrument_id', $area->id)
                    ->select('programs_statements.*', 'instruments_scores.remark', 'instruments_scores.remark_type', 'instruments_scores.remark_2', 'instruments_scores.remark_2_type','users.first_name','users.last_name', 'users.email' ,'assigned_users.role' )
                    ->orderBy('users.id')
                    ->get();
                return response()->json(['statements' => $statement_collection, 'documents' => $documents, 'remarks' => $remarks]);
            }
        }
        else{
            $statement_collection = new Collection();
            $area = InstrumentProgram::where('id', $task->transaction_id)->first();
            $parameters = ParameterProgram::where('program_instrument_id', $area->id)->get();
            foreach($parameters as $parameter){
                $statements = ProgramStatement::where('program_parameter_id', $parameter->id)->get();
                foreach ($statements as $statement){
                    $ratings = new Collection();
                    $graduate_perfomances =  GraduatePerformance::where('program_statement_id', $statement->id)->get();
                    foreach ($graduate_perfomances as $graduate_perfomance){
                        $ratings->push([
                            'year' =>  $graduate_perfomance->year,
                            'rating' => $graduate_perfomance->rating
                        ]);
                    }
                    $benchmark_statement = BenchmarkStatement::where('id', $statement->benchmark_statement_id)->first();

                    $statement_collection->push([
                        'id' => $statement->id,
                        'program_parameter_id' => $statement->program_parameter_id,
                        'benchmark_statement_id' => $statement->benchmark_statement_id,
                        'parent_statement_id' => $statement->parent_statement_id,
                        'parameter_id' => $parameter->id,
                        'statement' => $benchmark_statement->statement,
                        'type' => $benchmark_statement->type,
                        'ratings' => $ratings
                    ]);
                }
            }

            $documents = new Collection();
            foreach ($statement_collection as $statement){
                $statement_documents = AttachedDocument::where('statement_id', $statement['id'])->get();
                foreach ($statement_documents as $statement_document){
                    $doc = Document::where('id', $statement_document->document_id)->first();
                    $documents->push([
                        'id' => $statement_document->id,
                        'statement_id' => $statement_document->statement_id,
                        'document_id' => $statement_document->document_id,
                        'document_name' => $doc->document_name,
                        'link' => $doc->link,
                        'type' => $doc->type,
                        'uploader_id' => $doc->uploader_id,
                        'container_id' => $doc->container_id
                    ]);
                }
            }

            $remarks = DB::table('programs_statements')
                ->join('parameters_programs', 'parameters_programs.id', '=', 'programs_statements.program_parameter_id')
                ->join('instruments_scores', 'programs_statements.id', '=', 'instruments_scores.item_id')
                ->join('assigned_users', 'assigned_users.id', '=', 'instruments_scores.assigned_user_id')
                ->join('users', 'users.id', '=', 'assigned_users.user_id')
                ->where('parameters_programs.program_instrument_id', $area->id)
                ->select('programs_statements.*', 'instruments_scores.remark', 'instruments_scores.remark_type', 'instruments_scores.remark_2', 'instruments_scores.remark_2_type','users.first_name','users.last_name', 'users.email' ,'assigned_users.role' )
                ->orderBy('users.id')
                ->get();
            return response()->json(['statements' => $statement_collection, 'documents' => $documents, 'remarks' => $remarks]);
        }

    }
}
