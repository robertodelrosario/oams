<?php

namespace App\Http\Controllers\API;

use App\BestPracticeDocument;
use App\BestPracticeOffice;
use App\Document;
use App\Http\Controllers\Controller;
use App\Office;
use App\PPPStatement;
use App\PPPStatementDocument;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PPPController extends Controller
{
//    public function addPPPStatement(request $request,$id){
//        foreach ($request->texts as $text) {
//            $ppp_statement = PPPStatement::where([
//                ['program_parameter_id', $id], ['statement', $text['statement']], ['type', $text['type']]
//            ])->first();
//            if(is_null($ppp_statement)){
//                $ppp_statement = new PPPStatement();
//                $ppp_statement->statement = $text['statement'];
//                $ppp_statement->parameter_program_id = $id;
//                $ppp_statement->type = $text['type'];
//                $ppp_statement->save();
//            }
//        }
//        return response()->json(['status' => true, 'message' => 'Successfully added statement/s!']);
//    }

    public function addPPPStatement(request $request,$id){
        //text['id', 'statement', 'type']
        $collection_id = new Collection();
        foreach ($request->texts as $text){
            if($text['ppp_statement_id'] == null){
                $ppp_statement = new PPPStatement();
                $ppp_statement->statement = $text['statement'];
                $ppp_statement->parameter_program_id = $id;
                $ppp_statement->type = $text['type'];
                $ppp_statement->save();
            }
            else{
                $ppp_statement = PPPStatement::where('id', $text['ppp_statement_id'])->first();
                $ppp_statement->statement = $text['statement'];
                $ppp_statement->parameter_program_id = $id;
                $ppp_statement->type = $text['type'];
                $ppp_statement->save();
            }
            $collection_id->push($ppp_statement->id);
        }
        $ppp_statements = PPPStatement::where('parameter_program_id', $id)->get();
        foreach($ppp_statements as $ppp_statement){
            if($collection_id->contains($ppp_statement->id)){
                continue;
            }
            else $ppp_statement->delete();
        }
        return response()->json(['status' => true, 'message' => 'Successfully saved.']);
    }

    public function editPPPStatement(request $request,$id){
        $ppp_statement = PPPStatement::where('id', $id)->first();
        if(is_null($ppp_statement)) return response()->json(['status' => false, 'message' => 'ID does not exist!']);
        $ppp_statement->statement = $request->statement;
        $ppp_statement->save();
        return response()->json(['status' => true, 'message' => 'Successfully edited statement/s!']);
    }

    public function deletePPPStatement($id){
        $ppp_statement = PPPStatement::where('id', $id)->first();
        if(is_null($ppp_statement)) return response()->json(['status' => false, 'message' => 'ID does not exist!']);
        $ppp_statement->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted statement/s!']);
    }

    public function showPPPStatement($id){
        $collection = new Collection();
        $ppp_statements = PPPStatement::where('program_parameter_id', $id)->get();
        foreach ($ppp_statements as $ppp_statement){
            $collection_document = new Collection();
            $ppp_statement_documents = PPPStatementDocument::where('ppp_statement_id', $ppp_statement->id)->get();
            foreach ($ppp_statement_documents as $ppp_statement_document){
                $document = Document::where('id', $ppp_statement_document->id)->first();
                $collection_document->push([
                    'ppp_statement_document_id' => $ppp_statement_document->id,
                    'document_id' =>  $document->id,
                    'document_name' =>  $document->document_name,
                    'link' =>  $document->link,
                    'type' =>  $document->type,
                ]);
            }
            $collection->push([
                'ppp_statement_id' => $ppp_statement->id,
                'statement' => $ppp_statement->statement,
                'type' => $ppp_statement->type,
                'files' => $collection_document
            ]);
        }
        return response()->json($collection);
    }

    public function attachFile($statement_id, $document_id){
        $ppp_statement_document = PPPStatementDocument::where([
            ['ppp_statement_id', $statement_id], ['document_id', $document_id]
        ])->first();
        if(is_null($ppp_statement_document)){
            $ppp_statement_document = new PPPStatementDocument();
            $ppp_statement_document->ppp_statement_id = $statement_id;
            $ppp_statement_document->document_id = $document_id;
            $ppp_statement_document->save();
            return response()->json(['status' => true, 'message' => 'Successfully attached file!']);
        }
        return response()->json(['status' => false, 'message' => 'File was already attached!']);
    }

    public function removeFile($id){
        $ppp_statement_document = PPPStatementDocument::where('id', $id)->first();
        if(is_null($ppp_statement_document))return response()->json(['status' => false, 'message' => 'ID not found!']);
        $ppp_statement_document->delete();
        return response()->json(['status' => true, 'message' => 'Successfully removed attached file!']);
    }

    public function showAllBestPractice($id)
    {
        $collection = new Collection();
        $offices = Office::where('campus_id', $id)->get();
        foreach ($offices as $office) {
            $best_practices = BestPracticeOffice::where('office_id', $office->id)->get();
            foreach ($best_practices as $best_practice) {
                $user = User::where('id', $best_practice->user_id)->first();
                $best_practice_documents = BestPracticeDocument::where('id', $best_practice->id)->get();
                $document_collection = new Collection();
                foreach ($best_practice_documents as $best_practice_document) {
                    $document = Document::where('id', $best_practice_document->document_id)->first();
                    $document_collection->push([
                        'best_practice_document_id' => $best_practice_document->id,
                        'document_id' => $best_practice_document->document_id,
                        'document_name' => $document->document_name,
                        'document_name' => $document->document_name,
                        'link' => $document->link,
                        'type' => $document->type,
                    ]);
                }
                $collection->push([
                    'best_practice_id' => $best_practice->id,
                    'best_practice' => $best_practice->best_practice,
                    'office_id' => $best_practice->office_id,
                    'user_id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'updated_at' => $best_practice->updated_at,
                    'files' => $document_collection
                ]);
            }
        }
        return response()->json(['best_practices' => $collection]);
    }
}
