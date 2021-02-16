<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\ApplicationProgramFile;
use App\AreaMean;
use App\AssignedUser;
use App\BestPractice;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function uploadAreaReport(request $request, $id, $userID){
        $area = AssignedUser::where([
            ['user_id', $userID], ['app_program_id', $id]
        ])->first();

        if(!(is_null($request->sfr))){
            $fileName = $request->sfr->getClientOriginalName();
            $filePath = $request->file('sfr')->storeAs('reports', $fileName);
            $area->sfr_report = $filePath;
        }
        if(!(is_null($request->sar))){
            $fileName = $request->sar->getClientOriginalName();
            $filePath = $request->file('sar')->storeAs('reports', $fileName);
            $area->sar_report = $filePath;
        }
        $area->save();
        return response()->json(['status' => true, 'message' => 'Successfully added report documents!']);
    }

    public function uploadProgramReport(request $request, $id, $userID){
        $report = new ApplicationProgramFile();
        $report->application_program_id = $id;
        $report->uploader_id = $userID;
        $fileName = $request->file->getClientOriginalName();
        $filePath = $request->file('file')->storeAs('reports', $fileName);
        $report->file_title = $fileName;
        $report->file = $filePath;
        $report->type = $report->type;
        $report->save();
        return response()->json(['status' => true, 'message' => 'Successfully added report documents!']);
    }

    public function generateAreaSAR($id, $app_prog){
        $collections = new Collection();
        $collections_internal = new Collection();
        $parameters = DB::table('parameters')
            ->join('parameters_programs', 'parameters_programs.parameter_id','=','parameters.id')
            ->select('parameters_programs.*', 'parameters.parameter')
            ->where('parameters_programs.program_instrument_id', $id)
            ->get();
        $mean_array = array();
        foreach ($parameters as $parameter){
            $means = DB::table('parameters_means')
                ->join('assigned_users', 'assigned_users.id', '=','parameters_means.assigned_user_id')
                ->join('users', 'users.id', '=', 'assigned_users.user_id')
                ->where('parameters_means.program_parameter_id', $parameter->id)
                ->where('assigned_users.app_program_id', $app_prog)
                ->select('parameters_means.*', 'assigned_users.user_id','assigned_users.role' ,'users.first_name','users.last_name')
                ->get();
            foreach ($means as $mean) {
                if (Str::contains($mean->role, 'external accreditor')) {
                    $mean_array = Arr::prepend($mean_array, $mean);
                }
            }
        }
        $total = 0;
        foreach ($parameters as $parameter) {
            if ($parameter->acceptable_score_gap == null) $gap = 0;
            else $gap = $parameter->acceptable_score_gap;
            $diff = 0;
            $sum = 0;
            $count = 0;
            foreach ($mean_array as $mean) {
                if ($mean->program_parameter_id == $parameter->id) {
                    $diff = abs($diff - $mean->parameter_mean);
                    $sum = $sum + $mean->parameter_mean;
                    $count++;
                }
            }
            if ($count <= 1) $diff = 0;
            if ($count != 0) $average = $sum / $count;
            else $average = $sum;

            if($average < 1.50) $rating = 'Poor';
            elseif ($average < 2.50) $rating = 'Fair';
            elseif ($average < 3.50) $rating = 'Satisfactory';
            elseif ($average < 4.50) $rating = 'Very Satisfactory';
            else $rating = 'Excellent';

            if ($diff >= $gap) {
                $collections->push(['program_parameter_id' => $parameter->id, 'average_mean' => $average, 'difference' => $diff, 'status' => 'unaccepted', 'descriptive_rating' => $rating]);
            } else {
                $collections->push(['program_parameter_id' => $parameter->id, 'average_mean' => $average, 'difference' => $diff, 'status' => 'accepted', 'descriptive_rating'  => $rating]);
            }
            $total = $total + $average;
            if ($collections->count() != 0) $mean_ext = $total / $collections->count();
            else $mean_ext = 0;
        }
        $area_mean = new Collection();
        $area_mean->push(['total' => $total,'area_mean' => $mean_ext]);

        foreach($mean_array as $arr){
            if(Str::contains($arr->role, 'leader') || Str::contains($arr->role, 'area 7')){
                $mean = AreaMean::where([
                    ['instrument_program_id',$id], ['assigned_user_id', $arr->assigned_user_id]
                ])->first();
                if(!(is_null($mean))){
                    $mean->area_mean = $mean_ext;
                    $mean->save();
                }
            }
        }

//        $result_array= array();
//        foreach($collections as $collection) $result_array = Arr::prepend($result_array,$collection);
//        $pdf = PDF::loadView('areaSar', ['parameters'=>$parameters, 'means' => $mean_array, 'results'=> $collections, 'area_mean' => $area_mean]);
//        return $pdf->download('sar.pdf');
       return view('areaSar', ['parameters'=>$parameters, 'means' => $mean_array, 'results'=> $collections, 'area_mean' => $area_mean]);
    //    return response()->json(['parameters'=>$parameters, 'means' => $mean_array, 'results'=> $result_array, 'area_mean' => $area_mean]);
    }
}
