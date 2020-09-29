<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    protected $table="parameters";

    public function areaInstruments(){
        return $this->belongsToMany(AreaInstrument::class, 'statements_intermediaries')->withPivot('benchmark_statement_id');
    }

    public  function benchmarkStatements(){
        return $this->belongsToMany(BenchmarkStatement::class, 'statements_intermediaries')->withPivot('area_instrument_id');
    }
}
