<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = [
        'name',
        'country_id'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'state_id');
    }
}
