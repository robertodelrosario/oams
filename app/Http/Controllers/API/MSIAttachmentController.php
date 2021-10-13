<?php

namespace App\Http\Controllers\API;

use App\ApplicationFile;
use App\AssignedUser;
use App\AttachedDocument;
use App\Document;
use App\DummyDocument;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentProgramDocument;
use App\InstrumentStatement;
use App\ProgramStatement;
use App\Tag;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class MSIAttachmentController extends Controller
{
    public function attachSupportDocument($id, $docID){
        $check = AttachedDocument::where([
         ['statement_id', $id], ['document_id', $docID]
        ])->first();
        if(is_null($check)){
            $supportDocument = new AttachedDocument();
            $supportDocument->statement_id = $id;
            $supportDocument->document_id = $docID;
            $supportDocument->save();
            return response()->json(['status' => true, 'message' => 'Successfully added document', 'document' => $supportDocument]);
        }
        return response()->json(['status' => false, 'message' => 'Already added document']);
    }

    public function removeSupportDocument($id){
        $document = AttachedDocument::where('id', $id)->first();
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

    public function attachAreaSupportDocument($id, $doc_id){
        $check = InstrumentProgramDocument::where([
            ['instrument_program_id', $id], ['document_id', $doc_id]
        ])->first();
        if(is_null($check)){
            $supportDocument = new InstrumentProgramDocument();
            $supportDocument->instrument_program_id = $id;
            $supportDocument->document_id = $doc_id;
            $supportDocument->save();
            return response()->json(['status' => true, 'message' => 'Successfully added document', 'document' => $supportDocument]);
        }
        return response()->json(['status' => false, 'message' => 'Already added document']);
    }

    public function removeAreaSupportDocument($id){
        $document = InstrumentProgramDocument::where('id', $id);
        $document->delete();
        return response()->json(['status' => true, 'message' => 'Successfully removed document']);
    }

    public function showAreaSupportDocument($id){
        $collection = new Collection();
        $documents = InstrumentProgramDocument::where('instrument_program_id', $id)->get();
        foreach ($documents as $document){
            $file = Document::where('id', $document->document_id)->first();
//            $user = User::where('id', $file->uploader_id)->first();
            $collection->push([
                'instrument_program_document_id' =>  $document->id,
                'document_id' => $file->id,
                'document_name' => $file->document_name,
                'link' => $file->link,
                'type' => $file->type,
                'uploader_id' => $file->uploader_id,
//                'first_name' => $user->first_name,
//                'last_name' => $user->last_name,
                'updated_at' => $file->updated_at,
            ]);
        }
        return response()->json($collection);
    }
}
