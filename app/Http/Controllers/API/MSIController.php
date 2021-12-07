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
use App\ScoreRemark;
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
                        $year_1 = null;
                        $year_2 = null;
                        $year_3 = null;
                        $rating_1 = null;
                        $rating_2 = null;
                        $rating_3 = null;
                        $x = 1;
                        foreach ($graduate_perfomances as $graduate_perfomance){
                            if($x == 1){
                                $year_1 = $graduate_perfomance->year;
                                $rating_1 = $graduate_perfomance->rating;
                                $x++;
                            }
                            elseif($x == 2){
                                $year_2 = $graduate_perfomance->year;
                                $rating_2 = $graduate_perfomance->rating;
                                $x++;
                            }
                            elseif($x == 3){
                                $year_3 = $graduate_perfomance->year;
                                $rating_3 = $graduate_perfomance->rating;
                                $x++;
                            }
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
                            'year_1' =>  $year_1,
                            'rating_1' => $rating_1,
                            'year_2' =>  $year_2,
                            'rating_2' => $rating_2,
                            'year_3' =>  $year_3,
                            'rating_3' => $rating_3,
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
                if(is_null($area_mean)) $score = null;
                else $score = $area_mean->area_mean;
                $unread_messages_count = new Collection();
                foreach ($statement_collection as $sm){
                    $messages_internal = ScoreRemark::where([
                        ['application_program_id', $id], ['program_statement_id', $sm['id']], ['status', 'unread'], ['sender_id', '!=', auth()->user()->id], ['type', 'Internal']
                    ])->get();
                    $messages_external = ScoreRemark::where([
                        ['application_program_id', $id], ['program_statement_id', $sm['id']], ['status', 'unread'], ['sender_id', '!=', auth()->user()->id], ['type', 'External']
                    ])->get();
                    $unread_messages_count->push([
                        'id' =>  $sm['id'],
                        'count_internal' => count($messages_internal),
                        'count_external' => count($messages_external)
                    ]);
                }
                return response()->json(['statements' => $statement_collection,'unread_message_count' => $unread_messages_count ,'documents' => $documents, 'area_mean' => $score]);
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
                        $year_1 = null;
                        $year_2 = null;
                        $year_3 = null;
                        $rating_1 = null;
                        $rating_2 = null;
                        $rating_3 = null;
                        $x = 1;
                        foreach ($graduate_perfomances as $graduate_perfomance){
                            if($x == 1){
                                $year_1 = $graduate_perfomance->year;
                                $rating_1 = $graduate_perfomance->rating;
                                $x++;
                            }
                            elseif($x == 2){
                                $year_2 = $graduate_perfomance->year;
                                $rating_2 = $graduate_perfomance->rating;
                                $x++;
                            }
                            elseif($x == 3){
                                $year_3 = $graduate_perfomance->year;
                                $rating_3 = $graduate_perfomance->rating;
                                $x++;
                            }
//                            $ratings->push([
//                                'id'=>$graduate_perfomance->id,
//                                'year' =>  $graduate_perfomance->year,
//                                'rating' => $graduate_perfomance->rating
//                            ]);
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
 //                           'ratings' => $ratings,
                            'year_1' =>  $year_1,
                            'rating_1' => $rating_1,
                            'year_2' =>  $year_2,
                            'rating_2' => $rating_2,
                            'year_3' =>  $year_3,
                            'rating_3' => $rating_3,
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
                $unread_messages_count = new Collection();
                foreach ($statement_collection as $sm){
                    $messages_internal = ScoreRemark::where([
                        ['application_program_id', $id], ['program_statement_id', $sm['id']], ['status', 'unread'], ['sender_id', '!=', auth()->user()->id], ['type', 'Internal']
                    ])->get();
                    $messages_external = ScoreRemark::where([
                        ['application_program_id', $id], ['program_statement_id', $sm['id']], ['status', 'unread'], ['sender_id', '!=', auth()->user()->id], ['type', 'External']
                    ])->get();
                    $unread_messages_count->push([
                        'id' =>  $sm['id'],
                        'count_internal' => count($messages_internal),
                        'count_external' => count($messages_external)
                    ]);
                }
                return response()->json(['statements' => $statement_collection,'unread_message_count' => $unread_messages_count,'documents' => $documents, 'remarks' => $remarks]);
            }
        }
        else{
            $statement_collection = new Collection();
            $area = InstrumentProgram::where('id', $transactionID)->first();
            $parameters = ParameterProgram::where('program_instrument_id', $area->id)->get();
            foreach($parameters as $parameter){
                $statements = ProgramStatement::where('program_parameter_id', $parameter->id)->get();
                foreach ($statements as $statement){
                    $ratings = new Collection();
                    $graduate_perfomances =  GraduatePerformance::where('program_statement_id', $statement->id)->get();
                    $year_1 = null;
                    $year_2 = null;
                    $year_3 = null;
                    $rating_1 = null;
                    $rating_2 = null;
                    $rating_3 = null;
                    $x = 1;
                    foreach ($graduate_perfomances as $graduate_perfomance){
                        if($x == 1){
                            $year_1 = $graduate_perfomance->year;
                            $rating_1 = $graduate_perfomance->rating;
                            $x++;
                        }
                        elseif($x == 2){
                            $year_2 = $graduate_perfomance->year;
                            $rating_2 = $graduate_perfomance->rating;
                            $x++;
                        }
                        elseif($x == 3){
                            $year_3 = $graduate_perfomance->year;
                            $rating_3 = $graduate_perfomance->rating;
                            $x++;
                        }
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
//                        'ratings' => $ratings,
                        'year_1' =>  $year_1,
                        'rating_1' => $rating_1,
                        'year_2' =>  $year_2,
                        'rating_2' => $rating_2,
                        'year_3' =>  $year_3,
                        'rating_3' => $rating_3,
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
            $unread_messages_count = new Collection();
            foreach ($statement_collection as $sm){
                $messages_internal = ScoreRemark::where([
                    ['application_program_id', $id], ['program_statement_id', $sm['id']], ['status', 'unread'], ['sender_id', '!=', auth()->user()->id], ['type', 'Internal']
                ])->get();
                $messages_external = ScoreRemark::where([
                    ['application_program_id', $id], ['program_statement_id', $sm['id']], ['status', 'unread'], ['sender_id', '!=', auth()->user()->id], ['type', 'External']
                ])->get();
                $unread_messages_count->push([
                    'id' =>  $sm['id'],
                    'count_internal' => count($messages_internal),
                    'count_external' => count($messages_external)
                ]);
            }
            return response()->json(['statements' => $statement_collection, 'unread_message_count' => $unread_messages_count,'documents' => $documents, 'remarks' => $remarks]);
        }

    }
}
