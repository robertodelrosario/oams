<?php

namespace App\Http\Controllers\API;

use App\Application;
use App\ApplicationFile;
use App\ApplicationProgram;
use App\ApplicationProgramFile;
use App\AreaInstrument;
use App\AssignedUser;
use App\AssignedUserHead;
use App\BenchmarkStatement;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class AppliedProgramController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login', 'register', 'me']]);
    }*/

    public function program(request $request)
    {
        $check = ApplicationProgram::where([
            ['application_id', $request->application_id], ['program_id', $request->program_id]
        ])->first();
        if(is_null($check)){
            $program = new ApplicationProgram();
            $program->application_id = $request->application_id;
            $program->program_id = $request->program_id;
            $program->level = $request->level;
            $program->preferred_start_date = \Carbon\Carbon::parse($request->preferred_start_date)->format('Y-m-d');
            $program->preferred_end_date = \Carbon\Carbon::parse($request->preferred_end_date)->format('Y-m-d');
            $program->status = "pending";
            $program->save();
            $check = ApplicationProgram::where([
                ['application_id', $request->application_id], ['program_id', $request->program_id]
            ])->first();

            return response()->json(['status' => true, 'message' => 'Successfully added program!', 'applied_program'=> $check]);
        }
        return response()->json(['status' => false, 'message' => 'program already applied!']);
    }

    public function delete($id){
        $program = ApplicationProgram::where('id',$id);
        $program->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted applied program!']);
    }

    public function edit(request $request, $id){
        $program = ApplicationProgram::where('id',$id)->first();
        $program->level = $request->level;
        $program->preferred_start_date = \Carbon\Carbon::parse($request->preferred_start_date)->format('Y-m-d');
        $program->preferred_end_date = \Carbon\Carbon::parse($request->preferred_end_date)->format('Y-m-d');
        $program->save();
        return response()->json(['status' => true, 'message' => 'Successfully updated applied program!']);
    }

    public function uploadFile(Request $request, $id, $userID)
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required',
            'filename.*' => 'mimes:doc,pdf,docx,zip'
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Required Application Letter!']);


        if ($request->hasfile('filename')) {
            foreach ($files = $request->file('filename') as $file) {
                $applicationProgram = new ApplicationProgramFile();
                $fileName = $file->getClientOriginalName();
                $filePath = $file->storeAs('application/files', $fileName);
                $applicationProgram->file_title = $fileName;
                $applicationProgram->file = $filePath;
                $applicationProgram->type = $request->type;
                $applicationProgram->application_program_id = $id;
                $applicationProgram->uploader_id = $userID;
                $applicationProgram->save();
            }
            return response()->json(['status' => true, 'message' => 'Successfully added files!']);
        }
        return response()->json(['status' => false, 'message' => 'Unsuccessfully added files!']);
    }

    public function deleteProgramFile($id){
        $file = ApplicationProgramFile::where('id', $id)->first();
        File::delete(storage_path("app/".$file->file));
        $file->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted file!']);
    }

    public function viewProgramFile($id){
        $file_link = ApplicationProgramFile::where('id', $id)->first();
        $file = File::get(storage_path("app/".$file_link->file));
        $type = File::mimeType(storage_path("app/".$file_link->file));

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }

//    public function uploadPPP(request $request, $id){
//        $validator = Validator::make($request->all(), [
//            'ppp' => 'required|mimes:doc,docx,pdf|max:2048'
//        ]);
//        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required report!']);
//
//        $program = ApplicationProgram::where('id', $id)->first();
//
//        $fileName = time().'_'.$request->ppp->getClientOriginalName();
//        $filePath = $request->file('ppp')->storeAs('uploads', $fileName);
//        $program->ppp = $filePath;
//        $program->save();
//        return response()->json(['status' => true, 'message' => 'Successfully added supporting documents!']);
//    }
//    public function uploadCompliance(request $request, $id){
//        $validator = Validator::make($request->all(), [
//            'compliance_report' => 'required|mimes:doc,docx,pdf|max:2048'
//        ]);
//        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required report!']);
//
//        $program = ApplicationProgram::where('id', $id)->first();
//
//        $fileName = time().'_'.$request->compliance_report->getClientOriginalName();
//        $filePath = $request->file('compliance_report')->storeAs('uploads', $fileName);
//        $program->compliance_report =$filePath;
//        $program->save();
//        return response()->json(['status' => true, 'message' => 'Successfully added supporting documents!']);
//    }
//    public function uploadNarrative(request $request, $id){
//        $validator = Validator::make($request->all(), [
//            'narrative_report' => 'required|mimes:doc,docx,pdf|max:2048'
//        ]);
//        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Required report!']);
//
//        $program = ApplicationProgram::where('id', $id)->first();
//        $fileName = time().'_'.$request->narrative_report->getClientOriginalName();
//        $filePath = $request->file('narrative_report')->storeAs('uploads', $fileName);
//        $program->narrative_report =$filePath;
//        $program->save();
//        return response()->json(['status' => true, 'message' => 'Successfully added supporting documents!']);
//    }
//    public function deletePPP($id){
//        $program = ApplicationProgram::where('id', $id)->first();
//        Storage::delete(storage_path("app/".$program->ppp));
//        $program->ppp = null;
//        $program->save();
//        return response()->json(['status' => true, 'message' => 'Successfully deleted supporting documents!']);
//    }
//    public function deleteCompliance($id){
//        $program = ApplicationProgram::where('id', $id)->first();
//        Storage::delete(storage_path("app/".$program->compliance_report));
//        $program->compliance_report= null;
//        $program->save();
//        return response()->json(['status' => true, 'message' => 'Successfully deleted supporting documents!']);
//    }
//    public function deleteNarrative($id){
//        $program = ApplicationProgram::where('id', $id)->first();
//        File::delete(storage_path("app/".$program->Narrative_report));
//        $program->Narrative_report= null;
//        $program->save();
//        return response()->json(['status' => true, 'message' => 'Successfully deleted supporting documents!']);
//    }
//    public function viewPPP($id){
//        $ppp = ApplicationProgram::where('id', $id)->first();
//        $file = File::get(storage_path("app/".$ppp->ppp));
//        $type = File::mimeType(storage_path("app/".$ppp->ppp));
//
//        $response = Response::make($file, 200);
//        $response->header("Content-Type", $type);
//        return $response;
//    }
//
//    public function viewNarrative($id){
//        $narrative = ApplicationProgram::where('id', $id)->first();
//        $file = File::get(storage_path("app/".$narrative->narrative_report));
//        $type = File::mimeType(storage_path("app/".$narrative->narrative_report));
//
//        $response = Response::make($file, 200);
//        $response->header("Content-Type", $type);
//        return $response;
//    }
//
//    public function viewCompliance($id){
//        $compliance = ApplicationProgram::where('id', $id)->first();
//        $file = File::get(storage_path("app/".$compliance->compliance_report));
//        $type = File::mimeType(storage_path("app/".$compliance->compliance_report));
//
//        $response = Response::make($file, 200);
//        $response->header("Content-Type", $type);
//        return $response;
//    }

    public function programList($id){
        $collection = new Collection();
        $programs = DB::table('campuses')
            ->join('programs', 'programs.campus_id', '=', 'campuses.id')
            ->where('campuses.id', $id)
            ->get();
        foreach($programs as $program){
            $checkPrograms = ApplicationProgram::where('program_id', $program->id)->get();
            $status = 0;
            foreach ($checkPrograms as $checkProgram){
                if($checkProgram->status == 'done') continue;
                else{
                    $status = 1;
                    break;
                }
            }
            if ($status == 0){
                $collection->push($program);
            }
        }
        return response()->json(['programs' =>$collection]);

    }

    public function showProgram($id){
        $programs = DB::table('applications_programs')
            ->join('programs', 'applications_programs.program_id', '=', 'programs.id')
            ->join('campuses', 'campuses.id', '=', 'programs.campus_id')
            ->where('applications_programs.application_id', $id)
            ->select('applications_programs.*', 'programs.program_name', 'campuses.campus_name')
            ->get();
        $users = array();
        $attach_files = array();
        foreach ($programs as $program){
            $user = DB::table('assigned_user_heads')
                ->join('users', 'users.id', '=', 'assigned_user_heads.user_id')
                ->where('assigned_user_heads.application_program_id', $program->id)
                ->first();
            if ($user != null) $users = Arr::prepend($users, $user);
            $files = ApplicationProgramFile::where('application_program_id', $program->id)->get();
            foreach ($files as $file) $attach_files = Arr::prepend($attach_files, $file);
        }
        return response()->json(['programs' =>$programs, 'users' => $users, 'files' => $attach_files]);
    }

    public function showInstrumentProgram($id){
        $instrumentPrograms = DB::table('instruments_programs')
            ->join('area_instruments', 'instruments_programs.area_instrument_id', '=', 'area_instruments.id')
            ->where('instruments_programs.program_id', $id)
            ->select('instruments_programs.*', 'area_instruments.area_number', 'area_instruments.area_name', 'area_instruments.version')
            ->get();
        if(is_null($instrumentPrograms)) return response()->json(['status' => false, 'message' => 'Do not have instruments']);
        $users = array();
        foreach ($instrumentPrograms as $instrumentProgram){
            $assigned_users = DB::table('assigned_users')
                ->join('users', 'users.id', '=', 'assigned_users.user_id')
                ->where('assigned_users.transaction_id', $instrumentProgram->id)
                ->get();
            foreach($assigned_users as $assigned_user){
                if ($assigned_user != null) $users = Arr::prepend($users, $assigned_user);
            }
        }
        return response()->json(['instruments' => $instrumentPrograms, 'users' => $users]);
    }

    public function showStatementDocument($id)
    {
        $area = InstrumentProgram::where('id', $id)->first();
        $instrumentStatement = DB::table('programs_statements')
            ->join('benchmark_statements', 'benchmark_statements.id', '=', 'programs_statements.benchmark_statement_id')
            ->join('parameters_statements', 'parameters_statements.benchmark_statement_id', '=', 'programs_statements.benchmark_statement_id')
            ->join('parameters', 'parameters.id', '=' , 'parameters_statements.parameter_id')
            ->join('instruments_parameters', 'instruments_parameters.parameter_id', '=', 'parameters.id')
            ->where('instruments_parameters.area_instrument_id',$area->area_instrument_id)
            ->where('programs_statements.program_instrument_id', $area->id)
            ->select('programs_statements.program_instrument_id', 'benchmark_statements.id','benchmark_statements.statement','benchmark_statements.type','programs_statements.parent_statement_id', 'parameters_statements.parameter_id', 'parameters.parameter')
            ->orderBy('parameters.parameter')
            ->get();
        $statementDocument = DB::table('programs_statements')
            ->join('attached_documents', 'programs_statements.id', '=', 'attached_documents.statement_id')
            ->join('documents', 'documents.id', '=', 'attached_documents.document_id')
            ->where('programs_statements.program_instrument_id', $area->id)
            ->get();

        $scores = DB::table('programs_statements')
            ->join('instruments_scores', 'programs_statements.id', '=', 'instruments_scores.item_id')
            ->join('assigned_users', 'assigned_users.id', '=', 'instruments_scores.assigned_user_id')
            ->join('users', 'users.id', '=', 'assigned_users.user_id')
            ->where('programs_statements.program_instrument_id', $area->id)
            ->select('programs_statements.*', 'instruments_scores.*','users.first_name','users.last_name', 'users.email' ,'assigned_users.role' )
            ->orderBy('users.id')
            ->get();
        return response()->json(['statements' => $instrumentStatement, 'documents' => $statementDocument, 'scores' => $scores]);
    }
}
