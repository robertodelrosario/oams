<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BenchmarkStatement extends Model
{
    protected $table="benchmark_statements";

    public function areaInstruments(){
        return $this->belongsToMany(AreaInstrument::class,  'instruments_statements', 'benchmark_statement_id', 'area_instrument_id');
    }

    public function parameters(){
        return $this->belongsToMany(Parameter::class, 'parameters_statements', 'benchmark_statement_id', 'parameter_id');
    }
}
