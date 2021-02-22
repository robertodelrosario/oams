<?php

namespace App\Http\Controllers\API;

use App\AssignedUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OverviewController extends Controller
{
    public function showInstrument($id){
        $instruments = AssignedUser::where('app_program_id', $id)->get();
        $instrument_ids = array();
        foreach ($instruments as $instrument){
            if(!(in_array($instrument->transaction_id, $instrument_ids))) $instrument_ids = Arr::prepend($instrument->transaction_id,$instrument_ids);
        }
        $areas = array();
        foreach ($instrument_ids as $instrument_id){
            $instrument = DB::table('instruments_programs')
                ->join('area_instruments', 'area_instruments.id', '=', 'instruments_programs.area_instrument_id')
                ->where('instruments_programs.id', $instrument_id)
                ->select('instruments_programs.*', 'programs.program_name', 'area_instruments.intended_program_id', 'area_instruments.area_number', 'area_instruments.area_name')
                ->first();
            $areas = Arr::prepend($areas,$instrument);
        }
    }
}
