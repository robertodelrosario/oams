<?php

namespace App\Http\Controllers\API;

use App\Application;
use App\ApplicationFile;
use App\ApplicationProgram;
use App\ApplicationProgramFile;
use App\AreaInstrument;
use App\AreaMandatory;
use App\AssignedUser;
use App\AssignedUserHead;
use App\BenchmarkStatement;
use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\InstrumentProgram;
use App\InstrumentStatement;
use App\Office;
use App\OfficeUser;
use App\ParameterProgram;
use App\Program;
use App\ProgramInstrument;
use App\ProgramReportTemplate;
use App\ProgramStatement;
use App\ReportTemplate;
use App\TemplateTag;
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
use Illuminate\Support\Str;

class AppliedProgramController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth:api',['except' => ['login', 'register', 'me']]);
    }*/

    public function program(request $request)
    {
//        $check = ApplicationProgram::where([
//            ['application_id', $request->application_id], ['program_id', $request->program_id]
//        ])->first();
        $programs = ApplicationProgram::where('program_id', $request->program_id)->get();
        $check = 'valid';
        foreach ($programs as $program){
            if($program->status != 'done'){
                $check = 'invalid';
                break;
            }
            $check = 'valid';
        }
        if($check == 'valid'){
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
            $instrument = InstrumentProgram::where('program_id', $request->program_id);
            $instrument->delete();

            if(Str::contains($program->level, 'Candidate')){
                $areas = AreaInstrument::where('intended_program_id', 48)->get();
                foreach ($areas as $area){
                    $instrumentProgram = new InstrumentProgram();
                    $instrumentProgram->program_id = $request->program_id;
                    $instrumentProgram->area_instrument_id = $area->id;
                    $instrumentProgram->save();
                    $instrumentParamenters = InstrumentParameter::where('area_instrument_id', $area->id)->get();
                    if (count($instrumentParamenters) != 0) {
                        foreach ($instrumentParamenters as $instrumentParamenter) {
                            $parameter = new ParameterProgram();
                            $parameter->program_instrument_id = $instrumentProgram->id;
                            $parameter->parameter_id = $instrumentParamenter->parameter_id;
                            $parameter->save();

                            $statements = InstrumentStatement::where('instrument_parameter_id', $instrumentParamenter->id)->get();
                            if (count($statements) != 0) {
                                foreach ($statements as $statement) {
                                    $programStatement = new ProgramStatement();
                                    $programStatement->program_parameter_id = $parameter->id;
                                    $programStatement->benchmark_statement_id = $statement->benchmark_statement_id;
                                    $programStatement->parent_statement_id = $statement->parent_statement_id;
                                    $programStatement->save();
                                }
                            }
                        }
                    }
                }
                return response()->json(['status' => true, 'message' => 'Successfully added program!', 'applied_program'=> $check]);
            }
            elseif(Str::contains($program->level, 'Level III')) {
                $areas = AreaInstrument::where('intended_program_id', 42)->get();
                $level = 'LEVEL III -';
            }
            elseif(Str::contains($program->level, 'Level IV')) {
                $areas = AreaInstrument::where('intended_program_id', 47)->get();
                $level = 'LEVEL IV -';
            }
            foreach ($areas as $area){
                $area_mandatories = AreaMandatory::where('area_instrument_id', $area->id)->get();
                foreach ($area_mandatories as $area_mandatory){
                    if($area_mandatory->type == 'Mandatory' && $prog->type == $area_mandatory->program_status){
                        $instrumentProgram = new InstrumentProgram();
                        $instrumentProgram->program_id = $request->program_id;
                        $instrumentProgram->area_instrument_id = $area->id;
                        $instrumentProgram->save();
                        $instrumentParamenters = InstrumentParameter::where('area_instrument_id', $area->id)->get();
                        if (count($instrumentParamenters) != 0) {
                            foreach ($instrumentParamenters as $instrumentParamenter) {
                                $parameter = new ParameterProgram();
                                $parameter->program_instrument_id = $instrumentProgram->id;
                                $parameter->parameter_id = $instrumentParamenter->parameter_id;
                                $parameter->save();

                                $statements = InstrumentStatement::where('instrument_parameter_id', $instrumentParamenter->id)->get();
                                if (count($statements) != 0) {
                                    foreach ($statements as $statement) {
                                        $programStatement = new ProgramStatement();
                                        $programStatement->program_parameter_id = $parameter->id;
                                        $programStatement->benchmark_statement_id = $statement->benchmark_statement_id;
                                        $programStatement->parent_statement_id = $statement->parent_statement_id;
                                        $programStatement->save();
                                    }
                                }
                            }
                        }
                        //code here
                        $code = $level.' '.$area->area_name;
                        $templates = ReportTemplate::where('campus_id', $prog->campus_id)->get();
                        foreach ($templates as $template){
                            $temp_tags = TemplateTag::where('report_template_id', $template->id)->get();
                            foreach ($temp_tags as $temp_tag){
                                if($temp_tag->tag == $code){
                                    $program_report_template = ProgramReportTemplate::where([
                                        ['report_template_id', $template->id], ['instrument_program_id', $instrumentProgram->id]
                                    ])->first();
                                    if(is_null($program_report_template)) {
                                        $program_report_template = new ProgramReportTemplate();
                                        $program_report_template->report_template_id = $template->id;
                                        $program_report_template->instrument_program_id = $instrumentProgram->id;
                                        $program_report_template->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return response()->json(['status' => true, 'message' => 'Successfully added program!', 'applied_program'=> $check]);
        }
        return response()->json(['status' => false, 'message' => 'program already applied!']);
    }

    public function delete($id){
        $program = ApplicationProgram::where('id',$id)->first();
        $application = Application::where('id', $program->application_id)->first();
        if($application->sender_id == auth()->user()->id){
            $program->delete();
            return response()->json(['status' => true, 'message' => 'Successfully deleted applied program!']);
        }
        $user = User::where('id', $application->sender_id)->first();
        return response()->json(['status' => false, 'message' => 'Only '.$user->first_name. ' '.$user->last_name. ' can delete the program']);
    }

    public function edit(request $request, $id){
        $program = ApplicationProgram::where('id',$id)->first();
        if($program->status == 'pending'){
            $program->preferred_start_date = \Carbon\Carbon::parse($request->preferred_start_date)->format('Y-m-d');
            $program->preferred_end_date = \Carbon\Carbon::parse($request->preferred_end_date)->format('Y-m-d');
            $success = $program->save();
            if($success) return response()->json(['status' => true, 'message' => 'Successfully updated applied program!']);
            else return response()->json(['status' => false, 'message' => 'Unsuccessfully updated applied program. Invalid input!']);
        }
        else return response()->json(['status' => false, 'message' => 'Schedule was already approved.']);
    }

    public function changeApplication($application_id, $program_id){
        $application = Application::where('id', $application_id)->first();
        if($application->status == 'under preparation'){
            $program = ApplicationProgram::where('id',$program_id)->first();
            $program->application_id = $application_id;
            $success = $program->save();
            if($success) return response()->json(['status' => true, 'message' => 'Successfully transferred applied program!']);
            else return response()->json(['status' => false, 'message' => 'Unsuccessfully transferred applied program. Invalid input!']);
        }
        else return response()->json(['status' => false, 'message' => 'Application was already submitted']);
    }

    public function uploadFile(Request $request, $id, $userID)
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required',
            'filename.*' => 'mimes:doc,pdf,docx,zip,png,jpg'
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Required File!']);

        $applied_program = ApplicationProgram::where('id', $id)->first();
        if(Str::contains($applied_program->level, 'Level IV'))
            $area_name = array('RESEARCH','PERFORMANCE OF GRADUATES','COMMUNITY SERVICE','INTERNATIONAL LINKAGES AND CONSORTIA','PLANNING');
        elseif(Str::contains($applied_program->level, 'Level III'))
            $area_name = array('INSTRUCTION','EXTENSION','RESEARCH','FACULTY','LICENSURE EXAM', 'CONSORTIA OR LINKAGE', 'LIBRARY');
        else $area_name = array('Area I','Area II','Area III','Area IV','Area V','Area VI','Area VII','Area VIII','Area IX','Area X');
        $success = false;
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
                $applicationProgram->status = 'pending';
                $area = null;
                for($x=0; $x < 10; $x++){
                    if($x+1 == $request->area_number) {
                        $area = $area_name[$x];
                        break;
                    }
                }
                $applicationProgram->area = $area;
                $success = $applicationProgram->save();
            }
            if($success) return response()->json(['status' => true, 'message' => 'Successfully added files!']);
            else return response()->json(['status' => false, 'message' => 'Unsuccessfully added files!']);
        }
        return response()->json(['status' => false, 'message' => 'Unsuccessfully added files!']);
    }

    public function updateProgramFile(request $request, $id){
        $newfile = ApplicationProgramFile::where('id', $id)->first();
        $user = User::where('id', $newfile->uploader_id)->first();
        if($newfile->uploader_id != auth()->user()->id) return response()->json(['status' => false, 'message' => 'Only '.$user->first_name." ".$user->last_name." can update the file."]);
        if(!(is_null($request->file))) {
            $fileName = $request->file->getClientOriginalName();
            $filePath = $request->file->storeAs('application/files', $fileName);
            $newfile->file_title = $fileName;
            $newfile->file = $filePath;
            $newfile->status = 'revised';
            $success = $newfile->save();
            if ($success) return response()->json(['status' => true, 'message' => 'Successfully updated file']);
            else return response()->json(['status' => false, 'message' => 'Unsuccessfully updated file']);
        }
        return response()->json(['status' => false, 'message' => 'null']);
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
            ['application_program_id', $id], ['type', 'like','%Compliance Report%']
            ])->get();
        foreach ($compliance as $c) $report = Arr::prepend($report,$c);
        $ppp = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'like','%PPP%']
        ])->get();
        foreach ($ppp as $p) $report = Arr::prepend($report,$p);
        $narrative = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'like','%Narrative%']
        ])->get();
        foreach ($narrative as $n) $report = Arr::prepend($report,$n);

        return response()->json($report);
    }

    public function showFileTF($id,$Userid){
        $applied_program = ApplicationProgram::where('id', $id)->first();
        if(Str::contains($applied_program->level, 'Level IV'))
            $area_name = array('RESEARCH','PERFORMANCE OF GRADUATES','COMMUNITY SERVICE','INTERNATIONAL LINKAGES AND CONSORTIA','PLANNING');
        elseif(Str::contains($applied_program->level, 'Level III'))
            $area_name = array('INSTRUCTION','EXTENSION','RESEARCH','FACULTY','LICENSURE EXAM', 'CONSORTIA OR LINKAGE', 'LIBRARY');
        else $area_name = array('Area I','Area II','Area III','Area IV','Area V','Area VI','Area VII','Area VIII','Area IX','Area X');
//        $area_name = array('Area I','Area II','Area III','Area IV','Area V','Area VI','Area VII','Area VIII','Area IX','Area X');

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
                ['application_program_id', $id], ['type', 'like','%Narrative%'], ['area', $area_name[$area_number->area_number-1]]
            ])->get();
            foreach ($narrative as $n) $report = Arr::prepend($report,$n);
        }
        return response()->json($report);
    }

    public function showFileIA($id,$Userid){
        $applied_program = ApplicationProgram::where('id', $id)->first();
        if(Str::contains($applied_program->level, 'Level IV'))
            $area_name = array('RESEARCH','PERFORMANCE OF GRADUATES','COMMUNITY SERVICE','INTERNATIONAL LINKAGES AND CONSORTIA','PLANNING');
        elseif(Str::contains($applied_program->level, 'Level III'))
            $area_name = array('INSTRUCTION','EXTENSION','RESEARCH','FACULTY','LICENSURE EXAM', 'CONSORTIA OR LINKAGE', 'LIBRARY');
        else $area_name = array('Area I','Area II','Area III','Area IV','Area V','Area VI','Area VII','Area VIII','Area IX','Area X');
//        $area_name = array('Area I','Area II','Area III','Area IV','Area V','Area VI','Area VII','Area VIII','Area IX','Area X');
        $areas = AssignedUser::where([
            ['app_program_id', $id], ['user_id', $Userid]
        ])->get();

        $report = array();
        foreach ($areas as $area){
            $instrument = InstrumentProgram::where('id', $area->transaction_id)->first();
            $area_number = AreaInstrument::where('id', $instrument->area_instrument_id)->first();

            $compliance = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'Compliance Report'], ['area', $area_name[$area_number->area_number-1]], ['status', 'approved']
            ])->get();
            foreach ($compliance as $c) $report = Arr::prepend($report,$c);
            $ppp = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'PPP'], ['area', $area_name[$area_number->area_number-1], ['status', 'approved']]
            ])->get();
            foreach ($ppp as $p) $report = Arr::prepend($report,$p);
            $narrative = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'like','%Narrative%'], ['area', $area_name[$area_number->area_number-1], ['status', 'approved']]
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
        $internal_rated_instruments = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'like', 'Internal Rated Instrument']
        ])->get();
        foreach ($internal_rated_instruments as $instrument) $report = Arr::prepend($report,$instrument);
        foreach ($sfr as $s) $report = Arr::prepend($report,$s);
        return response()->json($report);
    }

    public function showFileEA($id,$Userid){
        $applied_program = ApplicationProgram::where('id', $id)->first();
        if(Str::contains($applied_program->level, 'Level IV'))
            $area_name = array('RESEARCH','PERFORMANCE OF GRADUATES','COMMUNITY SERVICE','INTERNATIONAL LINKAGES AND CONSORTIA','PLANNING');
        elseif(Str::contains($applied_program->level, 'Level III'))
            $area_name = array('INSTRUCTION','EXTENSION','RESEARCH','FACULTY','LICENSURE EXAM', 'CONSORTIA OR LINKAGE', 'LIBRARY');
        else $area_name = array('Area I','Area II','Area III','Area IV','Area V','Area VI','Area VII','Area VIII','Area IX','Area X');
//        $area_name = array('Area I','Area II','Area III','Area IV','Area V','Area VI','Area VII','Area VIII','Area IX','Area X');
        $areas = AssignedUser::where([
            ['app_program_id', $id], ['user_id', $Userid]
        ])->get();

        $report = array();
        foreach ($areas as $area){
            $instrument = InstrumentProgram::where('id', $area->transaction_id)->first();
            $area_number = AreaInstrument::where('id', $instrument->area_instrument_id)->first();

            $compliance = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'Compliance Report'], ['area', $area_name[$area_number->area_number-1]],['status', 'approved']
            ])->get();
            foreach ($compliance as $c) $report = Arr::prepend($report,$c);
            $ppp = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'PPP'], ['area', $area_name[$area_number->area_number-1]], ['status', 'approved']
            ])->get();
            foreach ($ppp as $p) $report = Arr::prepend($report,$p);
            $narrative = ApplicationProgramFile::where([
                ['application_program_id', $id], ['type', 'like','%Narrative%'], ['area', $area_name[$area_number->area_number-1]], ['status', 'approved']
            ])->get();
            foreach ($narrative as $n) $report = Arr::prepend($report,$n);
        }
        $sar = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type','like', 'SAR']
        ])->get();
        foreach ($sar as $s) $report = Arr::prepend($report,$s);
        $sfr = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type','like' ,'SFR']
        ])->get();
        $internal_rated_instruments = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'like', 'Internal Rated Instrument']
        ])->get();
        $rated_instruments = ApplicationProgramFile::where([
            ['application_program_id', $id], ['type', 'like', 'Rated Instrument']
        ])->get();
        foreach ($internal_rated_instruments as $instrument) $report = Arr::prepend($report,$instrument);
        foreach ($rated_instruments as $instrument) $report = Arr::prepend($report,$instrument);
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
            ['application_program_id', $id], ['type', 'like','%Narrative%']
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

    public function changeProgramFileStatus(request $request){
        foreach ($request->file_ids as $file_id){
            $file = ApplicationProgramFile::where('id',$file_id)->first();
            if(is_null($file)) return response()->json(['status' => false, 'message' => 'id '.$file_id. ' does not exist']);
            $file->status = $request->status;
            $file->save();
        }
        return response()->json(['status' => true, 'message' => 'Successfully changed the file status.']);
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
        $instrumentPrograms = new Collection();
        $instruments =  InstrumentProgram::where('program_id', $id)->get();
        $program = Program::where('id', $id)->first();
        foreach ($instruments as $instrument){
            $area = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
            $area_type = AreaMandatory::where([
                ['area_instrument_id',$area->id], ['program_status',$program->type]
            ])->first();
            if(is_null($area_type)) $type = null;
            elseif($area_type->type == 'Mandatory') $type = 'Mandatory';
            elseif($area_type->type == 'Optional') $type = 'Optional';
            $templates = ProgramReportTemplate::where('instrument_program_id', $instrument->id)->get();
            $collection = new Collection();
            foreach ($templates as $template){
                $report_temp = ReportTemplate::where('id', $template->report_template_id)->first();
                $collection->push([
                    'id' => $report_temp->id,
                    'link' => $report_temp->link,
                    'template_name' => $report_temp->template_name,
                ]);
            }
            $instrumentPrograms->push([
                'id' => $instrument->id,
                'program_id' => $instrument->program_id,
                'area_instrument_id' => $instrument->area_instrument_id,
                'created_at' => $instrument->created_at,
                'updated_at' => $instrument->updated_at,
                'intended_program_id' => $area->intended_program_id,
                'area_number' => $area->area_number,
                'area_name' => $area->area_name,
                'version' => $area->version,
                'report_templates' => $collection,
                'type' => $type
            ]);
        }
        if(count($instrumentPrograms) < 0 ) return response()->json(['status' => false, 'message' => 'Do not have instruments']);
        $users = array();
        $program= null;
        foreach ($instrumentPrograms as $instrumentProgram){
            $assigned_users = DB::table('users')
                ->join('assigned_users', 'users.id', '=', 'assigned_users.user_id')
                ->where('assigned_users.transaction_id', $instrumentProgram['id'])
                ->where('assigned_users.status', null)
                ->get();
            foreach($assigned_users as $assigned_user){
                if ($assigned_user != null) $users = Arr::prepend($users, $assigned_user);
            }
            $intended_program = ProgramInstrument::where('id',$instrumentProgram['intended_program_id'])->first();
            if(!(is_null($intended_program))){
                $program = $intended_program->type_of_instrument;
                if(is_null($intended_program->type_of_instrument)) $program = $intended_program->intended_program;
            }
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
        if($request->status) $program->self_survey_status = 1;
        else $program->self_survey_status = 0;
        $program->save();

        $prog = Program::where('id', $program->program_id)->first();
        if($program->self_survey_status == 0) return response()->json(['status' => true, 'message' => "Self-survey for program " .$prog->program_name. " was successfully unlocked."]);
        elseif($program->self_survey_status == 1) return response()->json(['status' => true, 'message' => "Self-survey for program " .$prog->program_name. " was successfully locked."]);
    }

    public function showOptionArea($id){
        $collection = new Collection();
        $applied_program = ApplicationProgram::where('id', $id)->first();
        $program = Program::where('id', $applied_program->program_id)->first();
        if(Str::contains($applied_program->level, 'Level III')) $areas = AreaInstrument::where('intended_program_id', 42)->get();
        elseif(Str::contains($applied_program->level, 'Level IV')) $areas = AreaInstrument::where('intended_program_id', 47)->get();
        else $areas = [];
        foreach ($areas as $area){
            $area_mandatories = AreaMandatory::where('area_instrument_id', $area->id)->get();
            foreach ($area_mandatories as $area_mandatory) {
                if ($area_mandatory->type == 'Optional' && $program->type == $area_mandatory->program_status){
                    $collection->push([
                        'id' => $area->id,
                        'intended_program_id' => $area->intended_program_id,
                        'area_number' => $area->area_number,
                        'area_name' => $area->area_name,
                        'version' => $area->version,
                        'created_at' => $area->created_at,
                        'updated_at' => $area->updated_at
                    ]);
                }
            }
        }
        return response()->json($collection);
    }

    public function editLevel(request $request, $id){
        $applied_program = ApplicationProgram::where('id', $id)->first();
        $applied_program->level = $request->level;
        $success = $applied_program->save();
        if($success) return response()->json(['status' => true, 'message' => 'Success']);
        else return response()->json(['status' => false, 'message' => 'Unsuccessful']);
    }

    public function showAllAppliedProgram($id){
        $collection = new Collection();
        $programs = DB::table('campuses')
            ->join('programs', 'programs.campus_id', '=', 'campuses.id')
            ->where('campuses.id', $id)
            ->get();
        foreach($programs as $program){
            $checkPrograms = ApplicationProgram::where('program_id', $program->id)->get();
            foreach ($checkPrograms as $checkProgram){
                $collection->push($checkProgram);
            }
        }
        return response()->json(['programs' =>$collection]);
    }
}
