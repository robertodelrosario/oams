<?php

namespace App\Http\Controllers\API;

use App\AreaInstrument;
use App\BestPracticeDocument;
use App\BestPracticeOffice;
use App\BestPracticeTag;
use App\Document;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\Office;
use App\Parameter;
use App\ParameterProgram;
use App\PPPStatement;
use App\PPPStatementDocument;
use App\Program;
use App\ProgramInstrument;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
require_once '/var/www/html/oams/vendor/autoload.php';
//require_once 'C:\laragon\www\online_accreditation_management_system\vendor/autoload.php';
use  \PhpOffice\PhpWord\PhpWord;

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
                $ppp_statement->program_parameter_id = $id;
                $ppp_statement->type = $text['type'];
                $ppp_statement->save();

                if($text['best_practice_id'] != null){
                    $files = BestPracticeDocument::where('best_practice_office_id', $text['best_practice_id'])->get();
                    foreach($files as $file){
                        $ppp_statement_document = PPPStatementDocument::where([
                            ['ppp_statement_id', $ppp_statement->id], ['document_id', $file->document_id]
                        ])->first();
                        if(is_null($ppp_statement_document)){
                            $ppp_statement_document = new PPPStatementDocument();
                            $ppp_statement_document->ppp_statement_id = $ppp_statement->id;
                            $ppp_statement_document->document_id = $file->document_id;
                            $ppp_statement_document->save();
                        }
                    }
                }
            }
            else{
                $ppp_statement = PPPStatement::where('id', $text['ppp_statement_id'])->first();
                $ppp_statement->statement = $text['statement'];
                $ppp_statement->program_parameter_id = $id;
                $ppp_statement->type = $text['type'];
                $ppp_statement->save();
            }
            $collection_id->push($ppp_statement->id);
        }
        $ppp_statements = PPPStatement::where('program_parameter_id', $id)->get();
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
                $document = Document::where('id', $ppp_statement_document->document_id)->first();
                $collection_document->push([
                    'ppp_statement_document_id' => $ppp_statement_document->id,
                    'document_id' => $document->id,
                    'document_name' => $document->document_name,
                    'link' => $document->link,
                    'type' => $document->type,
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
                $best_practice_documents = BestPracticeDocument::where('best_practice_office_id', $best_practice->id)->get();
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
                $best_practice_tags = BestPracticeTag::where('best_practice_office_id', $best_practice->id)->get();
                $office = Office::where('id', $best_practice->office_id)->first();
                $collection->push([
                    'best_practice_id' => $best_practice->id,
                    'best_practice' => $best_practice->best_practice,
                    'office_id' => $best_practice->office_id,
                    'office_name' => $office->office_name,
                    'user_id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'updated_at' => $best_practice->updated_at,
                    'files' => $document_collection,
                    'tags' => $best_practice_tags
                ]);
            }
        }
        return response()->json(['best_practices' => $collection]);
    }

    public function downloadPPP($id){
        $instrument = InstrumentProgram::where('id', $id)->first();
        $program = Program::where('id',$instrument->program_id)->first();
        $area = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
        $parameters = ParameterProgram::where('program_instrument_id', $id)->orderBy('parameter_id', 'ASC')->get();
//        foreach ($parameters as $parameter) {
//            $ppp_statements = PPPStatement::where('program_parameter_id', $parameter->id)->get();
//            $param = Parameter::where('id', $parameter->parameter_id)->first();
//            foreach ($ppp_statements as $ppp_statement) {
//                $collection_document = new Collection();
//                $ppp_statement_documents = PPPStatementDocument::where('ppp_statement_id', $ppp_statement->id)->get();
//                foreach ($ppp_statement_documents as $ppp_statement_document) {
//                    $document = Document::where('id', $ppp_statement_document->document_id)->first();
//                    $collection_document->push([
//                        'ppp_statement_document_id' => $ppp_statement_document->id,
//                        'document_id' => $document->id,
//                        'document_name' => $document->document_name,
//                        'link' => $document->link,
//                        'type' => $document->type,
//                    ]);
//                }
//                $collection->push([
//                    'program_parameter_id' => $parameter->id,
//                    'parameter_name'=> $param->parameter_name,
//                    'ppp_statement_id' => $ppp_statement->id,
//                    'statement' => $ppp_statement->statement,
//                    'type' => $ppp_statement->type,
//                    'files' => $collection_document
//                ]);
//            }
//        }

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $styleFont1 = array('align'=>\PhpOffice\PhpWord\Style\Cell::VALIGN_CENTER);
        $styleFont2 = array('space' => array('before' => 1000, 'after' => 100));
        $styleFont3 = array('indentation' => array('left' => 540, 'right' => 120), 'space' => array('before' => 360, 'after' => 280));
        $styleFont4 = array('indentation' => array('left' => 1000, 'right' => 120));

        $section->addText(
            $area->area_name,array('bold' => true, 'size' => 14),
            $styleFont1
        );

        foreach ($parameters as $parameter){
            $collection = new Collection();
            $param = Parameter::where('id', $parameter->parameter_id)->first();
            $section->addText(
                $param->parameter_name,array('bold' => true),
                $styleFont2
            );
            $ppp_statements = PPPStatement::where('program_parameter_id', $parameter->id)->get();
            foreach ($ppp_statements as $ppp_statement) {
                $collection->push([
                    'ppp_statement_id' => $ppp_statement->id,
                    'statement' => $ppp_statement->statement,
                    'type' => $ppp_statement->type
                ]);
            }
            $section->addText(
                $param->parameter,array('bold' => true, 'size' => 12),
                $styleFont1
            );
            $section->addText(
                "1. SYSTEM-INPUTS AND PROCESSES",array('bold' => true, 'size' => 12),
                $styleFont3
            );
            $x = 1;
            foreach ($collection as $c){
                if($c['type'] == 'System Input and Process') {
                    $section->addText(
                        $x . '. ' . $c['statement'], [],
                        $styleFont4
                    );
                    $x++;
                }
            }

            $section->addText(
                "2. IMPLEMENTATION",array('bold' => true, 'size' => 12),
                $styleFont3
            );

            $x = 1;
            foreach ($collection as $c){
                if($c['type'] == 'Implementation') {
                    $section->addText(
                        $x . '. ' . $c['statement'], [],
                        $styleFont4
                    );
                    $x++;
                }
            }

            $section->addText(
                "3. OUTCOMES",array('bold' => true, 'size' => 12),
                $styleFont3
            );

            $x = 1;
            foreach ($collection as $c){
                if($c['type'] == 'Outcome') {
                    $section->addText(
                        $x . '. ' . $c['statement'], [],
                        $styleFont4
                    );
                    $x++;
                }
            }

            $section->addText(
                "4. BEST PRACTICES",array('bold' => true, 'size' => 12),
                $styleFont3
            );

            foreach ($collection as $c){
                if($c['type'] == 'Best Practice') {
                    $section->addText(
                        $c['statement'], [],
                        $styleFont4
                    );
                }
            }

            $section->addText(
                "5. EXTENT of COMPLIANCE",array('bold' => true, 'size' => 12),
                $styleFont3
            );

            $x = 1;
            foreach ($collection as $c){
                if($c['type'] == 'Extent of Compliance') {
                    $section->addText(
                        $x . '. ' . $c['statement'], [],
                        $styleFont4
                    );
                    $x++;
                }
            }
            $section->addPageBreak();
        }
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($program->program_name.'_PPP_'.$area->area_name.'.docx');
        return response()->download(public_path($program->program_name.'_PPP_'.$area->area_name.'.docx'));
    }

    public function sample(){
        $phpword = new PhpWord();
        $section = $phpword->addSection();

        $section->addText("Hello World!");

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpword, 'Word2007');
        $objWriter->save('hello.docx');
        return response()->download(public_path('hello.docx'));
    }
}
