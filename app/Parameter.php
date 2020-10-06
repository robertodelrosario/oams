<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    protected $table="parameters";

    public  function benchmarkStatements(){
        return $this->belongsToMany(BenchmarkStatement::class);
    }
}
