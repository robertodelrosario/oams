<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BenchmarkStatement extends Model
{
    protected $table="benchmark_statements";

    public function areaInstruments(){
        return $this->belongsToMany(AreaInstrument::class, 'statements_intermediaries')->withPivot('parameter_id');
    }

    public function parameters(){
        return $this->belongsToMany(Parameter::class, 'statements_intermediaries')->withPivot('area_instrument_id');
    }
}
