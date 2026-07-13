<?php
// app/Models/User.php

namespace App\Models;

use App\Models\Appointment;
use App\Models\Favorite;
use App\Models\Lead;
use App\Models\Property;
use App\Models\UserNotification;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, CanResetPassword;

    protected $fillable = [
        // ============ DATOS PERSONALES ============
        'name',
        'last_name',
        'email',
        'password',
        'phone',
        
        // ============ PERFIL ============
        'profile_photo',
        'bio',
        'specialization',
        'social_links',
        
        // ============ IDENTIFICACIÓN ============
        'id_type',
        'id_number',
        
        // ============ UBICACIÓN ============
        'address',
        'country_id',
        'state_id',
        'municipality_id',
        'parish_id',
        'city_id',
        
        // ============ ESTADO ============
        'is_active',
        'is_online',
        'last_seen_at',
        'email_verified_at',
        
        // ============ SEGURIDAD ============
        'security_questions',      
        'security_answer_1',       
        'security_answer_2',       
        'security_answer_3',       
        'security_questions_set_at', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'security_answer_1',
        'security_answer_2',
        'security_answer_3',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'social_links' => 'array',
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
        'security_questions' => 'array', 
    ];

    // ==========================================
    // ACCESORS (GETTERS)
    // ==========================================

    public function getFullNameAttribute()
    {
        if ($this->last_name) {
            return $this->name . ' ' . $this->last_name;
        }
        return $this->name;
    }

    public function getInitialsAttribute()
    {
        $firstName = $this->name;
        $lastName = $this->last_name ?? '';

        $firstInitial = substr($firstName, 0, 1);

        if ($lastName) {
            $lastInitial = substr($lastName, 0, 1);
            return strtoupper($firstInitial . $lastInitial);
        }

        $words = explode(' ', $firstName);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($firstName, 0, 2));
    }

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&color=7F9CF5&background=EBF4FF&size=200';
    }

    public function getFullIdAttribute()
    {
        if ($this->id_type && $this->id_number) {
            return $this->id_type . '-' . $this->id_number;
        }
        return null;
    }

    // ==========================================
    // ACCESSORS DE UBICACIÓN
    // ==========================================

    public function getCountryNameAttribute()
    {
        return data_get($this, 'country.name', 'No especificado');
    }

    public function getStateNameAttribute()
    {
        return data_get($this, 'state.name', 'No especificado');
    }

    public function getMunicipalityNameAttribute()
    {
        return data_get($this, 'municipality.name', 'No especificado');
    }

    public function getParishNameAttribute()
    {
        return data_get($this, 'parish.name', 'No especificado');
    }

    public function getCityNameAttribute()
    {
        return data_get($this, 'userCity.name', 'No especificado');
    }

    public function getFullLocationAttribute()
    {
        $parts = [];

        $country = data_get($this, 'country.name');
        if ($country) $parts[] = $country;

        $state = data_get($this, 'state.name');
        if ($state) $parts[] = $state;

        $city = data_get($this, 'userCity.name');
        if ($city) $parts[] = $city;

        return !empty($parts) ? implode(', ', $parts) : 'No especificada';
    }

    // ==========================================
    // MÉTODOS DE SEGURIDAD
    // ==========================================

    public function hasSecurityQuestions(): bool
    {
        return !is_null($this->security_questions_set_at) &&
               !empty($this->security_questions) &&
               !empty($this->security_answer_1) &&
               !empty($this->security_answer_2) &&
               !empty($this->security_answer_3);
    }

    public function verifySecurityAnswer(int $questionIndex, string $answer): bool
    {
        $answerField = 'security_answer_' . ($questionIndex + 1);

        if (empty($this->$answerField)) {
            return false;
        }

        return Hash::check($answer, $this->$answerField);
    }

    public function getSecurityQuestionsWithIndex(): array
    {
        if (empty($this->security_questions)) {
            return [];
        }

        $questions = $this->security_questions;
        $result = [];

        foreach ($questions as $index => $question) {
            $result[] = [
                'index' => $index,
                'question' => $question,
                'answer_field' => 'security_answer_' . ($index + 1),
            ];
        }

        return $result;
    }

    // ==========================================
    // RELACIONES DE UBICACIÓN
    // ==========================================

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    public function userCity()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    // ==========================================
    // RELACIONES DEL SISTEMA
    // ==========================================

    public function properties()
    {
        return $this->hasMany(Property::class, 'user_id');
    }

    public function appointmentsAsClient()
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    public function appointmentsAsAsesor()
    {
        return $this->hasMany(Appointment::class, 'asesor_id');
    }

    // ==========================================
    // RELACIONES DE FAVORITOS
    // ==========================================

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    public function favoriteProperties()
    {
        return $this->belongsToMany(Property::class, 'favorites', 'user_id', 'property_id')
                    ->withTimestamps()
                    ->withPivot('id', 'created_at')
                    ->orderBy('favorites.created_at', 'desc');
    }

    public function favoritedProperties()
    {
        return $this->favoriteProperties();
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    // ==========================================
    // NOTIFICACIONES PERSONALIZADAS
    // ==========================================

    public function notifications()
    {
        return $this->hasMany(UserNotification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(UserNotification::class)->whereNull('read_at')->orderBy('created_at', 'desc');
    }

    public function readNotifications()
    {
        return $this->hasMany(UserNotification::class)->whereNotNull('read_at')->orderBy('created_at', 'desc');
    }

    public function createNotification($title, $message, $type = 'info', $url = null, $data = null)
    {
        return $this->notifications()->create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'url' => $url,
            'data' => $data,
        ]);
    }

    public function unreadNotificationsCount()
    {
        return $this->unreadNotifications()->count();
    }

    public function markAllNotificationsAsRead()
    {
        return $this->unreadNotifications()->update(['read_at' => now()]);
    }

    public function clearReadNotifications()
    {
        return $this->readNotifications()->delete();
    }

    public function clearAllNotifications()
    {
        return $this->notifications()->delete();
    }

    // ==========================================
    // MÉTODOS DE ROL (HELPERS)
    // ==========================================

    public function isSuperAdmin()
    {
        return $this->hasRole('Super Admin');
    }

    public function isAdmin()
    {
        return $this->hasRole('Administrador');
    }

    public function isAsesor()
    {
        return $this->hasRole('Asesor Inmobiliario');
    }

    public function isAuditor()
    {
        return $this->hasRole('Auditor');
    }

    public function isCliente()
    {
        return $this->hasRole('Cliente');
    }

    public function getMainRoleAttribute()
    {
        $role = $this->roles->first();
        return $role ? $role->name : 'Sin Rol';
    }

    // ==========================================
    // MÉTODOS DE FAVORITOS
    // ==========================================

    public function addFavorite(Property $property)
    {
        return $this->favoriteProperties()->syncWithoutDetaching([$property->id]);
    }

    public function removeFavorite(Property $property)
    {
        return $this->favoriteProperties()->detach($property->id);
    }

    public function hasFavorite(Property $property)
    {
        return $this->favoriteProperties()->where('property_id', $property->id)->exists();
    }

    public function getFavoriteIdsAttribute()
    {
        return $this->favoriteProperties()->pluck('property_id')->toArray();
    }

    public function getFavoritesCountAttribute()
    {
        return $this->favorites()->count();
    }

    // ==========================================
    // SCOPES (FILTROS DE CONSULTA)
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAsesores($query)
    {
        return $query->role('Asesor Inmobiliario');
    }

    public function scopeClientes($query)
    {
        return $query->role('Cliente');
    }

    public function scopeWithSecurityQuestions($query)
    {
        return $query->whereNotNull('security_questions_set_at');
    }

    public function scopeWithoutSecurityQuestions($query)
    {
        return $query->whereNull('security_questions_set_at');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function getLocationHierarchy()
    {
        return [
            'country' => $this->country_name,
            'state' => $this->state_name,
            'municipality' => $this->municipality_name,
            'parish' => $this->parish_name,
            'city' => $this->city_name,
            'full' => $this->full_location
        ];
    }

    // ==========================================
    // AUDIT LOGS
    // ==========================================

    public function auditLogsAsSubject()
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }

    public function auditLogsAsAuthor()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}