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

    public function campuses(){
        return $this->belongsTo(Campus::class);
    }
}
