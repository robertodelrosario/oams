<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    //
    protected $table = "programs";

    public function areaInstruments(){
        return $this->hasMany(AreaInstrument::class);
    }
}
