<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SUC extends Model
{
    protected $table = "sucs";

    public function programs(){
        return $this->hasMany(Program::class);
    }

    public function campuses(){
        return $this->hasMany(Campus::class);
    }

    public function users(){
        return $this->belongsToMany(User::class, 'users_sucs', 'suc_id', 'user_id');
    }
}
