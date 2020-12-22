<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    public function programs(){
        return $this->hasMany(Program::class);
    }

    public function users(){
        return $this->belongsToMany(User::class, 'campuses_users', 'campus_id', 'user_id');
    }

    public function offices(){
        return $this->hasMany(Office::class);
    }
}
