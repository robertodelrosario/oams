<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    protected $table="parameters";

    public static function find($parameter)
    {
    }

    public  function benchmarkStatements(){
        return $this->belongsToMany(BenchmarkStatement::class, 'parameters_statements', 'parameter_id','benchmark_statement_id');
    }

    public function areaInstruments(){
        return $this->belongsToMany(AreaInstrument::class,  'instruments_parameters', 'parameter_id', 'area_instrument_id');
    }
}
