<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'phone_code', 'phone_format', 'phone_min_length', 'phone_max_length'
    ];

    // Relaciones
    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    // Obtener formato de máscara para el teléfono
    public function getPhoneMaskAttribute()
    {
        if ($this->phone_format) {
            return $this->phone_format;
        }

        // Formatos por defecto según código de país
        $defaults = [
            '58' => '000-0000000',      // Venezuela: 412 1234567
            '1' => '000-000-0000',       // USA/Canadá: 123-456-7890
            '52' => '000-000-0000',      // México: 55 1234 5678
            '54' => '000-000-0000',      // Argentina: 11 1234-5678
            '57' => '000-000-0000',      // Colombia: 300 123 4567
            '34' => '000-000-000',       // España: 612 345 678
            '56' => '0-0000-0000',       // Chile: 9 1234 5678
            '51' => '000-000-000',       // Perú: 987 654 321
        ];

        return $defaults[$this->phone_code] ?? '000-000-0000';
    }

    // Obtener longitud máxima del teléfono local (sin código de país)
    public function getPhoneMaxLengthAttribute()
    {
        if ($this->attributes['phone_max_length'] ?? null) {
            return $this->attributes['phone_max_length'];
        }

        // Longitudes por país (solo dígitos locales)
        $lengths = [
            '58' => 10,  // Venezuela: 4121234567 = 10 dígitos
            '1' => 10,   // USA: 1234567890 = 10 dígitos
            '52' => 10,  // México: 10 dígitos
            '54' => 10,  // Argentina: 10 dígitos
            '57' => 10,  // Colombia: 10 dígitos
            '34' => 9,   // España: 9 dígitos
        ];

        return $lengths[$this->phone_code] ?? 10;
    }
}
