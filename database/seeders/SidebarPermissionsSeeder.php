<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;

class SidebarPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info(' Agregando permisos del sidebar...');

        // =============================================
        // PERMISOS DEL SIDEBAR (VISIBILIDAD)
        // =============================================
        $sidebarPermissions = [
            'sidebar.dashboard',
            'sidebar.catalogo',
            'sidebar.users',
            'sidebar.roles',
            'sidebar.properties',
            'sidebar.leads',
            'sidebar.appointments',
            'sidebar.chat',
            'sidebar.audit',
            'sidebar.reports',
            'sidebar.config',
            'sidebar.favorites',
            'sidebar.servicios',
            'sidebar.categorias',
            'sidebar.ubicaciones',
            'sidebar.telefonos',
            'sidebar.profile',
        ];

        // Crear permisos si no existen
        foreach ($sidebarPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        $this->command->info(' Permisos del sidebar creados: ' . count($sidebarPermissions));

        // =============================================
        // ASIGNAR PERMISOS A ROLES EXISTENTES
        // =============================================

        // 1. SUPER ADMIN - TODOS los permisos del sidebar
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo($sidebarPermissions);
        $this->command->info(' Super Admin: todos los permisos del sidebar');

        // 2. ADMINISTRADOR
        $admin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $adminPermissions = [
            'sidebar.dashboard',
            'sidebar.catalogo',
            'sidebar.users',
            'sidebar.properties',
            'sidebar.leads',
            'sidebar.appointments',
            'sidebar.audit',
            'sidebar.reports',
            'sidebar.config',
            'sidebar.favorites',
            'sidebar.servicios',
            'sidebar.categorias',
            'sidebar.ubicaciones',
            'sidebar.telefonos',
            'sidebar.profile',
        ];
        $admin->syncPermissions($adminPermissions);
        $this->command->info(' Administrador: permisos del sidebar asignados');

        // 3. ASESOR INMOBILIARIO
        $asesor = Role::firstOrCreate(['name' => 'Asesor Inmobiliario', 'guard_name' => 'web']);
        $asesorPermissions = [
            'sidebar.dashboard',
            'sidebar.catalogo',
            'sidebar.properties',
            'sidebar.leads',
            'sidebar.appointments',
            'sidebar.chat',
            'sidebar.favorites',
            'sidebar.profile',
        ];
        $asesor->syncPermissions($asesorPermissions);
        $this->command->info(' Asesor Inmobiliario: permisos del sidebar asignados');

        // 4. AUDITOR
        $auditor = Role::firstOrCreate(['name' => 'Auditor', 'guard_name' => 'web']);
        $auditorPermissions = [
            'sidebar.dashboard',
            'sidebar.catalogo',
            'sidebar.properties',
            'sidebar.leads',
            'sidebar.appointments',
            'sidebar.audit',
            'sidebar.reports',
            'sidebar.favorites',
            'sidebar.profile',
        ];
        $auditor->syncPermissions($auditorPermissions);
        $this->command->info(' Auditor: permisos del sidebar asignados');

        // 5. CLIENTE
        $cliente = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
        $clientePermissions = [
            'sidebar.dashboard',
            'sidebar.catalogo',
            'sidebar.appointments',
            'sidebar.chat',
            'sidebar.favorites',
            'sidebar.profile',
        ];
        $cliente->syncPermissions($clientePermissions);
        $this->command->info(' Cliente: permisos del sidebar asignados');

        // 6. EDITOR
        $editor = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $editorPermissions = [
            'sidebar.dashboard',
            'sidebar.catalogo',
            'sidebar.properties',
            'sidebar.leads',
            'sidebar.appointments',
            'sidebar.favorites',
            'sidebar.profile',
        ];
        $editor->syncPermissions($editorPermissions);
        $this->command->info(' Editor: permisos del sidebar asignados');

        // 7. SUPERVISOR
        $supervisor = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);
        $supervisorPermissions = [
            'sidebar.dashboard',
            'sidebar.catalogo',
            'sidebar.properties',
            'sidebar.leads',
            'sidebar.appointments',
            'sidebar.favorites',
            'sidebar.profile',
        ];
        $supervisor->syncPermissions($supervisorPermissions);
        $this->command->info(' Supervisor: permisos del sidebar asignados');

        // 8. MARKETING
        $marketing = Role::firstOrCreate(['name' => 'Marketing', 'guard_name' => 'web']);
        $marketingPermissions = [
            'sidebar.dashboard',
            'sidebar.catalogo',
            'sidebar.properties',
            'sidebar.leads',
            'sidebar.reports',
            'sidebar.chat',
            'sidebar.favorites',
            'sidebar.profile',
        ];
        $marketing->syncPermissions($marketingPermissions);
        $this->command->info(' Marketing: permisos del sidebar asignados');

        // =============================================
        // LIMPIAR CACHÉ
        // =============================================
        Cache::forget('permissions_grouped');
        Cache::forget('roles_with_permissions');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->newLine();
        $this->command->info(' ¡Permisos del sidebar configurados correctamente!');
        $this->command->info(' Ahora puedes editar la visibilidad del sidebar desde la interfaz de roles.');
        $this->command->info(' Total de permisos del sidebar: ' . count($sidebarPermissions));
    }
}
