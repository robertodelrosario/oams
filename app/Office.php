<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $table="offices";
    public function campuses(){
        return $this->belongsTo(Campus::class);
    }
}
