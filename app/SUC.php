<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SUC extends Model
{
    public function programs(){
        return $this->hasMany(Program::class);
    }
}
