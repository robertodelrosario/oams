<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BenchmarkStatement extends Model
{
    protected $table="benchmark_statements";

    public function areaInstruments(){
        return $this->belongsToMany(AreaInstrument::class);
    }

    public function parameters(){
        return $this->belongsToMany(Parameter::class);
    }
}
