<?php

namespace App\Http\Controllers\API;

use App\AreaInstrument;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
            $area = "Level III - ".$instrument->area_name;
            $collection->push($area);
        }
        $instruments = AreaInstrument::where('intended_program_id', 47)->get();
        foreach ($instruments as $instrument){
            $area = "Level IV - ".$instrument->area_name;
            $collection->push($area);
        }
        return response()->json($collection);
    }

}
