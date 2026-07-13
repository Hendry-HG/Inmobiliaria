<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parish extends Model
{
    protected $fillable = [
        'name',
        'municipality_id'
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'parish_id');
    }

}
