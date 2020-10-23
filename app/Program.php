<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    //
    protected $table = "programs";

    public function areaInstruments(){
        return $this->belongsToMany(AreaInstrument::class);
    }

    public function sucs(){
        return $this->belongsTo(SUC::class);
    }
}
