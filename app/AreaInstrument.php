<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AreaInstrument extends Model
{
    protected $table = "area_instruments";

    public function programs(){
        return $this->belongsToMany(Program::class);
    }

    public  function benchmarkStatements(){
        return $this->belongsToMany(BenchmarkStatement::class, 'instruments_statements', 'area_instrument_id','benchmark_statement_id');
    }
    public function parameters(){
        return $this->belongsToMany(Parameter::class, 'instruments_parameters', 'area_instrument_id', 'parameter_id');
    }
}
