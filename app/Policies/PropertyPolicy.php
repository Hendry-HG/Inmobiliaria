<?php


namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Determinar si el usuario puede actualizar la propiedad
     */
    public function update(User $user, Property $property): bool
    {
        // Super Admin y Administrador pueden editar cualquier propiedad
        if ($user->hasRole(['Super Admin', 'Administrador'])) {
            return true;
        }

        // Asesor solo puede editar sus propias propiedades
        if ($user->hasRole('Asesor Inmobiliario')) {
            return $property->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determinar si el usuario puede eliminar la propiedad
     */
    public function delete(User $user, Property $property): bool
    {
        // Misma lógica que update
        return $this->update($user, $property);
    }

    /**
     * Determinar si el usuario puede ver la propiedad
     */
    public function view(User $user, Property $property): bool
    {
        // Super Admin y Administrador pueden ver cualquier propiedad
        if ($user->hasRole(['Super Admin', 'Administrador'])) {
            return true;
        }

        // Asesor puede ver sus propias propiedades
        if ($user->hasRole('Asesor Inmobiliario')) {
            return $property->user_id === $user->id;
        }

        // Cliente puede ver propiedades publicadas
        if ($user->hasRole('Cliente')) {
            return $property->status === 'publicada';
        }

        return false;
    }
}
