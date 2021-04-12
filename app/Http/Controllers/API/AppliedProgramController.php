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
use App\Office;
use App\OfficeUser;
use App\Program;
use App\ProgramInstrument;
use App\Transaction;
use App\User;
use App\UserRole;
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

            $prog = Program::where('id', $request->program_id)->first();
            if($prog->office_id != null){
                $office_users = OfficeUser::where('office_id', $prog->office_id)->get();
                foreach($office_users as $office_user){
                    $user_role = UserRole::where('id', $office_user->user_role_id)->first();
                    if($user_role->role_id == 2){
                        $task = new AssignedUserHead();
                        $task->application_program_id = $program->id;
                        $task->user_id = $user_role->user_id;
                        $task->role = 'program task force chair';
                        $task->save();
                        break;
                    }
                }
                $office = Office::where('id', $prog->office_id)->first();
                $parent_office = Office::where('id', $office->parent_office_id)->first();
                if(!(is_null($parent_office))){
                    $office_users = OfficeUser::where('office_id', $parent_office->id)->get();
                    foreach($office_users as $office_user){
                        $user_role = UserRole::where('id', $office_user->user_role_id)->first();
                        if($user_role->role_id == 11){
                            $task = new AssignedUserHead();
                            $task->application_program_id = $program->id;
                            $task->user_id = $user_role->user_id;
                            $task->role = 'college task force head';
                            $task->save();
                        }
                    }
                }
            }
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
            'filename.*' => 'mimes:doc,pdf,docx,zip,png,jpg'   //
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Required File!']);


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
                $area_name = array('Area I','Area II','Area III','Area IV','Area V','Area VI','Area VII','Area VIII','Area IX','Area X');
                $area = null;
                for($x=0; $x < 10; $x++){
                    if($x+1 == $request->area_number) {
                        $area = $area_name[$x];
                        break;
                    }
                }
                $applicationProgram->area = $area;
                $applicationProgram->save();
            }
            return response()->json(['status' => true, 'message' => 'Successfully added files!']);
        }
        return response()->json(['status' => false, 'message' => 'Unsuccessfully added files!']);
    }

    public function deleteProgramFile($id, $user_id){
        $file = ApplicationProgramFile::where('id', $id)->first();
        $user = User::where('id', $file->uploader_id)->first();
        if($file->uploader_id != $user_id) return response()->json(['status' => false, 'message' => 'Only '.$user->first_name." ".$user->last_name." can remove the file."]);
        File::delete(storage_path("app/".$file->file));
        $file->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted file!']);
    }

    public function showProgramFile($id){
        $files = ApplicationProgramFile::where('application_program_id', $id)->get();
        return response()->json($files);
    }


    public function showFileTFH($id){
        $report = array();
        $compliance = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'Compliance Report']
            ])->get();
        foreach ($compliance as $c) $report = Arr::prepend($report,$c);
        $ppp = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'PPP']
        ])->get();
        foreach ($ppp as $p) $report = Arr::prepend($report,$p);
        $narrative = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'Narrative Report']
        ])->get();
        foreach ($narrative as $n) $report = Arr::prepend($report,$n);

        return response()->json($report);
    }

    public function showFileTF($id,$Userid){
        $area_name = array('Area I','Area II','Area III','Area IV','Area V','Area VI','Area VII','Area VIII','Area IX','Area X');

        $areas = AssignedUser::where([
            ['app_program_id', $id], ['user_id', $Userid]
        ])->get();
        $report = array();

        foreach ($areas as $area){
            $instrument = InstrumentProgram::where('id', $area->transaction_id)->first();
            $area_number = AreaInstrument::where('id', $instrument->area_instrument_id)->first();

            $compliance = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'Compliance Report'], ['area', $area_name[$area_number->area_number-1]]
            ])->get();
            foreach ($compliance as $c) $report = Arr::prepend($report,$c);
            $ppp = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'PPP'], ['area', $area_name[$area_number->area_number-1]]
            ])->get();
            foreach ($ppp as $p) $report = Arr::prepend($report,$p);
            $narrative = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'Narrative Report'], ['area', $area_name[$area_number->area_number-1]]
            ])->get();
            foreach ($narrative as $n) $report = Arr::prepend($report,$n);
        }
        return response()->json($report);
    }

    public function showFileIA($id,$Userid){
        $area_name = array('Area I','Area II','Area III','Area IV','Area V','Area VI','Area VII','Area VIII','Area IX','Area X');
        $areas = AssignedUser::where([
            ['app_program_id', $id], ['user_id', $Userid]
        ])->get();

        $report = array();
        foreach ($areas as $area){
            $instrument = InstrumentProgram::where('id', $area->transaction_id)->first();
            $area_number = AreaInstrument::where('id', $instrument->area_instrument_id)->first();

            $compliance = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'Compliance Report'], ['area', $area_name[$area_number->area_number-1]]
            ])->get();
            foreach ($compliance as $c) $report = Arr::prepend($report,$c);
            $ppp = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'PPP'], ['area', $area_name[$area_number->area_number-1]]
            ])->get();
            foreach ($ppp as $p) $report = Arr::prepend($report,$p);
            $narrative = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'Narrative Report'], ['area', $area_name[$area_number->area_number-1]]
            ])->get();
            foreach ($narrative as $n) $report = Arr::prepend($report,$n);
        }
        $sar = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'Internal SAR']
        ])->get();
        foreach ($sar as $s) $report = Arr::prepend($report,$s);
        $sfr = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'Internal SFR']
        ])->get();
        foreach ($sfr as $s) $report = Arr::prepend($report,$s);
        return response()->json($report);
    }

    public function showFileQA($id){
        $report = array();
        $compliance = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'Compliance Report']
        ])->get();
        foreach ($compliance as $c) $report = Arr::prepend($report,$c);
        $ppp = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'PPP']
        ])->get();
        foreach ($ppp as $p) $report = Arr::prepend($report,$p);
        $narrative = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'Narrative Report']
        ])->get();
        foreach ($narrative as $n) $report = Arr::prepend($report,$n);
        $sar = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'Internal SAR']
        ])->get();
        foreach ($sar as $s) $report = Arr::prepend($report,$s);
        $sfr = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'Internal SFR']
        ])->get();
        foreach ($sfr as $s) $report = Arr::prepend($report,$s);
        return response()->json($report);
    }

    public function viewProgramFile($id){
        $file_link = ApplicationProgramFile::where('id', $id)->first();
        $file = File::get(storage_path("app/".$file_link->file));
        $type = File::mimeType(storage_path("app/".$file_link->file));

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }

    public function showFile($id){
        $file_link = ApplicationProgramFile::where('id', $id)->first();
        $file = File::get(storage_path("app/".$file_link->file));
        $type = File::mimeType(storage_path("app/".$file_link->file));

        $path = storage_path("app/".$file_link->file);
        return response()->json(['link' =>$path, 'type' => $type]);
    }

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
        $collection = new Collection();
        foreach ($programs as $program){
            $user = DB::table('assigned_user_heads')
                ->join('users', 'users.id', '=', 'assigned_user_heads.user_id')
                ->where('assigned_user_heads.application_program_id', $program->id)
                ->get();
            foreach ($user as $u) $users = Arr::prepend($users, $u);
            $files = ApplicationProgramFile::where('application_program_id', $program->id)->get();
            foreach ($files as $file) $attach_files = Arr::prepend($attach_files, $file);
            $status = 'missing';
            $check = InstrumentProgram::where('program_id', $program->program_id)->get();
            if(count($check) > 0 ){
                $status = 'attached';
            }
            $collection->push([
                'id' => $program->id,
                'program_id' => $program->program_id,
                'application_id' => $program->application_id,
                'level' =>$program->level,
                'preferred_start_date' =>$program->preferred_start_date,
                'preferred_end_date' =>$program->preferred_end_date,
                'approved_start_date' =>$program->approved_start_date,
                'approved_end_date' =>$program->approved_end_date,
                'status' =>$program->status,
                'result' =>$program->result,
                'date_granted' =>$program->date_granted,
                'certificate'=>$program->certificate,
                'attachment_status' => $status,
                'program_name' =>$program->program_name,
                'campus_name' =>$program->campus_name,
                'self_survey_status' => $program->self_survey_status
            ]);
        }
        return response()->json(['programs' =>$collection, 'users' => $users, 'files' => $attach_files]);
    }

    public function showInstrumentProgram($id){
        $instrumentPrograms = DB::table('instruments_programs')
            ->join('area_instruments', 'instruments_programs.area_instrument_id', '=', 'area_instruments.id')
            ->where('instruments_programs.program_id', $id)
            ->select('instruments_programs.*','area_instruments.intended_program_id','area_instruments.area_number', 'area_instruments.area_name', 'area_instruments.version')
            ->get();
        if(count($instrumentPrograms) < 0 ) return response()->json(['status' => false, 'message' => 'Do not have instruments']);
        $users = array();
        $program= null;
        foreach ($instrumentPrograms as $instrumentProgram){
            $assigned_users = DB::table('assigned_users')
                ->join('users', 'users.id', '=', 'assigned_users.user_id')
                ->where('assigned_users.transaction_id', $instrumentProgram->id)
                ->get();
            foreach($assigned_users as $assigned_user){
                if ($assigned_user != null) $users = Arr::prepend($users, $assigned_user);
            }
            $intended_program = ProgramInstrument::where('id',$instrumentProgram->intended_program_id)->first();
            if(!(is_null($intended_program))) $program = $intended_program->type_of_instrument;
            else $program = "No attached instrument.";
        }
        return response()->json(['instruments' => $instrumentPrograms, 'users' => $users, 'intended_program' => $program]);
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

    public function lockSelfSurvey(request $request,$id){
        $program = ApplicationProgram::where('id', $id)->first();
        if($request->status == true) $program->self_survey_status = 1;
        else $program->self_survey_status = 0;
        $program->save();

        $prog = Program::where('id', $program->program_id)->first();
        if($request->status == 0) return response()->json(['status' => true, 'message' => "Self-survey for program " .$prog->program_name. " was successfully unlocked."]);
        elseif($request->status == 1) return response()->json(['status' => true, 'message' => "Self-survey for program " .$prog->program_name. " was successfully locked."]);
    }
}
