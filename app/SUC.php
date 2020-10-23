<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SUC extends Model
{
    protected $table = "sucs";

    public function programs(){
        return $this->hasMany(Program::class);
    }
}
