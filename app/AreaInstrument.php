<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AreaInstrument extends Model
{
    protected $table = "area_instruments";

    public function programs(){
        return $this->belongsTo(Program::class);
    }

    public  function benchmarkStatements(){
        return $this->belongsToMany(BenchmarkStatement::class, 'statements_intermediaries')->withPivot('parameter_id');
    }

    public  function parameters(){
        return $this->belongsToMany(Parameter::class, 'statements_intermediaries')->withPivot('parameter_id')->withPivot('benchmark_statement_id');
    }
}
