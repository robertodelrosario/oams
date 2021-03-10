<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AreaInstrument;
use App\AreaMean;
use App\AssignedUser;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\Program;
use App\SFRInformation;
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

        $instruments = AssignedUser::where('app_program_id', $app_prog)->get();
        $area_mean_external = array();
        $area_mean_internal = array();

        foreach($instruments as $instrument){
            if(Str::contains($instrument->role, 'leader') || Str::contains($instrument->role, 'area 7')){
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

        $weight = array(0,8,8,8,5,4,5,3,4,5);
        $sars = new Collection();
        $result= new Collection();

        $total_weight = 0;
        $total_area_mean = 0;
        $total_weighted_mean = 0;

        foreach ($weight as $w){
            $total_weight += $w;
        }

        if(Str::contains($role, 'external accreditor')){

            foreach ($area_mean_external as $area){
                $instrument = InstrumentProgram::where('id', $area->instrument_program_id)->first();
                $area_number = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
                for($x=0;$x < 10; $x++){
                    if($area_number->area_number == $x+1){
                        $sars->push(['instrument_program_id' => $instrument->id,'area_number' => $area_number->area_number,'area' => $area_number->area_name, 'weight' => $weight[$x], 'area_mean' => $area->area_mean, 'weighted_mean' => $area->area_mean * $weight[$x]]);
                        break;
                    }
                }
            }

            foreach ($sars as $sar){
                $total_area_mean += $sar['area_mean'];
                $total_weighted_mean += $sar['weighted_mean'];
            }

            $grand_mean  = $total_weighted_mean/$total_weight;

            if($grand_mean < 1.50) $descriptive_result = 'Poor';
            elseif ($grand_mean < 2.50) $descriptive_result ='Fair';
            elseif ($grand_mean < 3.50) $descriptive_result ='Satisfactory';
            elseif ($grand_mean < 4.50) $descriptive_result ='Very Satisfactory';
            elseif ($grand_mean >= 4.50) $descriptive_result ='Excellent';
            $result->push(['total_weight' => $total_weight, 'total_area_mean' => round($total_area_mean, 2), 'total_weighted_mean' => round($total_weighted_mean,2), 'grand_mean' => round($grand_mean,2), 'descriptive_result' => $descriptive_result]);

        }
        elseif (Str::contains($role, 'internal accreditor')){
            foreach ($area_mean_internal as $area){
                $instrument = InstrumentProgram::where('id', $area->instrument_program_id)->first();
                $area_number = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
                for($x=0;$x < 10; $x++){
                    if($area_number->area_number == $x+1){
                        $sars->push(['instrument_program_id' => $instrument->id,'area_number' => $area_number->area_number,'area' => $area_number->area_name, 'weight' => $weight[$x], 'area_mean' => $area->area_mean, 'weighted_mean' => $area->area_mean * $weight[$x] ]);
                        break;
                    }
                }
            }
            $total_area_mean = 0;
            $total_weighted_mean = 0;
            foreach ($sars as $sar){
                $total_area_mean += $sar['area_mean'];
                $total_weighted_mean += $sar['weighted_mean'];
            }

            $grand_mean  = $total_weighted_mean/$total_weight;

            if($grand_mean < 1.50) $descriptive_result = 'Poor';
            elseif ($grand_mean < 2.50) $descriptive_result ='Fair';
            elseif ($grand_mean < 3.50) $descriptive_result ='Satisfactory';
            elseif ($grand_mean < 4.50) $descriptive_result ='Very Satisfactory';
            elseif($grand_mean >= 4.5) $descriptive_result = 'Excellent';
            $result->push(['total_weight' => $total_weight, 'total_area_mean' => round($total_area_mean, 2), 'total_weighted_mean' => round($total_weighted_mean,2), 'grand_mean' => round($grand_mean,2), 'descriptive_result' => $descriptive_result]);

        }

        $program_sar = new Collection();
        for($x = 1; $x<=10 ; $x++){
            foreach ($sars as $sar){
                if($x == $sar['area_number']){
                    $program_sar->push(['area' => $sar['area'], 'weight' => $sar['weight'], 'mean' => $sar['area_mean'], 'weighted_mean' => $sar['weighted_mean']]);
                }
            }
        }

        $pdf = PDF::loadView('programSar', ['program' => $program,'areas' => $program_sar,'result' =>$result]);
        return $pdf->download($program->program_name. '_SAR.pdf');
        return response()->json(['program' => $program,'areas' => $program_sar,'result' =>$result]);
    }

    public function generateProgramSFR($id, $role){
        $assignedUsers = AssignedUser::where('app_program_id', $id)->get();
        if($role == 0) $role_str = 'internal accreditor';
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

//        $phpWord = new \PhpOffice\PhpWord\PhpWord();
//        $section = $phpWord->addSection();
//
//        $styleFont = array('align'=>\PhpOffice\PhpWord\Style\Cell::VALIGN_CENTER);
//        $styleFont1 = array('space' => array('before' => 300, 'after' => 100));
//        $styleFont2 = array('space' => array('before' => 1000, 'after' => 100));
//        $styleFont3 = array('indentation' => array('left' => 540, 'right' => 120), 'space' => array('before' => 360, 'after' => 280));
//        $styleFont4 = array('indentation' => array('left' => 1000, 'right' => 120));
//
//        $section->addText(
//            "SUMMARY OF FINDINGS AND RECOMMENDATIONS",array('bold' => true, 'size' => 14),
//            $styleFont
//        );
//        $section->addText(
//            $program->program_name,array('bold' => true, 'size' => 14),
//            $styleFont
//        );
//
//        foreach ($program_sfr as $prog){
//            $section->addText(
//                $prog['area_name'],array('bold' => true),
//                $styleFont2
//            );
//            foreach ($collection_user as $u){
//                if($prog['id'] == $u['instrument_program_id']){
//                    $section->addText(
//                        $u['user_name'],array('bold' => true),
//                        $styleFont1
//                    );
//                    $x = 1;
//                    $section->addText(
//                        'Best Practice/s',[],
//                        $styleFont3
//                    );
//                    foreach($u['best_practices'] as $bp){
//                        $section->addText(
//                            $x.'. '. $bp,[],
//                            $styleFont4
//                        );
//                    }
//                    $x = 1;
//                    $section->addText(
//                        'Strength/s',[],
//                        $styleFont3
//                    );
//                    foreach($u['strength_remarks'] as $s){
//                        $section->addText(
//                            $x.'. '. $s,[],
//                            $styleFont4
//                        );
//                    }
//                    $x = 1;
//                    $section->addText(
//                        'Weakness/s',[],
//                        $styleFont3
//                    );
//                    foreach($u['weakness_remarks'] as $w){
//                        $section->addText(
//                            $x.'. '. $w,[],
//                            $styleFont4
//                        );
//                    }
//                    $x = 1;
//                    $section->addText(
//                        'Recommendation/s',[],
//                        $styleFont3
//                    );
//                    foreach($u['recommendations'] as $r){
//                        $section->addText(
//                            $x.'. '. $r,[],
//                            $styleFont4
//                        );
//                    }
//                }
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
//        $objWriter->save('helloWorld.docx');
//        return response()->download(public_path('helloWorld.docx'));

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
