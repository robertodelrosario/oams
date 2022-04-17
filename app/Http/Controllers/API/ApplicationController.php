<?php

namespace App\Http\Controllers\API;

use App\Application;
use App\ApplicationFile;
use App\ApplicationProgram;
use App\AreaInstrument;
use App\AreaMandatory;
use App\AreaMean;
use App\AssignedUser;
use App\AssignedUserHead;
use App\Campus;
use App\CampusUser;
use App\Http\Controllers\Controller;
use App\InstrumentParameter;
use App\InstrumentProgram;
use App\InstrumentStatement;
use App\Office;
use App\OfficeUser;
use App\ParameterProgram;
use App\Program;
use App\ProgramReportTemplate;
use App\ProgramStatement;
use App\ReportTemplate;
use App\SUC;
use App\TemplateTag;
use App\UserRole;
use Carbon\Carbon;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth');
    }*/

    public function createApplication(request $request, $sucID, $userID){
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if($validator->fails()) return response()->json(['status' => false, 'message' => 'Cannot process creation. Required data needed']);

        $application = new Application();
        $application->suc_id=$sucID;
        $application->sender_id = $userID;
        $application->title = $request->title;
        $application->status = 'under preparation';
        $application->save();
        $message = 0;
        $count = count($request->programs);
        for($x=0; $x<$count; $x++){
            $check = ApplicationProgram::where([
                ['application_id', $application->id], ['program_id', $request->programs[$x]['program_id']]
            ])->first();
            if(is_null($check)){
                $program = new ApplicationProgram();
                $program->application_id = $application->id;
                $program->program_id = $request->programs[$x]['program_id'];
                $program->level = $request->programs[$x]['level'];
                $program->preferred_start_date = \Carbon\Carbon::parse($request->programs[$x]['preferred_start_date'])->format('Y-m-d');
                $program->preferred_end_date = \Carbon\Carbon::parse($request->programs[$x]['preferred_end_date'])->format('Y-m-d');
                $program->status = "pending";
                $success = $program->save();
                if(!($success)) dd($application);

                $prog = Program::where('id', $request->programs[$x]['program_id'])->first();
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
                $instrument = InstrumentProgram::where('program_id', $program->program_id);
                $instrument->delete();

                if(Str::contains($program->level, 'Candidate')){
                    $areas = AreaInstrument::where('intended_program_id', 48)->get();
                    foreach ($areas as $area){
                        $instrumentProgram = new InstrumentProgram();
                        $instrumentProgram->program_id = $request->programs[$x]['program_id'];
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
                }
                else{
                    $areas = [];
                    if(Str::contains($program->level, 'Level III')) {
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
                                $instrumentProgram->program_id = $request->programs[$x]['program_id'];
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
                }
            }
            else $message=1;
        }
        if($message == 0) return response()->json(['status' => true, 'message' => 'Successful', 'application' => $application]);
        else return response()->json(['status' => true, 'message' => 'Successfully added programs with no duplication.', 'application' => $application]);
    }

    public function editApplication(request $request, $id){
        $application = Application::where('id', $id)->first();
        if($application->status == 'under preparation'){
            $application->title = $request->title;
            $success = $application->save();
            if ($success) return response()->json(['status' => true, 'message' => 'Successfully updated application']);
            else return response()->json(['status' => false, 'message' => 'Unsuccessfully updated application. Invalid input!']);
        }
        else return response()->json(['status' => false, 'message' => 'Unsuccessfully updated application.']);
    }

    public function submitApplication($id, $sucID){
        $application = Application::where('id', $id)->first();
        echo $application;
        $files = ApplicationFile::where('application_id', $application->id)->first();

        $messages = null;
        $messages_1 = null;
        if(is_null($files)){
            $messages_1 ='Need to attach application files.';
        }
        $programs = ApplicationProgram::where('application_id', $id)->get();
        if(!(is_null($programs))){
            $count = count($programs);
            for ($x=0; $x<$count; $x++){
                $instrument = InstrumentProgram::where('program_id', $programs[$x]['program_id'])->first();
                if(is_null($instrument)){
                    if($x+1 == $count){
                        $messages = $messages .$programs[$x]['id']. ' ';
                    }
                    else $messages = $messages .$programs[$x]['id']. ', ';
                }
            }
        }

        if (is_null($messages)){
            $application->status = 'pending';
            $application->save();
            return response()->json(['status' => true, 'message' => 'Successful', 'application' => $application]);
        }
        $messages = $messages_1.' Applied program ID number '.$messages.'has a missing attached instruments.';
        return response()->json(['status' => false, 'message' => $messages]);

//        $suc = SUC::where('id', $sucID)->first();
//        $details = [
//            'title' => 'Application Notification for Accreditation',
//            'body' => 'Please check your AOMS account to view the application',
//            'suc' => $suc->institution_name,
//            'address' => $suc->address,
//            'email' => $suc->email,
//            'link' =>'http://online_accreditation_management_system.test/api/v1/aaccup/showApplication'
//        ];
//        \Mail::to('roberto.delrosario@ustp.edu.ph')->send(new ApplicationNotification($details));
        return response()->json(['status' => true, 'message' => 'Successful', 'application' => $application]);
    }

    public function deleteApplication($id){
        $application = Application::where('id', $id)->first();
        if($application->sender_id == auth()->user()->id){
            if($application->status == 'under preparation' || $application->status == 'done'){
                $application->delete();
                return response()->json(['status' => true, 'message' => 'Application successfully deleted!']);
            }
            return response()->json(['status' => false, 'message' => 'Application is on going.']);
        }
        $user = User::where('id', $application->sender_id)->first();
        return response()->json(['status' => false, 'message' => 'Only '.$user->first_name. ' '.$user->last_name. ' can delete the application']);
    }

    public function showApplication($id){
        //$applications = Application::where('suc_id', $id)->get();
        $collections = new Collection();
        $applications = Application::where('suc_id', $id)->get();
        foreach ($applications as $application){
            $suc = SUC::where('id', $application->suc_id)->first();
            $user = User::where('id', $application->sender_id)->first();
            $campus_user = CampusUser::where('user_id', $application->sender_id)->first();
            $campus = Campus::where('id', $campus_user->campus_id)->first();
            $collections->push([
                'id' => $application['id'],
                'suc_id' => $application['suc_id'],
                'title' => $application['title'],
                'sender_id' => $application['sender_id'],
                'status' => $application['status'],
                'created_at' => $application['created_at'],
                'updated_at' => $application['updated_at'],
                'institution_name' => $suc->institution_name,
                'address' => $suc->address,
                'email' => $suc->email,
                'contact_no' => $suc->contact_no,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'campus_name' => $campus->campus_name
            ]);
        }
//        $applications = DB::table('applications')
//            ->join('sucs', 'sucs.id', '=', 'applications.suc_id')
//            ->join('users', 'users.id', '=','applications.sender_id')
//            ->where('applications.suc_id', $id)
//            ->select('applications.*', 'sucs.institution_name', 'sucs.address', 'sucs.email', 'sucs.contact_no', 'users.first_name', 'users.last_name')
//            ->get();
        $file_arr = array();
        foreach ($collections as $collection){
            $files = ApplicationFile::where('application_id',$collection['id'])->get();
            foreach ($files as $file){
                $file_arr = Arr::prepend($file_arr,$file);
            }
        }
        return response()->json(['applications' => $collections, 'files' => $file_arr]);
    }


    public function uploadFile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required',
            'filename.*' => 'mimes:doc,pdf,docx,zip'
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Acceptable file types are .doc,.pdf,.docx, and .zip']);

        if ($request->hasfile('filename')) {
            foreach ($files = $request->file('filename') as $file) {
                $application = new ApplicationFile();
                $fileName = $file->getClientOriginalName();
                $filePath = $file->storeAs('application/files', $fileName);
                $application->file_title = $fileName;
                $application->file = $filePath;
                $application->application_id = $id;
                $application->save();

            }
            return response()->json(['status' => true, 'message' => 'Successfully added files!']);
        }
        return response()->json(['status' => false, 'message' => 'Unsuccessfully added files!']);
    }

    public function deleteFile($id){
        $file = ApplicationFile::where('id', $id)->first();
        File::delete(storage_path("app/".$file->file));
        $file->delete();
        return response()->json(['status' => true, 'message' => 'Successfully deleted file!']);
    }

    public function viewFile($id){
        $file_link = ApplicationFile::where('id', $id)->first();
        $file = File::get(storage_path("app/".$file_link->file));
        $type = File::mimeType(storage_path("app/".$file_link->file));

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }

    public function viewFileStream($id){
        $file_link = ApplicationFile::where('id', $id)->first();
        $file = File::get(storage_path("app/".$file_link->file));
        $type = File::mimeType(storage_path("app/".$file_link->file));

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }


    public function approveApplication(request $request, $id){
        $validator = Validator::make($request->all(), [
            'filename' => 'required',
            'end_of_validity' => 'required'
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Required inputs.']);

        $date = new Carbon();
        $applied_program = ApplicationProgram::where('id', $id)->first();
        if($applied_program->status == 'done')  return response()->json(['status' => false, 'message' => 'Application was already approved.']);
        $applied_program->status = 'done';
        $applied_program->result = 'accredited';
        $applied_program->date_granted = $date;
        $file = $request->file('filename');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->storeAs('application/files', $fileName);
        $applied_program->certificate = $filePath;


        $assigned_users = AssignedUser::where('app_program_id', $id)->get();
        $sum = 0;
        $weight = array(0,8,8,8,5,4,5,3,4,5);
        $scores = new Collection();
        if(count($assigned_users) < 1) return response()->json(['status' => false, 'message' => 'Application is not yet done.']);
        foreach ($assigned_users as $assigned_user){
            $assigned_user->status = 'done';
            $assigned_user->save();
            $area_mean = AreaMean::where('assigned_user_id', $assigned_user  ->id)->first();
            if(!(is_null($area_mean))){
                $instrument = InstrumentProgram::where('id', $area_mean->instrument_program_id)->first();
                $area_number = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
                for($x=0;$x < 10; $x++){
                    if($area_number->area_number == $x+1){
                        $scores->push(['weight' => $weight[$x], 'area_mean' => $area_mean->area_mean, 'weighted_mean' => $area_mean->area_mean * $weight[$x]]);
                        break;
                    }
                }
            }
        }
        $total_weight = 0;
        foreach ($scores as $score){
            $sum += $score['weighted_mean'];
            $total_weight += $score['weight'];
        }
        $program_mean = $sum/$total_weight;



        $program = Program::where('id',$applied_program->program_id)->first();
        if($applied_program->level == 'Level I') $program->accreditation_status = 'Level I Accredited';
        elseif ($applied_program->level == 'Level II') $program->accreditation_status = 'Level II Re-accredited';
        elseif ($applied_program->level == 'Level III, Phase 1') $program->accreditation_status = 'Level II Re-accredited';
        elseif ($applied_program->level == 'Level III, Phase 2') $program->accreditation_status = 'Level III Re-accredited';
        elseif ($applied_program->level == 'Level IV, Phase 1') $program->accreditation_status = 'Level III Re-accredited';
        elseif ($applied_program->level == 'Level IV, Phase 2') $program->accreditation_status = 'Level IV Re-accredited';
        $program->latest_applied_level = $applied_program->level;
        $program->rating_obtained = $program_mean;
        $program->save();

        $applied_program->save();
        return response()->json(['status' => true, 'message' => 'Successfully accredited the program.']);
    }

    public function disapproveApplication($id){
        $applied_program = ApplicationProgram::where('id', $id)->first();
        $applied_program->result = 'unaccredited';
        $applied_program->save();
        $assigned_users = AssignedUser::where('app_program_id', $id)->get();
        foreach ($assigned_users as $assigned_user){
            $assigned_user->status = 'done';
            $assigned_user->save();
        }
        return response()->json(['status' => true, 'message' => 'Unaccredited the program.']);
    }

    
}
