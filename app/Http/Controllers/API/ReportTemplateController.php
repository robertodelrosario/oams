<?php

namespace App\Http\Controllers\API;

use App\ApplicationFile;
use App\AreaInstrument;
use App\Http\Controllers\Controller;
use App\ReportTemplate;
use App\TemplateTag;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

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
        if ($request->hasfile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->storeAs('reporttemplates/files', $fileName);
            $template = new ReportTemplate();
            $template->campus_id = $id;
            $template->template_name = $request->template_name;
            $template->link = $filePath;
            $success = $template->save();
            if($success){
                foreach ($request->tags as $tag){
                    $template_tag = new TemplateTag();
                    $template_tag->report_template_id = $template->id;
                    $template_tag->tag = $tag;
                    $template_tag->save();
                }
                return response()->json(['status' => true, 'message'=>"Successfully added template."]);
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
}
