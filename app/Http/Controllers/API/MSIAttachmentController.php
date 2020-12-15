<?php

namespace App\Http\Controllers\API;

use App\AssignedUser;
use App\AttachedDocument;
use App\DummyDocument;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentStatement;
use App\ProgramStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        $document = AttachedDocument::where('id', $id);
        $document->delete();
        return response()->json(['status' => true, 'message' => 'Successfully removed document']);
    }

    public function viewSupportDocument($id){
        $document = DummyDocument::where('id', $id)->first();
        $file = File::get(storage_path("app/".$document->location));
        $type = File::mimeType(storage_path("app/".$document->location));

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }
}
