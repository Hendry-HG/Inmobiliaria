<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa una categoria de propiedades inmobiliarias.
 *
 * Las categorias permiten clasificar propiedades por tipo (venta, alquiler,
 * residencial, comercial, etc.). Cada categoria tiene un slug auto-generado
 * para URLs amigables y un orden de visualizacion configurable.
 *
 * Relaciones:
 * - properties: Propiedades que pertenecen a esta categoria.
 *
 * Campos clave:
 * - slug: Identificador URL amigable, auto-generado desde el nombre si se deja vacio.
 * - is_active: Controla si la categoria esta visible en la interfaz.
 * - order: Numero de orden para controlar la secuencia de visualizacion.
 *
 * Scopes:
 * - active: Filtra solo categorias activas.
 * - ordered: Ordena por el campo 'order'.
 *
 * @package App\Models
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'is_active', 'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Obtiene todas las propiedades que pertenecen a esta categoria.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacion con el modelo Property.
     */
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Scope que filtra solo las categorias activas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Builder de consulta.
     * @return \Illuminate\Database\Eloquent\Builder Builder con el filtro de activas.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope que ordena las categorias por su campo 'order'.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Builder de consulta.
     * @return \Illuminate\Database\Eloquent\Builder Builder con el orden aplicado.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Boot del modelo: genera automaticamente el slug al crear una categoria.
     *
     * Si el campo slug se deja vacio al momento de creacion, se genera
     * automaticamente desde el nombre usando el metodo slug() de Str.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = str($category->name)->slug();
            }
        });
    }
}
