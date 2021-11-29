<?php

namespace App\Http\Controllers\API;

use App\ApplicationFile;
use App\ApplicationProgram;
use App\AreaInstrument;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\Program;
use App\ProgramReportTemplate;
use App\ReportTemplate;
use App\TemplateTag;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReportTemplateController extends Controller
{
    public function showTagOption(){
        $collection = new Collection();
        $x = 6;
        do{
            $instruments = AreaInstrument::where('intended_program_id', $x)->get();
            if($instruments->count() > 0) break;
            else $x++;
        }
        while(true);

        foreach ($instruments as $instrument){
            $collection->push($instrument->area_name);
        }
        $instruments = AreaInstrument::where('intended_program_id', 42)->get();
        foreach ($instruments as $instrument){
            $area = "LEVEL III - ".$instrument->area_name;
            $collection->push($area);
        }
        $instruments = AreaInstrument::where('intended_program_id', 47)->get();
        foreach ($instruments as $instrument){
            $area = "LEVEL IV - ".$instrument->area_name;
            $collection->push($area);
        }
        return response()->json($collection);
    }

    public function addTemplate(request $request,$id){
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:doc,pdf,docx'
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'message' => 'Acceptable file types are .doc,.pdf, and .docx']);

        if ($request->hasfile('file')) {
            $template = ReportTemplate::where([
                ['campus_id', $id],['template_name',$request->template_name]
                ])->first();
            if(!(is_null($template))) return response()->json(['status' => false, 'message' => 'Template name already exist!']);
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->storeAs('reporttemplates/files', $fileName);
            $template = new ReportTemplate();
            $template->campus_id = $id;
            $template->template_name = $request->template_name;
            $template->link = $filePath;
            $success = $template->save();
            $programs = Program::where('campus_id', $id)->get();
            if($success){
                foreach ($request->tags as $tag){
                    $template_tag = new TemplateTag();
                    $template_tag->report_template_id = $template->id;
                    $template_tag->tag = $tag;
                    $template_tag->save();
                    $level = null;
                    foreach ($programs as $program){
                        $applied_programs = ApplicationProgram::where('program_id', $program->id)->get();
                        foreach($applied_programs as $applied_program){
                            $level = $applied_program->level;
                        }
                        if(!(is_null($level))) {
                            $intrument_programs = InstrumentProgram::where('program_id', $program->id)->get();
                            foreach ($intrument_programs as $intrument_program) {
                                $area = AreaInstrument::where('id', $intrument_program->area_instrument_id)->first();
                                if (Str::contains($level, 'Level III')) $core = 'LEVEL III -' . ' ' . $area->area_name;
                                elseif (Str::contains($level, 'Level IV')) $core = 'LEVEL IV -' . ' ' . $area->area_name;
                                else $core = $area->area_name;
                                if (Str::contains($core, $tag)) {
                                    $program_report_template = ProgramReportTemplate::where([
                                        ['report_template_id', $template->id], ['instrument_program_id', $intrument_program->id]
                                    ])->first();
                                    if (is_null($program_report_template)) {
                                        $program_report_template = new ProgramReportTemplate();
                                        $program_report_template->report_template_id = $template->id;
                                        $program_report_template->instrument_program_id = $intrument_program->id;
                                        $success_1 = $program_report_template->save();
                                    }
                                }
                            }
                        }
                    }
                }
                if($success_1) return response()->json(['status' => true, 'message'=>"Successfully added template."]);
                else return response()->json(['status' => false, 'message'=>"error"]);
            }
        }

        else return response()->json(['status' => false, 'message'=>"Unsuccessfully added template."]);
    }

    public function showTemplate($id){
        $collection = new Collection();
        $templates = ReportTemplate::where('campus_id', $id)->get();
        foreach ($templates as $template){
            $tags = TemplateTag::where('report_template_id', $template->id)->get();
            $collection->push([
                'id' => $template->id,
                'campus_id' => $template->campus_id,
                'link' => $template->link,
                'template_name' => $template->template_name,
                'tags' => $tags
            ]);
        }
        return response()->json($collection);
    }

    public function downloadFile($id){
        $file_link = ReportTemplate::where('id', $id)->first();
        $file = File::get(storage_path("app/".$file_link->link));
        $type = File::mimeType(storage_path("app/".$file_link->link));

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }

    public function deleteTemplate($id){
        $template = ReportTemplate::where('id', $id)->first();
        $success = $template->delete();
        if($success) return response()->json(['status' => true, "message" => 'Successfully deleted the template.']);
        else return response()->json(['status' => false, "message" => 'Unsuccessfully deleted the template.']);
    }

    public function addTemplateTag(request $request, $id){
        $success = false;
        $template = ReportTemplate::where('id', $id)->first();
        $programs = Program::where('campus_id', $template->campus_id)->get();
        foreach ($request->tags as $tag){
            $temp_tag = TemplateTag::where([
              ['tag', $tag], ['report_template_id', $id]
            ])->first();
            if(is_null($temp_tag)){
                $temp_tag = new TemplateTag();
                $temp_tag->tag = $tag;
                $temp_tag->report_template_id = $id;
                $success = $temp_tag->save();
                $level = null;
                    foreach ($programs as $program){
                        $applied_programs = ApplicationProgram::where('program_id', $program->id)->get();
                        foreach($applied_programs as $applied_program){
                            $level = $applied_program->level;
                        }
                        if(!is_null($level)) {
                            $intrument_programs = InstrumentProgram::where('program_id', $program->id)->get();
                            foreach ($intrument_programs as $intrument_program) {
                                $area = AreaInstrument::where('id', $intrument_program->area_instrument_id)->first();
                                if (Str::contains($level, 'Level III')) $core = 'LEVEL III -' . ' ' . $area->area_name;
                                elseif (Str::contains($level, 'Level IV')) $core = 'LEVEL IV -' . ' ' . $area->area_name;
                                else $core = $area->area_name;
                                if (Str::contains($core, $tag)) {
                                    $program_report_template = ProgramReportTemplate::where([
                                        ['report_template_id', $template->id], ['instrument_program_id', $intrument_program->id]
                                    ])->first();
                                    if (is_null($program_report_template)) {
                                        $program_report_template = new ProgramReportTemplate();
                                        $program_report_template->report_template_id = $id;
                                        $program_report_template->instrument_program_id = $intrument_program->id;
                                        $program_report_template->save();
                                    }
                                }
                            }
                        }
                }
            }
        }
        if($success) return response()->json(['status' => true, "message" => 'Successfully added tags for template.']);
        else return response()->json(['status' => false, "message" => 'Variable tags is either empty or tags already exist.']);
    }

    public function removeTemplateTag($id){
        $temp_tag = TemplateTag::where('id', $id)->first();
        $template = ReportTemplate::where('id', $temp_tag->report_template_id)->first();
        $programs = Program::where('campus_id', $template->campus_id)->get();
        $level = null;
        foreach ($programs as $program){
            $applied_programs = ApplicationProgram::where('program_id', $program->id)->get();
            foreach($applied_programs as $applied_program){
                $level = $applied_program->level;
            }
            if(!(is_null($level))) {
                $intrument_programs = InstrumentProgram::where('program_id', $program->id)->get();
                foreach ($intrument_programs as $intrument_program) {
                    $area = AreaInstrument::where('id', $intrument_program->area_instrument_id)->first();
                    if ($level == 'Level III') $core = 'LEVEL III -' . ' ' . $area->area_name;
                    elseif ($level == 'Level IV') $core = 'LEVEL IV -' . ' ' . $area->area_name;
                    else $core = $area->area_name;
                    if ($core == $temp_tag->tag) {
                        $program_report_template = ProgramReportTemplate::where([
                            ['report_template_id', $template->id], ['instrument_program_id', $intrument_program->id]
                        ]);
                        $program_report_template->delete();
                    }
                }
            }
        }
        $success = $temp_tag->delete();
        if($success) return response()->json(['status' => true, "message" => 'Successfully remove tag.']);
        else return response()->json(['status' => false, "message" => 'Unsuccessfully remove tag.']);
    }
}
