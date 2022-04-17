<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AreaInstrument;
use App\AreaMean;
use App\AssignedUser;
use App\BenchmarkStatement;
use App\Campus;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\InstrumentScore;
use App\Parameter;
use App\ParameterMean;
use App\ParameterProgram;
use App\Program;
use App\ProgramStatement;
use App\Recommendation;
use App\SFRInformation;
use App\SUC;
use App\User;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
//require_once '/var/www/html/oams/vendor/autoload.php';
////require_once 'C:\laragon\www\online_accreditation_management_system\vendor/autoload.php';
//use  \PhpOffice\PhpWord\PhpWord;

class ReportController extends Controller
{
    public function uploadAreaReport(request $request, $id, $userID){
        $area = AssignedUser::where([
            ['user_id', $userID], ['app_program_id', $id]
        ])->first();
        $success = false;
        if(!(is_null($request->sfr))){
            $fileName = $request->sfr->getClientOriginalName();
            $filePath = $request->file('sfr')->storeAs('reports', $fileName);
            $area->sfr_report = $filePath;
            $success = $area->save();
        }
        if(!(is_null($request->sar))){
            $fileName = $request->sar->getClientOriginalName();
            $filePath = $request->file('sar')->storeAs('reports', $fileName);
            $area->sar_report = $filePath;
            $success = $area->save();
        }
        if($success) return response()->json(['status' => true, 'message' => 'Successfully added report documents!']);
        else response()->json(['status' => false, 'message' => 'Unsuccessfully added report documents!']);
    }

    public function generateAreaSAR($id, $app_prog){
        $collections = new Collection();
        $count = 0;
        $checks = AssignedUser::where('app_program_id', $app_prog)->get();
        foreach ($checks as $check){
            if(Str::contains($check->role, 'external accreditor')) $count++;
        }
        if($count < 1)   return response()->json(['status' => false, 'message' => 'Empty']);
        $parameters = DB::table('parameters')
            ->join('parameters_programs', 'parameters_programs.parameter_id','=','parameters.id')
            ->select('parameters_programs.*', 'parameters.parameter')
            ->where('parameters_programs.program_instrument_id', $id)
            ->get();
        $mean_array = array();
        $accreditor = array();
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
                    if(!(in_array($mean->first_name .' '. $mean->last_name, $accreditor))) $accreditor = Arr::prepend($accreditor, ['role' => $mean->role, 'name' => $mean->first_name .' '. $mean->last_name]);
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
            elseif ($average >= 4.50) $rating = 'Excellent';

            if ($diff >= $gap) {
                $collections->push(['program_parameter_id' => $parameter->id, 'average_mean' => $average, 'difference' => $diff, 'status' => 'unaccepted', 'descriptive_rating' => $rating]);
            } else {
                $collections->push(['program_parameter_id' => $parameter->id, 'average_mean' => $average, 'difference' => $diff, 'status' => 'accepted', 'descriptive_rating'  => $rating]);
            }
            $total = $total + $average;
            if ($collections->count() != 0) $mean_ext = $total / $collections->count();
            else $mean_ext = 0;
        }

        foreach ($collections as $collection)
            if($collection['status'] == 'unaccepted') return response()->json(['status' => false, 'message' => 'An average paramenter mean is unacceptable with a difference of ' .$collection['difference']]);

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

        $instrument = InstrumentProgram::where('id', $id)->first();
        $area = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
        $program = Program::where('id', $instrument->program_id)->first();
        $pdf = PDF::loadView('areaSar', ['accreditors' =>$accreditor,'parameters'=>$parameters, 'means' => $mean_array, 'results'=> $collections, 'area_mean' => $area_mean, 'area' => $area, 'program' => $program]);
        return $pdf->download('sar.pdf');
    }

    public function generateAreaSARInternal($id, $app_prog){
        $collections = new Collection();
        $count = 0;
        $checks = AssignedUser::where('app_program_id', $app_prog)->get();
        foreach ($checks as $check){
            if(Str::contains($check->role, 'internal accreditor')) $count++;
        }
        if($count < 1)   return response()->json(['status' => false, 'message' => 'Empty']);
        $parameters = DB::table('parameters')
            ->join('parameters_programs', 'parameters_programs.parameter_id','=','parameters.id')
            ->select('parameters_programs.*', 'parameters.parameter')
            ->where('parameters_programs.program_instrument_id', $id)
            ->get();
        $mean_array = array();
        $accreditor = new Collection();
        foreach ($parameters as $parameter){
            $means = DB::table('parameters_means')
                ->join('assigned_users', 'assigned_users.id', '=','parameters_means.assigned_user_id')
                ->join('users', 'users.id', '=', 'assigned_users.user_id')
                ->where('parameters_means.program_parameter_id', $parameter->id)
                ->where('assigned_users.app_program_id', $app_prog)
                ->select('parameters_means.*', 'assigned_users.user_id','assigned_users.role' ,'users.first_name','users.last_name')
                ->get();
            foreach ($means as $mean) {
                if (Str::contains($mean->role, 'internal accreditor')) {
                    $mean_array = Arr::prepend($mean_array, $mean);
                    if(!($accreditor->contains( 'name', $mean->first_name .' '. $mean->last_name))) $accreditor->push(['role' => $mean->role, 'name' => $mean->first_name .' '. $mean->last_name]);
                }
            }
        }
        $total = 0;
        foreach ($parameters as $parameter) {
//            if ($parameter->acceptable_score_gap == null) $gap = 0;
//            else $gap = $parameter->acceptable_score_gap;
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
            elseif($average >= 4.50) $rating = 'Excellent';

            $collections->push(['program_parameter_id' => $parameter->id, 'average_mean' => $average, 'difference' => $diff, 'status' => 'accepted', 'descriptive_rating'  => $rating]);
//            if ($diff >= $gap) {
//                $collections->push(['program_parameter_id' => $parameter->id, 'average_mean' => $average, 'difference' => $diff, 'status' => 'unaccepted', 'descriptive_rating' => $rating]);
//            } else {
//                $collections->push(['program_parameter_id' => $parameter->id, 'average_mean' => $average, 'difference' => $diff, 'status' => 'accepted', 'descriptive_rating'  => $rating]);
//            }
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

        $instrument = InstrumentProgram::where('id', $id)->first();
        $area = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
        $program = Program::where('id', $instrument->program_id)->first();
        $pdf = PDF::loadView('areaSar', ['accreditors' =>$accreditor,'parameters'=>$parameters, 'means' => $mean_array, 'results'=> $collections, 'area_mean' => $area_mean, 'area' => $area, 'program' => $program]);
        return $pdf->download('sar.pdf');
    }

    public function generateProgramSAR($id, $app_prog){
        $check = ApplicationProgram::where('id', $app_prog)->first();
        $program = Program::where('id', $check->program_id)->first();
        $areas = AssignedUser::where([
            ['app_program_id', $app_prog], ['user_id', $id]
        ])->get();
        $instrument_array = array();
        $role = null;
        foreach ($areas as $area){
            $instrument = DB::table('instruments_programs')
                ->join('programs', 'programs.id', '=', 'instruments_programs.program_id')
                ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                ->where('instruments_programs.id', $area->transaction_id)
                ->select('instruments_programs.*', 'programs.program_name', 'area_instruments.intended_program_id', 'area_instruments.area_number', 'area_instruments.area_name')
                ->first();
            $role = $area->role;
            $instrument_array = Arr::prepend($instrument_array,$instrument);
        }

        if(Str::contains($check->level, 'Level III') || Str::contains($check->level, 'Level IV')){
            $assigned_users = AssignedUser::where('app_program_id', $app_prog)->get();
            $instruments_programs = InstrumentProgram::where('program_id', $program->id)->get();
            $internal_scores = new Collection();
            $external_scores = new Collection();
            $remarks_before_compliance = new Collection();
            $remarks_after_compliance = new Collection();
            $accreditors = new Collection();
            foreach ($instruments_programs as $instrument_program){
                $partial_internal_mean_scores = new Collection();
                $partial_external_mean_scores = new Collection();
                foreach ($assigned_users as $assigned_user){
                    if (Str::contains($assigned_user->role, 'accreditor')) {
                        $score = AreaMean::where([
                            ['instrument_program_id', $instrument_program->id], ['assigned_user_id', $assigned_user->id]
                        ])->first();
                        if (!(is_null($score))) {
                            if (Str::contains($assigned_user->role, 'external accreditor')) $partial_external_mean_scores->push(["instrument_program_id" => $score->instrument_program_id, "assigned_user_id" => $score->assigned_user_id, "area_mean" => round($score->area_mean, 2)]);
                            else $partial_internal_mean_scores->push(["instrument_program_id" => $score->instrument_program_id, "assigned_user_id" => $score->assigned_user_id, "area_mean" => round($score->area_mean, 2)]);
                        }
                    }
                }
                $average_internal_mean_score = 0;
                foreach ($partial_internal_mean_scores as $partial_internal_mean_score){
                    $average_internal_mean_score += $partial_internal_mean_score['area_mean'];
                }
                if($partial_internal_mean_scores->count() != 0) $average_internal_mean_score = $average_internal_mean_score / $partial_internal_mean_scores->count();
                else $average_internal_mean_score = 0;
                $internal_scores->push(["instrument_program_id" => $instrument_program->id, "area_mean" => round($average_internal_mean_score, 2)]);

                $average_external_mean_score = 0;

                foreach ($partial_external_mean_scores as $partial_external_mean_score){
                    $average_external_mean_score += $partial_external_mean_score->area_mean;
                }
                if($partial_external_mean_scores->count() != 0) $average_external_mean_score = $average_external_mean_score / $partial_external_mean_scores->count();
                else $average_external_mean_score = 0;
                $external_scores->push(["instrument_program_id" => $instrument_program->id, "area_mean" => round($average_external_mean_score, 2)]);
            }
            $sars = new Collection();
            $result = new Collection();

            if (Str::contains($role, 'external accreditor')) {
                foreach ($assigned_users as $assigned_user){
                    if (Str::contains($assigned_user->role, 'external accreditor'))
                    {
                        $user = User::where('id', $assigned_user->user_id)->first();
                        if(!($accreditors->contains('user_id', $user->id))) {
                            $accreditors->push([
                                'user_id' => $user->id,
                                'first_name' => $user->first_name,
                                'last_name' => $user->last_name
                            ]);
                        }
                    }
                }
                $total_area_mean = 0;
                foreach ($external_scores as $external_score) {
                    $instrument = InstrumentProgram::where('id', $external_score['instrument_program_id'])->first();
                    $area_instrument = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
                    $sars->push(['instrument_program_id' => $instrument->id, 'area_number' => $area_instrument->area_number,'area' => $area_instrument->area_name,'area_mean' => round($external_score['area_mean'], 2)]);
                    $total_area_mean += $external_score['area_mean'];
                }

                if($external_scores->count() != 0)$grand_mean = $total_area_mean / $external_scores->count();
                else $grand_mean = 0;

                if ($grand_mean < 1.50) $descriptive_result = 'Poor';
                elseif ($grand_mean < 2.50) $descriptive_result = 'Fair';
                elseif ($grand_mean < 3.50) $descriptive_result = 'Satisfactory';
                elseif ($grand_mean < 4.50) $descriptive_result = 'Very Satisfactory';
                elseif ($grand_mean >= 4.50) $descriptive_result = 'Excellent';
                $result->push(['total_area_mean' => round($total_area_mean, 2), 'grand_mean' => round($grand_mean, 2), 'descriptive_result' => $descriptive_result]);
                foreach ($instruments_programs as $instrument_program){
                    $remarks = SFRInformation::where([
                        ['application_program_id',$app_prog], ['instrument_program_id', $instrument_program->id], ['type', 'External']
                    ])->get();
                    if($remarks->count() > 0) {
                        foreach ($remarks as $remark) {
                            if($remark->remark_type == 'before_compliance') {
                                $remarks_before_compliance->push([
                                    'remark' => $remark->remark,
                                    'type' => $remark->remark_type
                                ]);
                            }
                            else{
                                $remarks_after_compliance->push([
                                    'remark' => $remark->remark,
                                    'type' => $remark->remark_type
                                ]);
                            }
                        }
                    }
                }
            }
            else {
                foreach ($assigned_users as $assigned_user) {
                    if (Str::contains($assigned_user->role, 'internal accreditor')) {
                        $user = User::where('id', $assigned_user->user_id)->first();

                        if(!($accreditors->contains('user_id', $user->id))) {
                            $accreditors->push([
                                'user_id' => $user->id,
                                'first_name' => $user->first_name,
                                'last_name' => $user->last_name
                            ]);
                        }
                    }
                }
                $total_area_mean = 0;
                foreach ($internal_scores as $internal_score) {
                    $instrument = InstrumentProgram::where('id', $internal_score['instrument_program_id'])->first();
                    $area_instrument = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
                    $sars->push(['instrument_program_id' => $instrument->id, 'area_number' => $area_instrument->area_number,'area' => $area_instrument->area_name,'area_mean' => round($internal_score['area_mean'],2)]);
                    $total_area_mean += $internal_score['area_mean'];
                }

                if($internal_scores->count() != 0) $grand_mean = $total_area_mean / $internal_scores->count();
                else $grand_mean = 0;

                if ($grand_mean < 1.50) $descriptive_result = 'Poor';
                elseif ($grand_mean < 2.50) $descriptive_result = 'Fair';
                elseif ($grand_mean < 3.50) $descriptive_result = 'Satisfactory';
                elseif ($grand_mean < 4.50) $descriptive_result = 'Very Satisfactory';
                elseif ($grand_mean >= 4.50) $descriptive_result = 'Excellent';
                $result->push(['total_area_mean' => round($total_area_mean, 2), 'grand_mean' => round($grand_mean, 2), 'descriptive_result' => $descriptive_result]);
                foreach ($instruments_programs as $instrument_program){
                    $remarks = SFRInformation::where([
                        ['application_program_id',$app_prog], ['instrument_program_id', $instrument_program->id], ['type', 'Internal']
                    ])->get();
                    if($remarks->count() > 0) {
                        foreach ($remarks as $remark) {
                            if($remark->remark_type == 'before_compliance') {
                                $remarks_before_compliance->push([
                                    'remark' => $remark->remark,
                                    'type' => $remark->remark_type
                                ]);
                            }
                            else{
                                $remarks_after_compliance->push([
                                    'remark' => $remark->remark,
                                    'type' => $remark->remark_type
                                ]);
                            }
                        }
                    }
                }
            }
            $date = date("Y-m-d");
            if(Str::contains($check->level, 'Level III')) $level = 'Level III';
            elseif(Str::contains($check->level, 'Level IV')) $level = 'Level IV';
            $campus = Campus::where('id', $program->campus_id)->first();
            $suc = SUC::where('id', $campus->suc_id)->first();
            $pdf = PDF::loadView('programSar_2', ['program' => $program, 'areas' => $sars, 'remarks_after_compliance' => $remarks_after_compliance, 'remarks_before_compliance' => $remarks_before_compliance,'result' => $result, 'date' => $date, 'level' => $level, 'suc' => $suc, 'accreditors' => $accreditors]);
            return $pdf->download($program->program_name . '_SAR.pdf');
        }
        else {
            $instruments = AssignedUser::where('app_program_id', $app_prog)->get();
            $area_mean_external = array();
            $area_mean_internal = array();

            foreach($instruments as $instrument){
                if(Str::contains($instrument->role, '[leader]') || Str::contains($instrument->role, 'area 7')){
                    $score = AreaMean::where([
                        ['instrument_program_id',$instrument->transaction_id], ['assigned_user_id', $instrument->id]
                    ])->first();
                    $area_mean_external = Arr::prepend($area_mean_external,$score);
                }
                elseif(Str::contains($instrument->role, 'internal accreditor')){
                    $score = AreaMean::where([
                        ['instrument_program_id',$instrument->transaction_id], ['assigned_user_id', $instrument->id]
                    ])->first();
                    if(!(is_null($score))) $area_mean_internal = Arr::prepend($area_mean_internal,$score);
                }
            }

            $weight = array(0, 8, 8, 8, 5, 4, 5, 3, 4, 5);
            $sars = new Collection();
            $result = new Collection();

            $total_weight = 0;
            $total_area_mean = 0;
            $total_weighted_mean = 0;

            foreach ($weight as $w) {
                $total_weight += $w;
            }

            if (Str::contains($role, 'external accreditor')) {

                foreach ($area_mean_external as $area) {
                    $instrument = InstrumentProgram::where('id', $area->instrument_program_id)->first();
                    $area_number = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
                    for ($x = 0; $x < 10; $x++) {
                        if ($area_number->area_number == $x + 1) {
                            $sars->push(['instrument_program_id' => $instrument->id, 'area_number' => $area_number->area_number, 'area' => $area_number->area_name, 'weight' => $weight[$x], 'area_mean' => round($area->area_mean,2), 'weighted_mean' => round($area->area_mean * $weight[$x], 2)]);
                            break;
                        }
                    }
                }

                foreach ($sars as $sar) {
                    $total_area_mean += $sar['area_mean'];
                    $total_weighted_mean += $sar['weighted_mean'];
                }

                $grand_mean = $total_weighted_mean / $total_weight;

                if ($grand_mean < 1.50) $descriptive_result = 'Poor';
                elseif ($grand_mean < 2.50) $descriptive_result = 'Fair';
                elseif ($grand_mean < 3.50) $descriptive_result = 'Satisfactory';
                elseif ($grand_mean < 4.50) $descriptive_result = 'Very Satisfactory';
                elseif ($grand_mean >= 4.50) $descriptive_result = 'Excellent';
                $result->push(['total_weight' => $total_weight, 'total_area_mean' => round($total_area_mean, 2), 'total_weighted_mean' => round($total_weighted_mean, 2), 'grand_mean' => round($grand_mean, 2), 'descriptive_result' => $descriptive_result]);

            } elseif (Str::contains($role, 'internal accreditor')) {
                foreach ($area_mean_internal as $area) {
                    $instrument = InstrumentProgram::where('id', $area->instrument_program_id)->first();
                    $area_number = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
                    for ($x = 0; $x < 10; $x++) {
                        if ($area_number->area_number == $x + 1) {
                            $sars->push(['instrument_program_id' => $instrument->id, 'area_number' => $area_number->area_number, 'area' => $area_number->area_name, 'weight' => $weight[$x], 'area_mean' => round($area->area_mean,2), 'weighted_mean' => round($area->area_mean * $weight[$x], 2)]);
                            break;
                        }
                    }
                }
                $total_area_mean = 0;
                $total_weighted_mean = 0;
                foreach ($sars as $sar) {
                    $total_area_mean += $sar['area_mean'];
                    $total_weighted_mean += $sar['weighted_mean'];
                }

                $grand_mean = $total_weighted_mean / $total_weight;

                if ($grand_mean < 1.50) $descriptive_result = 'Poor';
                elseif ($grand_mean < 2.50) $descriptive_result = 'Fair';
                elseif ($grand_mean < 3.50) $descriptive_result = 'Satisfactory';
                elseif ($grand_mean < 4.50) $descriptive_result = 'Very Satisfactory';
                elseif ($grand_mean >= 4.5) $descriptive_result = 'Excellent';
                $result->push(['total_weight' => $total_weight, 'total_area_mean' => round($total_area_mean, 2), 'total_weighted_mean' => round($total_weighted_mean, 2), 'grand_mean' => round($grand_mean, 2), 'descriptive_result' => $descriptive_result]);

            }

            $program_sar = new Collection();
            for ($x = 1; $x <= 10; $x++) {
                foreach ($sars as $sar) {
                    if ($x == $sar['area_number']) {
                        $program_sar->push(['area' => $sar['area'], 'weight' => $sar['weight'], 'mean' => $sar['area_mean'], 'weighted_mean' => $sar['weighted_mean']]);
                    }
                }
            }

            $pdf = PDF::loadView('programSar', ['program' => $program, 'areas' => $program_sar, 'result' => $result]);
            return $pdf->download($program->program_name . '_SAR.pdf');
            return response()->json(['program' => $program, 'areas' => $program_sar, 'result' => $result]);
        }
    }

    public function generateAccreditorReport($id){
        $applied_program = ApplicationProgram::where('id', $id)->first();
        $program = Program::where('id', $applied_program->program_id)->first();
        $area = AssignedUser::where([
            ['app_program_id', $id], ['user_id', auth()->user()->id]
        ])->first();
        $campus = Campus::where('id', $program->campus_id)->first();
        $suc = SUC::where('id', $campus->suc_id)->first();
        $accreditor = User::where('id', auth()->user()->id)->first();
        if (Str::contains($area->role, 'external accreditor')) $role = 'external accreditor';
        else $role = 'internal accreditor';
        $instruments = new Collection();
        $program_instruments = InstrumentProgram::where('program_id', $program->id)->get();
        foreach ($program_instruments as $program_instrument){
            $area_instrument = AreaInstrument::where('id', $program_instrument->area_instrument_id)->first();
            $instruments->push([
                'id' => $program_instrument->id,
                'program_id' => $program_instrument->program_id,
                'area_instrument_id' => $program_instrument->area_instrument_id,
                'program_name' => $program->program_name,
                'intended_program_id' => $area_instrument->intended_program_id,
                'area_number' => $area_instrument->area_number,
                'area_name' => $area_instrument->area_name,
            ]);
        }

        $assigned_users = AssignedUser::where([
            ['app_program_id', $id], ['role', 'like', '%'. $role.'%']
        ])->get();
        $scores = new Collection();
        $accreditor_area_mean_score = new Collection();
        $accreditor_total_score = new Collection();
        $recommendation_collection = new Collection();
        $accreditors = new Collection();
        $list_of_accreditor = new Collection();
        foreach ($assigned_users as $assigned_user){
            $user = User::where('id', $assigned_user->user_id)->first();
            $accreditors->push([
                'id' => $assigned_user->transaction_id,
                'first_name' =>  $user->first_name,
                'last_name' =>  $user->last_name,
            ]);
            if(!($list_of_accreditor->contains('id', $user->id))){
                $list_of_accreditor->push([
                    'id' =>  $user->id,
                    'first_name' =>  $user->first_name,
                    'last_name' =>  $user->last_name,
                ]);
            }
            $recommendations = Recommendation::where('assigned_user_id', $assigned_user->id)->get();
            foreach ($recommendations as $recommendation){
                $recommendation_collection->push([
                    'instrument_id' => $assigned_user->transaction_id,
                    'recommendation' => $recommendation->recommendation
                ]);
            }
        }

        foreach ($instruments as $instrument){
            $parameter = ParameterProgram::where('program_instrument_id', $instrument['id'])->first();
            $statements = ProgramStatement::where('program_parameter_id', $parameter->id)->get();
            $statement_scores = new Collection();
            $collection_id = new Collection();
            $collection_statements = new Collection();
            foreach ($statements as $statement) {
                $benchmark_statement = BenchmarkStatement::where('id', $statement->benchmark_statement_id)->first();
                if (!($collection_id->contains($statement->id))) {
                    $collection_id->push($statement->id);
                    $collection_statements->push([
                        'id' => $statement->id,
                        'instrument_parameter_id' => $statement->instrument_parameter_id,
                        'benchmark_statement_id' => $statement->benchmark_statement_id,
                        'parent_statement_id' => $statement->parent_statement_id,
                        'benchmark_statement' => $benchmark_statement->statement,
                        'degree' => 1
                    ]);

                    foreach ($statements as $statement_1) {
                        if(!($collection_id->contains($statement_1->id))) {
                            if ($statement->benchmark_statement_id == $statement_1->parent_statement_id) {
                                $collection_id->push($statement_1->id);
                                $benchmark_statement_1 = BenchmarkStatement::where('id', $statement_1->benchmark_statement_id)->first();
                                $collection_statements->push([
                                    'id' => $statement_1->id,
                                    'instrument_parameter_id' => $statement_1->instrument_parameter_id,
                                    'benchmark_statement_id' => $statement_1->benchmark_statement_id,
                                    'parent_statement_id' => $statement_1->parent_statement_id,
                                    'benchmark_statement' => $benchmark_statement_1->statement,
                                    'degree' => 2
                                ]);
                                foreach ($statements as $statement_2) {
                                    if(!($collection_id->contains($statement_2->id))) {
                                        if ($statement_1->benchmark_statement_id == $statement_2->parent_statement_id) {
                                            $collection_id->push($statement_2->id);
                                            $benchmark_statement_2 = BenchmarkStatement::where('id', $statement_2->benchmark_statement_id)->first();
                                            $collection_statements->push([
                                                'id' => $statement_2->id,
                                                'instrument_parameter_id' => $statement_2->instrument_parameter_id,
                                                'benchmark_statement_id' => $statement_2->benchmark_statement_id,
                                                'parent_statement_id' => $statement_2->parent_statement_id,
                                                'benchmark_statement' => $benchmark_statement_2->statement,
                                                'degree' => 3
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $area_mean = 0;
            $count = 0;
            foreach ($assigned_users as $assigned_user){
                $available = 0;
                $inadequate = 0;
                if($instrument['id'] == $assigned_user->transaction_id){
                    $user = User::where('id', $assigned_user->user_id)->first();
                    foreach ($statements as $statement){
                        $statement_score = InstrumentScore::where([
                            ['item_id', $statement->id], ['assigned_user_id', $assigned_user->id]
                            ])->first();
                        $statement_scores->push([
                            'item_id' =>  $statement->id,
                            'assigned_user_id' => $assigned_user->id,
                            'user_id' => $assigned_user->user_id,
                            'score' => $statement_score->item_score
                        ]);
                        if(is_null($statement->parent_statement_id)) {
                            if ($statement_score->item_score >= 3) $available = $available + $statement_score->item_score;
                            elseif ($statement_score->item_score >= 1) $inadequate = $inadequate + $statement_score->item_score;
                        }
                    }
                    $accreditor_total_score->push([
                        'instrument_id' => $instrument['id'],
                        'user_id' => $user->id,
                        'last_name'=> $user->last_name,
                        'available' => $available,
                        'inadequate' => $inadequate,
                    ]);
                }
                $accreditor_area_mean = AreaMean::where([
                    ['instrument_program_id', $instrument['id']], ['assigned_user_id',$assigned_user->id]
                ])->first();
                if(!(is_null($accreditor_area_mean))) {
                    $area_mean = $area_mean + $accreditor_area_mean->area_mean;
                    $count++;
                }
            }
            if($count != 0) $area_mean = $area_mean/$count;
            $accreditor_area_mean_score->push([
                'instrument_program_id' => $instrument['id'],
                'area_name' => $instrument['area_name'],
                'area_number' => $instrument['area_number'],
                'area_mean' => $area_mean
            ]);
            $sorted = $collection_statements->sortBy('benchmark_statement');
            foreach ($sorted as $collection_statement){
                $user_score = new Collection();
                foreach ($statement_scores as $ss){
                    if($collection_statement['id'] == $ss['item_id']){
                        $user = User::where('id', $ss['user_id'])->first();
                        $user_score->push([
                            'last_name' => $user->last_name,
                            'score' => $ss['score']
                        ]);
                    }
                }
                $scores->push([
                    'id' => $instrument['id'],
                    'statement' => $collection_statement['benchmark_statement'],
                    'degree' => $collection_statement['degree'],
                    'score' => $user_score
                ]);
            }
        }
        $area_total = 0;
        foreach ($accreditor_area_mean_score as $am){
            $area_total = $area_total + $am['area_mean'];
        }
        $grand_mean_total = $area_total/10;
        set_time_limit(500);
//        return response()->json(['program' => $program,'campus' => $campus, 'suc'=>$suc, 'accreditor' => $accreditor ,'areas' => $instruments, 'result' => $scores, 'recommendations' => $recommendation_collection, 'grand_mean'=> $accreditor_area_mean_score]);
        $pdf = PDF::loadView('accreditor_report', ['program' => $program,'applied_program' => $applied_program,'campus' => $campus, 'suc'=>$suc, 'accreditor' => $accreditor, 'list_of_accreditor' => $list_of_accreditor ,'areas' => $instruments, 'result' => $scores, 'recommendations' => $recommendation_collection, 'grand_mean'=> $accreditor_area_mean_score, 'accreditors' => $accreditors, 'total_score' => $accreditor_total_score, 'total' => $area_total, 'grand_mean_total'=> $grand_mean_total]);
        return $pdf->download($program->program_name . '_ACCREDITOR_REPORT.pdf');
    }

    public function generateAccreditorAreaReport($id, $instrument_id){
        $program_instrument = InstrumentProgram::where('id', $instrument_id)->first();
        $area_instrument = AreaInstrument::where('id', $program_instrument->area_instrument_id)->first();
        $applied_program = ApplicationProgram::where('id', $id)->first();
        $program = Program::where('id', $program_instrument->program_id)->first();
        $campus = Campus::where('id', $program->campus_id)->first();
        $suc = SUC::where('id', $campus->suc_id)->first();
        $accreditor = User::where('id', auth()->user()->id)->first();
        $user_task = AssignedUser::where([
            ['app_program_id', $id], ['user_id', auth()->user()->id]
        ])->first();
        if (Str::contains($user_task->role, 'external accreditor')) $role = 'external accreditor';
        else $role = 'internal accreditor';
        $assigned_users = AssignedUser::where([
            ['app_program_id', $id], ['transaction_id', $instrument_id], ['role', 'like', '%'. $role.'%']
        ])->get();
        $scores = new Collection();
        $accreditor_total_score = new Collection();
        $accreditor_area_mean_score = new Collection();

        $recommendation_collection = new Collection();

        $parameter = ParameterProgram::where('program_instrument_id', $program_instrument->id)->first();
        $statements = ProgramStatement::where('program_parameter_id', $parameter->id)->get();
        $statement_scores = new Collection();
        $collection_id = new Collection();
        $collection_statements = new Collection();
        foreach ($statements as $statement) {
            $benchmark_statement = BenchmarkStatement::where('id', $statement->benchmark_statement_id)->first();
            if (!($collection_id->contains($statement->id))) {
                $collection_id->push($statement->id);
                $collection_statements->push([
                    'id' => $statement->id,
                    'instrument_parameter_id' => $statement->instrument_parameter_id,
                    'benchmark_statement_id' => $statement->benchmark_statement_id,
                    'parent_statement_id' => $statement->parent_statement_id,
                    'benchmark_statement' => $benchmark_statement->statement,
                    'type' => $benchmark_statement->type,
                    'degree' => 1
                ]);

                foreach ($statements as $statement_1) {
                    if(!($collection_id->contains($statement_1->id))) {
                        if ($statement->benchmark_statement_id == $statement_1->parent_statement_id) {
                            $collection_id->push($statement_1->id);
                            $benchmark_statement_1 = BenchmarkStatement::where('id', $statement_1->benchmark_statement_id)->first();
                            $collection_statements->push([
                                'id' => $statement_1->id,
                                'instrument_parameter_id' => $statement_1->instrument_parameter_id,
                                'benchmark_statement_id' => $statement_1->benchmark_statement_id,
                                'parent_statement_id' => $statement_1->parent_statement_id,
                                'benchmark_statement' => $benchmark_statement_1->statement,
                                'type' => $benchmark_statement->type,
                                'degree' => 2
                            ]);
                            foreach ($statements as $statement_2) {
                                if(!($collection_id->contains($statement_2->id))) {
                                    if ($statement_1->benchmark_statement_id == $statement_2->parent_statement_id) {
                                        $collection_id->push($statement_2->id);
                                        $benchmark_statement_2 = BenchmarkStatement::where('id', $statement_2->benchmark_statement_id)->first();
                                        $collection_statements->push([
                                            'id' => $statement_2->id,
                                            'instrument_parameter_id' => $statement_2->instrument_parameter_id,
                                            'benchmark_statement_id' => $statement_2->benchmark_statement_id,
                                            'parent_statement_id' => $statement_2->parent_statement_id,
                                            'benchmark_statement' => $benchmark_statement_2->statement,
                                            'type' => $benchmark_statement->type,
                                            'degree' => 3
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $area_mean = 0;
        $count = 0;
        foreach ($assigned_users as $assigned_user){
            $available = 0;
            $inadequate = 0;
            if($program_instrument->id == $assigned_user->transaction_id){
                $user = User::where('id', $assigned_user->user_id)->first();
                foreach ($statements as $statement){
                    $statement_score = InstrumentScore::where([
                        ['item_id', $statement->id], ['assigned_user_id', $assigned_user->id]
                    ])->first();
                    $statement_scores->push([
                        'item_id' =>  $statement->id,
                        'assigned_user_id' => $assigned_user->id,
                        'user_id' => $assigned_user->user_id,
                        'score' => $statement_score->item_score
                    ]);
                    if(is_null($statement->parent_statement_id)) {
                        if ($statement_score->item_score >= 3) $available = $available + $statement_score->item_score;
                        elseif ($statement_score->item_score >= 1) $inadequate = $inadequate + $statement_score->item_score;
                    }
                }
                $accreditor_total_score->push([
                    'instrument_id' => $program_instrument->id,
                    'user_id' => $user->id,
                    'last_name'=> $user->last_name,
                    'available' => $available,
                    'inadequate' => $inadequate,
                ]);
            }
            $accreditor_area_mean = AreaMean::where([
                ['instrument_program_id', $program_instrument->id], ['assigned_user_id',$assigned_user->id]
            ])->first();
            if(!(is_null($accreditor_area_mean))) {
                $area_mean = $area_mean + $accreditor_area_mean->area_mean;
                $count++;
            }
        }
        if($count != 0) $area_mean = $area_mean/$count;
        $accreditor_area_mean_score->push([
            'instrument_program_id' => $program_instrument->id,
            'area_mean' => $area_mean
        ]);
        $sorted = $collection_statements->sortBy('benchmark_statement');
        foreach ($sorted as $collection_statement){
            $user_score = new Collection();
            foreach ($statement_scores as $ss){
                if($collection_statement['id'] == $ss['item_id']){
                    $user = User::where('id', $ss['user_id'])->first();
                    $user_score->push([
                        'last_name' => $user->last_name,
                        'score' => $ss['score']
                    ]);
                }
            }
            $scores->push([
                'id' => $program_instrument->id,
                'statement' => $collection_statement['benchmark_statement'],
                'degree' => $collection_statement['degree'],
                'score' => $user_score
            ]);
        }
//        $total_score = new Collection();
        $accreditors = new Collection();
        foreach ($assigned_users as $assigned_user){
            $user = User::where('id', $assigned_user->user_id)->first();
            $accreditors->push([
                'first_name' =>  $user->first_name,
                'last_name' =>  $user->last_name,
            ]);
            $recommendations = Recommendation::where('assigned_user_id', $assigned_user->id)->get();
            foreach ($recommendations as $recommendation){
                $recommendation_collection->push([
                    'instrument_id' => $assigned_user->transaction_id,
                    'recommendation' => $recommendation->recommendation
                ]);
            }
        }
        set_time_limit(300);
        $pdf = PDF::loadView('accreditor_area_report', ['program' => $program,'applied_program' => $applied_program,'campus' => $campus, 'suc'=>$suc, 'accreditor' => $accreditor ,'areas' => $area_instrument, 'result' => $scores, 'recommendations' => $recommendation_collection, 'grand_mean'=> $accreditor_area_mean_score, 'total_score' => $accreditor_total_score, 'accreditors' => $accreditors]);
        return $pdf->download($program->program_name .'_ACCREDITOR_REPORT.pdf');
    }

    public function downloadOBE($id, $instrument_id){
        $program_instrument = InstrumentProgram::where('id', $instrument_id)->first();
        $area_instrument = AreaInstrument::where('id', $program_instrument->area_instrument_id)->first();
        $applied_program = ApplicationProgram::where('id', $id)->first();
        $program = Program::where('id', $program_instrument->program_id)->first();
        $campus = Campus::where('id', $program->campus_id)->first();
        $suc = SUC::where('id', $campus->suc_id)->first();

//        $user_task = AssignedUser::where([
//            ['app_program_id', $id], ['user_id', 109], ['transaction_id', $instrument_id]
//        ])->first();
        $user_task = AssignedUser::where([
            ['app_program_id', $id], ['user_id', auth()->user()->id], ['transaction_id', $instrument_id]
        ])->first();
        if (Str::contains($user_task->role, 'external accreditor')) $role = 'external accreditor';
        else $role = 'internal accreditor';
        $assigned_users = AssignedUser::where([
            ['app_program_id', $id], ['transaction_id', $instrument_id], ['role', 'like', '%'. $role.'%']
        ])->get();

        $accreditor_list = new Collection();
        $area_means = new Collection();
        foreach($assigned_users as $assigned_user){
            $user = User::where('id', $assigned_user->user_id)->first();
            $accreditor_list->push(['id' => $user->id, 'first_name' => $user->first_name, 'last_name' => $user->last_name]);
            $area_mean = AreaMean::where('assigned_user_id', $assigned_user->id)->first();
            if(!(is_null($area_mean))){
                if($area_mean->area_mean < 1.5) $descriptive_rating = 'Poor';
                elseif($area_mean->area_mean < 2.5) $descriptive_rating = 'Fair';
                elseif($area_mean->area_mean < 3.5) $descriptive_rating = 'Good';
                elseif($area_mean->area_mean < 4.5) $descriptive_rating = 'Very Good';
                else $descriptive_rating = 'Excellent';
                $area_means->push([
                    'id' =>  $area_mean->id,
                    'instrument_program_id' =>  $area_mean->instrument_program_id,
                    'assigned_user_id' =>  $area_mean->assigned_user_id,
                    'area_mean' =>  $area_mean->area_mean,
                    'descriptive_rating' => $descriptive_rating
                ]);
            }
        }

        $parameters = ParameterProgram::where('program_instrument_id', $program_instrument->id)->get();
        $collection_id = new Collection();
        $statements_collection = new Collection();
        $parameter_collection = new Collection();
        $sorted_parameter = new Collection();
        foreach ($parameters as $parameter){
            $param = Parameter::where('id', $parameter->parameter_id)->first();
            $parameter_collection->push([
                'id' => $parameter->id,
                'parameter_id' => $parameter->parameter_id,
                'parameter' => $param->parameter
            ]);
        }
        $parameters = $parameter_collection->sortBy('parameter');
        foreach($parameters as $parameter){
            $collection_statements = new Collection();
            $statements = ProgramStatement::where('program_parameter_id', $parameter['id'])->get();
            foreach ($statements as $statement){
                $benchmark_statement = BenchmarkStatement::where('id', $statement->benchmark_statement_id)->first();
                if (!($collection_id->contains($statement->id))) {
                    $collection_id->push($statement->id);
                    $collection_statements->push([
                        'id' => $statement->id,
                        'parameter_id' => $parameter['parameter_id'],
                        'instrument_parameter_id' => $statement->instrument_parameter_id,
                        'benchmark_statement_id' => $statement->benchmark_statement_id,
                        'parent_statement_id' => $statement->parent_statement_id,
                        'benchmark_statement' => $benchmark_statement->statement,
                        'type' => $benchmark_statement->type,
                        'degree' => 1
                    ]);
                    foreach ($statements as $statement_1) {
                        if(!($collection_id->contains($statement_1->id))) {
                            if ($statement->benchmark_statement_id == $statement_1->parent_statement_id) {
                                $collection_id->push($statement_1->id);
                                $benchmark_statement_1 = BenchmarkStatement::where('id', $statement_1->benchmark_statement_id)->first();
                                $collection_statements->push([
                                    'id' => $statement_1->id,
                                    'parameter_id' => $parameter['parameter_id'],
                                    'instrument_parameter_id' => $statement_1->instrument_parameter_id,
                                    'benchmark_statement_id' => $statement_1->benchmark_statement_id,
                                    'parent_statement_id' => $statement_1->parent_statement_id,
                                    'benchmark_statement' => $benchmark_statement_1->statement,
                                    'type' => $benchmark_statement->type,
                                    'degree' => 2
                                ]);
                                foreach ($statements as $statement_2) {
                                    if(!($collection_id->contains($statement_2->id))) {
                                        if ($statement_1->benchmark_statement_id == $statement_2->parent_statement_id) {
                                            $collection_id->push($statement_2->id);
                                            $benchmark_statement_2 = BenchmarkStatement::where('id', $statement_2->benchmark_statement_id)->first();
                                            $collection_statements->push([
                                                'id' => $statement_2->id,
                                                'parameter_id' => $parameter['parameter_id'],
                                                'instrument_parameter_id' => $statement_2->instrument_parameter_id,
                                                'benchmark_statement_id' => $statement_2->benchmark_statement_id,
                                                'parent_statement_id' => $statement_2->parent_statement_id,
                                                'benchmark_statement' => $benchmark_statement_2->statement,
                                                'type' => $benchmark_statement->type,
                                                'degree' => 3
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $sorted = $collection_statements->sortBy('benchmark_statement');
            $sorted_statements = new Collection();
            foreach ($sorted as $collection_statement){
                if($collection_statement['type'] == 'System Input'){
                    $sorted_statements->push([
                        'id' => $collection_statement['id'],
                        'parameter_id' => $collection_statement['parameter_id'],
                        'instrument_parameter_id' => $collection_statement['instrument_parameter_id'],
                        'benchmark_statement_id' => $collection_statement['benchmark_statement_id'],
                        'parent_statement_id' => $collection_statement['parent_statement_id'],
                        'benchmark_statement' => $collection_statement['benchmark_statement'],
                        'type' => $collection_statement['type'],
                        'degree' => $collection_statement['degree'],
                    ]);
                }
            }
            foreach ($sorted as $collection_statement){
                if($collection_statement['type'] == 'Implementation'){
                    $sorted_statements->push([
                        'id' => $collection_statement['id'],
                        'parameter_id' => $collection_statement['parameter_id'],
                        'instrument_parameter_id' => $collection_statement['instrument_parameter_id'],
                        'benchmark_statement_id' => $collection_statement['benchmark_statement_id'],
                        'parent_statement_id' => $collection_statement['parent_statement_id'],
                        'benchmark_statement' => $collection_statement['benchmark_statement'],
                        'type' => $collection_statement['type'],
                        'degree' => $collection_statement['degree'],
                    ]);
                }
            }
            foreach ($sorted as $collection_statement){
                if($collection_statement['type'] == 'Outcome'){
                    $sorted_statements->push([
                        'id' => $collection_statement['id'],
                        'parameter_id' => $collection_statement['parameter_id'],
                        'instrument_parameter_id' => $collection_statement['instrument_parameter_id'],
                        'benchmark_statement_id' => $collection_statement['benchmark_statement_id'],
                        'parent_statement_id' => $collection_statement['parent_statement_id'],
                        'benchmark_statement' => $collection_statement['benchmark_statement'],
                        'type' => $collection_statement['type'],
                        'degree' => $collection_statement['degree'],
                    ]);
                }
            }
            foreach ($sorted_statements as $s){
                $item_scores = new Collection();
                foreach ($assigned_users as $assigned_user){
                    $user = User::where('id', $assigned_user->user_id)->first();
                    $statement_score = InstrumentScore::where([
                        ['item_id', $s['id']], ['assigned_user_id', $assigned_user->id]
                    ])->first();

                    $item_scores->push([
                        'id' => $user->id,
                        'last_name' => $user->last_name,
                        'score' => $statement_score->item_score
                    ]);
                }
                $statements_collection->push([
                    'id' => $s['id'],
                    'parameter_id' => $s['parameter_id'],
                    'instrument_parameter_id' => $s['instrument_parameter_id'],
                    'benchmark_statement_id' => $s['benchmark_statement_id'],
                    'parent_statement_id' => $s['parent_statement_id'],
                    'benchmark_statement' => $s['benchmark_statement'],
                    'type' => $s['type'],
                    'degree' => $s['degree'],
                    'score' => $item_scores
                ]);
            }
        }

        foreach ($parameters as $parameter){
            $system_input_collection = new Collection();
            $implementation_collection = new Collection();
            $outcome_collection = new Collection();
            $param_mean_collection = new Collection();
            foreach ($assigned_users as $assigned_user){
                $system_input = 0;
                $implementation = 0;
                $outcome = 0;
                $system_input_count = 0;
                $implementation_count = 0;
                $outcome_count = 0;
                $user = User::where('id', $assigned_user->user_id)->first();
                foreach ($statements_collection as $sc){
                    if($parameter['parameter_id'] == $sc['parameter_id']){
                        if($sc['parent_statement_id'] == null){
                            foreach ($sc['score'] as $score){
                                if($score['id'] == $user->id){
                                    if($sc['type'] == 'System Input'){
                                        $system_input += $score['score'];
                                        $system_input_count++;
                                    }
                                    elseif ($sc['type'] == 'Implementation'){
                                        $implementation += $score['score'];
                                        $implementation_count++;
                                    }
                                    else{
                                        $outcome += $score['score'];
                                        $outcome_count++;
                                    }
                                }
                            }
                        }
                    }
                }
                $system_input_collection->push([
                    'id' => $user->id,
                    'last_name' =>  $user->last_name,
                    'score' => round($system_input/$system_input_count,2)
                ]);
                $implementation_collection->push([
                    'id' => $user->id,
                    'last_name' =>  $user->last_name,
                    'score' => round($implementation/$implementation_count,2)
                ]);
                $outcome_collection->push([
                    'id' => $user->id,
                    'last_name' =>  $user->last_name,
                    'score' => round($outcome/$outcome_count,2)
                ]);
                $parameter_mean = ParameterMean::where([
                    ['program_parameter_id', $parameter['id']], ['assigned_user_id', $assigned_user->id]
                ])->first();
                if($parameter_mean->parameter_mean < 1.5) $descriptive_rating = 'Poor';
                elseif($parameter_mean->parameter_mean < 2.5) $descriptive_rating = 'Fair';
                elseif($parameter_mean->parameter_mean < 3.5) $descriptive_rating = 'Good';
                elseif($parameter_mean->parameter_mean < 4.5) $descriptive_rating = 'Very Good';
                else $descriptive_rating = 'Excellent';
                $param_mean_collection->push([
                    'id' => $user->id,
                    'last_name' => $user->last_name,
                    'parameter_mean' => $parameter_mean->parameter_mean,
                    'descriptive_rating' => $descriptive_rating
                ]);
            }
            $sorted_parameter->push([
                'id' => $parameter['id'],
                'parameter_id' => $parameter['parameter_id'],
                'parameter' => $parameter['parameter'],
                'parameter_mean' => $param_mean_collection,
                'system_input' => $system_input_collection,
                'implementation' => $implementation_collection,
                'outcome' => $outcome_collection
            ]);
        }
        $total = new Collection();
        foreach ($assigned_users as $assigned_user){
            $total_mean = 0;
            foreach($sorted_parameter as $sp){
                foreach ($sp['parameter_mean'] as $pm){
                    if($assigned_user->user_id == $pm['id']){
                        $total_mean += $pm['parameter_mean'];
                    }
                }
            }
            $total->push([
                'id' => $assigned_user->user_id,
                'total' => $total_mean,
            ]);
        }

//        return response()->json(['statements' => $statements_collection, 'parameter_results' => $sorted_parameter,'parameters' =>  $parameters,'accreditors' => $accreditor_list, 'instrument' => $area_instrument]);
        set_time_limit(300);
        $pdf = PDF::loadView('download_OBE', ['statements' => $statements_collection, 'parameter_results' => $sorted_parameter,'accreditors' => $accreditor_list, 'instrument' => $area_instrument, 'area_means' => $area_means, 'total_parameter_means' => $total])->setPaper('a4');
        return $pdf->download($program->program_name .'_OBE.pdf');
    }

    public function generateProgramSFR($id, $role){
        $assignedUsers = AssignedUser::where('app_program_id', $id)->get();
        if($role == 0) $role_str = 'internal accreditor';
        elseif($role == 1) $role_str = 'external accreditor';
        elseif($role == 1) $role_str = 'external accreditor';
        $transactions = array();
        foreach ($assignedUsers as $assignedUser){
            $tran = DB::table('area_instruments')
                ->join('instruments_programs', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                ->where('instruments_programs.id', $assignedUser->transaction_id)
                ->first();
            if(!(in_array($tran, $transactions))) $transactions= Arr::prepend($transactions,$tran);
        }
        $program = "";
        $bestpractice_array = array();
        $remark_strength_array = array();
        $remark_weakness_array = array();
        $recommendation_array = array();
        $empty = array();

        $collection_user = new Collection();
        $test = array();
        foreach ($transactions as $transaction){
            $tasks = AssignedUser::where([
                ['transaction_id', $transaction->id], ['app_program_id', $id]
            ])->get();

            $area = DB::table('area_instruments')
                ->join('instruments_programs', 'area_instruments.id','=', 'instruments_programs.area_instrument_id')
                ->where('instruments_programs.id',$transaction->id)
                ->first();

            $program = Program::where('id', $area->program_id)->first();
            foreach ($tasks as $task){
                if(Str::contains($task->role, $role_str))
                {
                    $test= Arr::prepend($test,$task);
                    $user = User::where('id', $task->user_id)->first();
                    $bestpractices = DB::table('assigned_users')
                        ->join('best_practices', 'best_practices.assigned_user_id','=', 'assigned_users.id')
                        ->where('assigned_users.id', $task->id)
                        ->get();
                    foreach ($bestpractices as $bestpractice) $bestpractice_array = Arr::prepend($bestpractice_array,$bestpractice->best_practice);

                    $remarks = DB::table('assigned_users')
                        ->join('instruments_scores', 'instruments_scores.assigned_user_id', '=', 'assigned_users.id')
                        ->where('assigned_users.id', $task->id)
                        ->where('instruments_scores.remark', '!=', null)
                        ->get();
                    if(count($remarks) > 0)
                        foreach ($remarks as $remark)
                        {
                            if($remark->remark_type == 'Strength') $remark_strength_array = Arr::prepend($remark_strength_array, $remark->remark);
                            elseif($remark->remark_type == 'Weakness') $remark_weakness_array = Arr::prepend($remark_weakness_array, $remark->remark);
                        }

                    $recommendations = DB::table('assigned_users')
                        ->join('recommendations', 'assigned_users.id', '=', 'recommendations.assigned_user_id')
                        ->where('assigned_users.id', $task->id)
                        ->get();
                    foreach ($recommendations as $recommendation) $recommendation_array = Arr::prepend($recommendation_array,$recommendation->recommendation);

                    $collection_user->push([
                        'instrument_program_id' => $area->id,
                        'area_name' => $area->area_name,
                        'user_name' => $user->first_name. " ".$user->last_name,
                        'best_practices' => $bestpractice_array,
                        'strength_remarks' => $remark_strength_array,
                        'weakness_remarks' => $remark_weakness_array,
                        'recommendations' => $recommendation_array
                    ]);
                    $bestpractice_array = $empty;
                    $remark_strength_array = $empty;
                    $remark_weakness_array= $empty;
                    $recommendation_array= $empty;
                }
            }
        }

        $program_sfr = new Collection();
        for($x = 1; $x<=10 ; $x++){
            foreach ($transactions as $transaction){
                if($x == $transaction->area_number){
                    $program_sfr->push(['id' => $transaction->id, 'area_number' => $transaction->area_number, 'area_name' => $transaction->area_name]);
                }
            }
        }

        $pdf = PDF::loadView('program_sfr', ['program' => $program, 'instrument_programs' => $program_sfr, 'collections' => $collection_user]);
        return $pdf->download($program->program_name. '_SFR.pdf');
        return response()->json(['program' => $program, 'instrument_programs' => $program_sfr, 'collections' => $collection_user]);
    }

    public function saveSFR(request $request, $programID,$instrumentID){
        $remarks = SFRInformation::where([
            ['application_program_id',$programID], ['instrument_program_id', $instrumentID], ['type', $request->role]
        ])->get();
        foreach ($remarks as $remark ) $remark->delete();
        foreach($request->sfr as $s){
            if(is_null($s['remark'])) continue;
            $check = SFRInformation::where([
                ['application_program_id',$programID], ['instrument_program_id', $instrumentID], ['remark',$s['remark']], ['remark_type', $s['type']], ['type', $request->role]
            ])->first();
            if(is_null($check)){
                $remark = new SFRInformation();
                $remark->application_program_id = $programID;
                $remark->instrument_program_id = $instrumentID;
                $remark->remark = $s['remark'];
                $remark->remark_type = $s['type'];
                $remark->type = $request->role;
                $remark->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Successfully saved remarks.']);
    }

    public function viewSFR($programID, $instrumentID, $role){
        if($role == 0) $role_str = 'Internal';
        elseif($role == 1) $role_str = 'External';
        $collection = new Collection();
        $remarks = SFRInformation::where([
            ['application_program_id',$programID], ['instrument_program_id', $instrumentID], ['type', $role_str]
        ])->get();
        foreach ($remarks as $remark) $collection->push(['remark' => $remark->remark, 'type' => $remark->remark_type]);
        return response()->json($collection);
    }

    public function showSFR($id, $role){

        if($role == 0) $role_str = 'Internal';
        elseif($role == 1) $role_str = 'External';
        $collection = new Collection();
        $strengths = array();
        $weaknesses = array();
        $recommendations = array();
        $empty = array();
        $program = ApplicationProgram::where('id',$id)->first();
        $prog = Program::where('id', $program->program_id)->first();
        $instruments = InstrumentProgram::where('program_id', $program->program_id)->get();
        foreach ($instruments as $instrument){
            $remarks = SFRInformation::where([
                ['application_program_id', $id], ['instrument_program_id', $instrument->id], ['type', $role_str]
            ])->get();
            foreach ($remarks as $remark){
                if($remark->remark_type == 'Strength'){
                    $strengths = Arr::prepend($strengths,$remark->remark);
                }
                elseif($remark->remark_type == 'Weakness'){
                    $weaknesses = Arr::prepend($weaknesses,$remark->remark);
                }
                elseif($remark->remark_type == 'Recommendation'){
                    $recommendations = Arr::prepend($recommendations,$remark->remark);
                }
            }
            $area = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
            $collection->push([
                'area_number' => $area->area_number,
                'area_name' => $area->area_name,
                'strengths' => $strengths,
                'weaknesses' => $weaknesses,
                'recommendations' => $recommendations,
            ]);
            $strengths = $empty;
            $weaknesses = $empty;
            $recommendations = $empty;
        }

//        $phpWord = new PhpWord();
//        $section = $phpWord->addSection();
//
//        $styleFont = array('align'=>\PhpOffice\PhpWord\Style\Cell::VALIGN_CENTER);
//        $styleFont2 = array('space' => array('before' => 1000, 'after' => 100));
//        $styleFont3 = array('indentation' => array('left' => 540, 'right' => 120), 'space' => array('before' => 360, 'after' => 280));
//        $styleFont4 = array('indentation' => array('left' => 1000, 'right' => 120));
//
//        $section->addText(
//            "SUMMARY OF FINDINGS AND RECOMMENDATIONS",array('bold' => true, 'size' => 14),
//            $styleFont
//        );
//        $section->addText(
//            $prog->program_name,array('bold' => true, 'size' => 14),
//            $styleFont
//        );
//
//        foreach ($collection as $c){
//            $section->addText(
//                $c['area_name'],array('bold' => true),
//                $styleFont2
//            );
//
//            $x = 1;
//            $section->addText(
//                'Strength/s',[],
//                $styleFont3
//            );
//            foreach($c['strengths'] as $s){
//                $section->addText(
//                    $x.'. '. $s,[],
//                    $styleFont4
//                );
//                $x++;
//            }
//            $x = 1;
//            $section->addText(
//                'Areas Needing Improvement',[],
//                $styleFont3
//            );
//            foreach($c['weaknesses'] as $w){
//                $section->addText(
//                    $x.'. '. $w,[],
//                    $styleFont4
//                );
//                $x++;
//            }
//            $x = 1;
//            $section->addText(
//                'Recommendation/s',[],
//                $styleFont3
//            );
//            foreach($c['recommendations'] as $r){
//                $section->addText(
//                    $x.'. '. $r,[],
//                    $styleFont4
//                );
//                $x++;
//            }
//
//            $section->addPageBreak();
//            $section->addText(
//                "SUMMARY OF FINDINGS AND RECOMMENDATIONS",array('bold' => true, 'size' => 10),
//                $styleFont
//            );
//            $section->addText(
//                $program->program_name,array('bold' => true, 'size' => 10),
//                $styleFont
//            );
//        }
//
//
//        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
//        $objWriter->save($prog->program_name. '_SFR.docx');
//        return response()->download(public_path($prog->program_name. '_SFR.docx'));
//
        $pdf = PDF::loadView('sfr', ['program' => $prog,  'collections' => $collection]);
        return $pdf->stream($prog->program_name. '_SFR.pdf');
    }
}
