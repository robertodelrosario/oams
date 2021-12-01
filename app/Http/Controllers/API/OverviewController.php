<?php

namespace App\Http\Controllers\API;

use App\ApplicationProgram;
use App\AreaInstrument;
use App\AreaMean;
use App\AssignedUser;
use App\Http\Controllers\Controller;
use App\InstrumentProgram;
use App\Program;
use App\RequiredRating;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OverviewController extends Controller
{
    public function showProgramSAR($app_prog, $role){
        $check = ApplicationProgram::where('id', $app_prog)->first();
        $program = Program::where('id', $check->program_id)->first();

        $transactions = array();
        if($role == 0)  //internal accreditor
        {
            $areas = AssignedUser::where([
                ['app_program_id', $app_prog], ['role', 'like', '%internal accreditor%']
            ])->get();
        }
        else{       //external accreditor
            $areas = array();
            $areas_list = AssignedUser::where('app_program_id', $app_prog)->get();
            foreach ($areas_list as $area_list){
                if(Str::contains($area_list->role, '%external accreditor%')){
                    $areas = Arr::prepend($areas, $area_list);
                }
            }
        }

        foreach ($areas as $area){
            if(!(in_array($area->transaction_id, $transactions))) $transactions= Arr::prepend($transactions,$area->transaction_id);
        }
        $weight = array(0,8,8,8,5,4,5,3,4,5);
        $sar = new Collection();
        foreach ($areas as $area) {
            $mean_score = AreaMean::where([
                ['instrument_program_id',$area->transaction_id],['assigned_user_id', $area->id]])->first();
            {
                if (!(is_null($mean_score))) {
                    for ($x = 0; $x < 10; $x++) {
                        $instrument = InstrumentProgram::where('id', $area->transaction_id)->first();
                        $area_instrument = AreaInstrument::where('id', $instrument->area_instrument_id)->first();
                        if($area_instrument->area_number == $x+1){
                            $sar->push([
                                'instrument_program_id' => $instrument->id,
                                'area_number' => $area_instrument->area_number,
                                'area' => $area_instrument->area_name,
                                'weight' => $weight[$x],
                                'area_mean' => round($mean_score->area_mean,2),
                                'weighted_mean' => round($mean_score->area_mean * $weight[$x],2)]);
                        }

                    }
                }
            }
        }
        $result = new Collection();

        $total_weight = 0;
        $total_area_mean = 0;
        $total_weighted_mean = 0;
        foreach ($sar as $s){
            $total_area_mean += $s['area_mean'];
            $total_weighted_mean += $s['weighted_mean'];
        }
        foreach ($weight as $w){
            $total_weight += $w;
        }
        $grand_mean  = $total_weighted_mean/$total_weight;
        $area_mean = 0;
        if($areas->count() != 0) $area_mean = $total_area_mean/$areas->count();
        $result->push(['total_weight' => $total_weight, 'total_area_mean' => round($total_area_mean, 2),'area_mean' => round($area_mean, 2), 'total_weighted_mean' => round($total_weighted_mean,2), 'grand_mean' => round($grand_mean,2)]);


        if($check->level == 'Candidate'){
            $required_rating = RequiredRating::where('accreditation_status', 'Candidate')->first();
        }
        elseif ($check->level == 'Level I')
        {
            $required_rating = RequiredRating::where('accreditation_status', 'Accredited Level I')->first();
        }
        elseif ($check->level == 'Level II')
        {
            $required_rating = RequiredRating::where('accreditation_status', 'Accredited Level II')->first();
        }
        elseif ($check->level == 'Level III')
        {
            $required_rating = RequiredRating::where('accreditation_status', 'Accredited Level III')->first();
        }
        elseif ($check->level == 'Level IV')
        {
            $required_rating = RequiredRating::where('accreditation_status', 'Accredited Level IV')->first();
        }

        return response()->json(['program' => $program,'areas' => $sar, 'result' => $result, 'require_ratings' => $required_rating]);
    }
}
