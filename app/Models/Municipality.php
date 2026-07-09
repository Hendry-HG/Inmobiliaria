<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    protected $fillable = [
        'name',
        'state_id'
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function parishes()
    {
        return $this->hasMany(Parish::class);
    }
}
