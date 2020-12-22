<?php

namespace App\Http\Controllers\API;

use App\ApplicationFile;
use App\AssignedUser;
use App\AttachedDocument;
use App\DummyDocument;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentStatement;
use App\ProgramStatement;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

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
        $document = AttachedDocument::where('id', $id);
        $document->delete();
        return response()->json(['status' => true, 'message' => 'Successfully removed document']);
    }

    public function showDocument(){
        $documents = DB::table('documents')
            ->join('offices', 'offices.id', '=', 'documents.office_id')
            ->join('users', 'users.id', '=', 'documents.uploader_id')
            ->select('documents.*', 'offices.office_name', 'users.first_name', 'users.last_name', 'users.email')
            ->get();

        $tag = array();
        foreach ($documents as $document)
        {
            $tags = Tag::where('document_id', $document->id)->get();
            foreach ($tags as $key){
                $tag = Arr::prepend($tag, $key);
            }
        }
        return response()->json(['documents' =>$documents, 'tags' => $tag]);
    }
}
