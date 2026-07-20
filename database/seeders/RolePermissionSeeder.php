<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class RolePermissionSeeder extends Seeder
{
    /**
     * Lista de preguntas de seguridad predefinidas
     */
    private array $securityQuestions = [
        '¿Cuál es el nombre de tu primera mascota?',
        '¿Cuál es el apellido de soltera de tu madre?',
        '¿En qué ciudad naciste?',
        '¿Cuál es tu comida favorita?',
        '¿Cuál es el nombre de tu mejor amigo de la infancia?',
        '¿Cuál es el título de tu libro favorito?',
        '¿Cuál es el nombre de tu profesor favorito?',
        '¿En qué año te graduaste de la escuela?',
        '¿Cuál es el nombre de tu primer amor?',
        '¿Cuál es el nombre de tu abuelo favorito?',
        '¿Cuál es el nombre de tu hijo/a?',
        '¿Cuál es el nombre de tu padre?',
        '¿Cuál es el modelo de tu primer auto?',
        '¿Cuál es el nombre de tu mejor amigo?',
        '¿Cuál es tu color favorito?',
        '¿Cuál es tu deporte favorito?',
        '¿Cuál es el nombre de tu primera escuela?',
        '¿Cuál es el nombre de tu primer jefe?',
        '¿Cuál es tu lugar favorito para vacacionar?',
        '¿Cuál es el nombre de tu tío favorito?',
    ];

    /**
     * Credenciales de los usuarios de prueba
     */
    private array $testUsers = [
        'superadmin' => [
            'email' => 'superadmin@mso.com',
            'password' => 'SuperAdmin2025#Secure!',
            'role' => 'Super Admin',
        ],
        'admin' => [
            'email' => 'admin@mso.com',
            'password' => 'Admin2025#Secure!',
            'role' => 'Administrador',
        ],
        'asesor1' => [
            'email' => 'asesor1@mso.com',
            'password' => 'Asesor2025#Secure!',
            'role' => 'Asesor Inmobiliario',
        ],
        'asesor2' => [
            'email' => 'asesor2@mso.com',
            'password' => 'Asesor2025#Secure!',
            'role' => 'Asesor Inmobiliario',
        ],
        'auditor' => [
            'email' => 'auditor@mso.com',
            'password' => 'Auditor2025#Secure!',
            'role' => 'Auditor',
        ],
        'cliente1' => [
            'email' => 'pedro@test.com',
            'password' => 'Cliente2025#Secure!',
            'role' => 'Cliente',
        ],
        'cliente2' => [
            'email' => 'maria.cliente@test.com',
            'password' => 'Cliente2025#Secure!',
            'role' => 'Cliente',
        ],
        'editor' => [
            'email' => 'editor@mso.com',
            'password' => 'Editor2025#Secure!',
            'role' => 'Editor',
        ],
        'supervisor' => [
            'email' => 'supervisor@mso.com',
            'password' => 'Supervisor2025#Secure!',
            'role' => 'Supervisor',
        ],
        'marketing' => [
            'email' => 'marketing@mso.com',
            'password' => 'Marketing2025#Secure!',
            'role' => 'Marketing',
        ],
    ];

    /**
     * Respuestas de seguridad predefinidas por usuario
     */
    private array $securityAnswers = [
        'superadmin' => ['respuesta_superadmin_1', 'respuesta_superadmin_2', 'respuesta_superadmin_3'],
        'admin' => ['respuesta_admin_1', 'respuesta_admin_2', 'respuesta_admin_3'],
        'asesor1' => ['respuesta_asesor1_1', 'respuesta_asesor1_2', 'respuesta_asesor1_3'],
        'asesor2' => ['respuesta_asesor2_1', 'respuesta_asesor2_2', 'respuesta_asesor2_3'],
        'auditor' => ['respuesta_auditor_1', 'respuesta_auditor_2', 'respuesta_auditor_3'],
        'cliente1' => ['respuesta_cliente1_1', 'respuesta_cliente1_2', 'respuesta_cliente1_3'],
        'cliente2' => ['respuesta_cliente2_1', 'respuesta_cliente2_2', 'respuesta_cliente2_3'],
        'editor' => ['respuesta_editor_1', 'respuesta_editor_2', 'respuesta_editor_3'],
        'supervisor' => ['respuesta_supervisor_1', 'respuesta_supervisor_2', 'respuesta_supervisor_3'],
        'marketing' => ['respuesta_marketing_1', 'respuesta_marketing_2', 'respuesta_marketing_3'],
    ];

    public function run(): void
    {
        DB::beginTransaction();

        try {
            $this->command->info(' Iniciando RolePermissionSeeder...');

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // =============================================
            // CREAR PERMISOS DEL SISTEMA
            // =============================================
            $this->command->info(' Creando permisos del sistema...');

            $systemPermissions = [
                // Panel de control
                'ver panel de control',

                // PERMISOS DE USUARIOS
                'ver usuarios',
                'crear usuario',
                'editar usuario',
                'eliminar usuario',
                'gestionar usuarios',

                // PERMISOS DE ROLES
                'ver roles',
                'crear rol',
                'editar rol',
                'eliminar rol',
                'gestionar roles',

                // PERMISOS DE CATEGORÍAS
                'ver categorias',
                'crear categoria',
                'editar categoria',
                'eliminar categoria',
                'gestionar categorias',

                // PERMISOS DE UBICACIONES
                'ver paises',
                'crear paises',
                'eliminar paises',
                'ver estados',
                'crear estados',
                'eliminar estados',
                'ver municipios',
                'crear municipios',
                'eliminar municipios',
                'ver parroquias',
                'crear parroquias',
                'eliminar parroquias',
                'ver ciudades',
                'crear ciudades',
                'eliminar ciudades',
                'gestionar ubicaciones',

                // PERMISOS DE PROPIEDADES
                'ver propiedades',
                'crear propiedad',
                'editar propiedad',
                'eliminar propiedad',
                'publicar propiedad',
                'gestionar propiedades',

                // PERMISOS DE CITAS
                'ver citas',
                'crear cita',
                'editar cita',
                'eliminar cita',
                'gestionar citas',
                'configurar agenda',

                // PERMISOS DE LEADS
                'ver leads',
                'crear lead',
                'editar lead',
                'eliminar lead',
                'gestionar leads',
                'cambiar estado lead',

                // PERMISOS DE SERVICIOS
                'ver servicios',
                'crear servicios',
                'editar servicios',
                'eliminar servicios',
                'gestionar servicios',

                // PERMISOS DE AUDITORÍA
                'ver logs de auditoria',
                'acceso auditoria',
                'ver reportes',
                'exportar reportes',

                // PERMISOS DE CONFIGURACIÓN
                'ver configuración',
                'editar configuración',
                'actualizar configuracion telefonica',
                'gestionar configuracion',

                // PERMISOS DE CHAT
                'chat access',

                // PERMISOS DE FAVORITOS
                'ver favoritos',
                'crear favorito',
                'eliminar favorito',
            ];

            foreach ($systemPermissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web'
                ]);
            }

            $this->command->info(' Permisos del sistema creados: ' . count($systemPermissions));

            // =============================================
            // CREAR PERMISOS DEL SIDEBAR
            // =============================================
            $this->command->info(' Creando permisos del sidebar...');

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

            foreach ($sidebarPermissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web'
                ]);
            }

            $this->command->info(' Permisos del sidebar creados: ' . count($sidebarPermissions));

            // =============================================
            // CREAR ROLES Y ASIGNAR PERMISOS
            // =============================================
            $this->command->info('👤 Creando roles...');

            // =============================================
            // 1. SUPER ADMIN - TODOS los permisos
            // =============================================
            $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
            $superAdminPermissions = array_merge($systemPermissions, $sidebarPermissions);
            $superAdmin->syncPermissions($superAdminPermissions);
            $this->command->info(' Super Admin: permisos asignados (' . count($superAdminPermissions) . ' permisos)');

            // =============================================
            // 2. ADMINISTRADOR
            // =============================================
            $admin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);

            $adminSystemPermissions = [
                'ver panel de control',
                'ver usuarios', 'crear usuario', 'editar usuario', 'eliminar usuario', 'gestionar usuarios',
                'ver roles',
                'ver categorias', 'crear categoria', 'editar categoria', 'eliminar categoria', 'gestionar categorias',
                'ver paises', 'ver estados', 'ver municipios', 'ver parroquias', 'ver ciudades',
                'crear paises', 'eliminar paises',
                'crear estados', 'eliminar estados',
                'crear municipios', 'eliminar municipios',
                'crear parroquias', 'eliminar parroquias',
                'crear ciudades', 'eliminar ciudades',
                'gestionar ubicaciones',
                'ver propiedades', 'crear propiedad', 'editar propiedad', 'eliminar propiedad', 'publicar propiedad', 'gestionar propiedades',
                'ver citas', 'crear cita', 'editar cita', 'eliminar cita', 'gestionar citas',
                'configurar agenda',
                'ver leads', 'crear lead', 'editar lead', 'eliminar lead', 'gestionar leads', 'cambiar estado lead',
                'ver servicios', 'crear servicios', 'editar servicios', 'eliminar servicios', 'gestionar servicios',
                'ver logs de auditoria',
                'acceso auditoria',
                'ver reportes', 'exportar reportes',
                'ver configuración', 'editar configuración', 'gestionar configuracion',
                'actualizar configuracion telefonica',
                'ver favoritos', 'crear favorito', 'eliminar favorito',
            ];

            $adminSidebarPermissions = [
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

            $adminPermissions = array_merge($adminSystemPermissions, $adminSidebarPermissions);
            $admin->syncPermissions($adminPermissions);
            $this->command->info(' Administrador: permisos asignados (' . count($adminPermissions) . ' permisos)');

            // =============================================
            // 3. ASESOR INMOBILIARIO
            // =============================================
            $asesor = Role::firstOrCreate(['name' => 'Asesor Inmobiliario', 'guard_name' => 'web']);

            $asesorSystemPermissions = [
                'ver panel de control',
                'ver propiedades', 'crear propiedad', 'editar propiedad', 'publicar propiedad', 'gestionar propiedades',
                'ver citas', 'crear cita', 'editar cita', 'gestionar citas',
                'configurar agenda',
                'ver leads', 'crear lead', 'editar lead', 'gestionar leads', 'cambiar estado lead',
                'chat access',
                'ver favoritos', 'crear favorito', 'eliminar favorito',
            ];

            $asesorSidebarPermissions = [
                'sidebar.dashboard',
                'sidebar.catalogo',
                'sidebar.properties',
                'sidebar.leads',
                'sidebar.appointments',
                'sidebar.chat',
                'sidebar.favorites',
                'sidebar.profile',
            ];

            $asesorPermissions = array_merge($asesorSystemPermissions, $asesorSidebarPermissions);
            $asesor->syncPermissions($asesorPermissions);
            $this->command->info('Asesor Inmobiliario: permisos asignados (' . count($asesorPermissions) . ' permisos)');

            // =============================================
            // 4. AUDITOR
            // =============================================
            $auditor = Role::firstOrCreate(['name' => 'Auditor', 'guard_name' => 'web']);

            $auditorSystemPermissions = [
                'ver panel de control',
                'ver usuarios',
                'ver categorias',
                'ver paises', 'ver estados', 'ver municipios', 'ver parroquias', 'ver ciudades',
                'ver propiedades',
                'ver citas',
                'ver leads',
                'ver servicios',
                'ver logs de auditoria',
                'acceso auditoria',
                'ver reportes',
                'exportar reportes',
                'ver favoritos',
            ];

            $auditorSidebarPermissions = [
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

            $auditorPermissions = array_merge($auditorSystemPermissions, $auditorSidebarPermissions);
            $auditor->syncPermissions($auditorPermissions);
            $this->command->info(' Auditor: permisos asignados (' . count($auditorPermissions) . ' permisos)');

            // =============================================
            // 5. CLIENTE
            // =============================================
            $cliente = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);

            $clienteSystemPermissions = [
                'ver panel de control',
                'ver citas', 'crear cita',
                'chat access',
                'ver favoritos', 'crear favorito', 'eliminar favorito',
            ];

            $clienteSidebarPermissions = [
                'sidebar.dashboard',
                'sidebar.catalogo',
                'sidebar.appointments',
                'sidebar.chat',
                'sidebar.favorites',
                'sidebar.profile',
            ];

            $clientePermissions = array_merge($clienteSystemPermissions, $clienteSidebarPermissions);
            $cliente->syncPermissions($clientePermissions);
            $this->command->info(' Cliente: permisos asignados (' . count($clientePermissions) . ' permisos)');

            // =============================================
            // 6. EDITOR
            // =============================================
            $editor = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);

            $editorSystemPermissions = [
                'ver panel de control',
                'ver propiedades', 'crear propiedad', 'editar propiedad', 'publicar propiedad',
                'ver citas', 'crear cita', 'editar cita',
                'ver leads', 'crear lead', 'editar lead',
                'ver favoritos', 'crear favorito',
            ];

            $editorSidebarPermissions = [
                'sidebar.dashboard',
                'sidebar.catalogo',
                'sidebar.properties',
                'sidebar.leads',
                'sidebar.appointments',
                'sidebar.favorites',
                'sidebar.profile',
            ];

            $editorPermissions = array_merge($editorSystemPermissions, $editorSidebarPermissions);
            $editor->syncPermissions($editorPermissions);
            $this->command->info(' Editor: permisos asignados (' . count($editorPermissions) . ' permisos)');

            // =============================================
            // 7. SUPERVISOR
            // =============================================
            $supervisor = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);

            $supervisorSystemPermissions = [
                'ver panel de control',
                'ver usuarios',
                'ver categorias',
                'ver paises', 'ver estados', 'ver municipios', 'ver parroquias', 'ver ciudades',
                'ver propiedades',
                'ver citas',
                'ver leads',
                'ver servicios',
                'ver reportes',
                'ver favoritos',
            ];

            $supervisorSidebarPermissions = [
                'sidebar.dashboard',
                'sidebar.catalogo',
                'sidebar.properties',
                'sidebar.leads',
                'sidebar.appointments',
                'sidebar.favorites',
                'sidebar.profile',
            ];

            $supervisorPermissions = array_merge($supervisorSystemPermissions, $supervisorSidebarPermissions);
            $supervisor->syncPermissions($supervisorPermissions);
            $this->command->info(' Supervisor: permisos asignados (' . count($supervisorPermissions) . ' permisos)');

            // =============================================
            // 8. MARKETING
            // =============================================
            $marketing = Role::firstOrCreate(['name' => 'Marketing', 'guard_name' => 'web']);

            $marketingSystemPermissions = [
                'ver panel de control',
                'ver propiedades',
                'ver leads', 'crear lead', 'editar lead', 'cambiar estado lead',
                'ver reportes',
                'chat access',
                'ver favoritos',
            ];

            $marketingSidebarPermissions = [
                'sidebar.dashboard',
                'sidebar.catalogo',
                'sidebar.properties',
                'sidebar.leads',
                'sidebar.reports',
                'sidebar.chat',
                'sidebar.favorites',
                'sidebar.profile',
            ];

            $marketingPermissions = array_merge($marketingSystemPermissions, $marketingSidebarPermissions);
            $marketing->syncPermissions($marketingPermissions);
            $this->command->info(' Marketing: permisos asignados (' . count($marketingPermissions) . ' permisos)');

            // =============================================
            // CREAR UBICACIONES DE PRUEBA
            // =============================================
            $this->command->info(' Creando ubicaciones de prueba...');

            $venezuela = Country::firstOrCreate(
                ['name' => 'Venezuela'],
                [
                    'code' => 'VEN',
                    'phone_code' => '+58',
                    'phone_format' => '000-0000000',
                    'phone_min_length' => 10,
                    'phone_max_length' => 10
                ]
            );

            $distritoCapital = State::firstOrCreate([
                'country_id' => $venezuela->id,
                'name' => 'Distrito Capital'
            ]);

            $miranda = State::firstOrCreate([
                'country_id' => $venezuela->id,
                'name' => 'Miranda'
            ]);

            $carabobo = State::firstOrCreate([
                'country_id' => $venezuela->id,
                'name' => 'Carabobo'
            ]);

            $libertador = Municipality::firstOrCreate([
                'state_id' => $distritoCapital->id,
                'name' => 'Libertador'
            ]);

            $baruta = Municipality::firstOrCreate([
                'state_id' => $miranda->id,
                'name' => 'Baruta'
            ]);

            $valencia = Municipality::firstOrCreate([
                'state_id' => $carabobo->id,
                'name' => 'Valencia'
            ]);

            $altagracia = Parish::firstOrCreate([
                'municipality_id' => $libertador->id,
                'name' => 'Altagracia'
            ]);

            $catedral = Parish::firstOrCreate([
                'municipality_id' => $libertador->id,
                'name' => 'Catedral'
            ]);

            $barutaParish = Parish::firstOrCreate([
                'municipality_id' => $baruta->id,
                'name' => 'Baruta'
            ]);

            $sanJose = Parish::firstOrCreate([
                'municipality_id' => $valencia->id,
                'name' => 'San José'
            ]);

            $caracas = City::firstOrCreate([
                'parish_id' => $altagracia->id,
                'name' => 'Caracas'
            ]);

            $caracas2 = City::firstOrCreate([
                'parish_id' => $catedral->id,
                'name' => 'Caracas'
            ]);

            $barutaCity = City::firstOrCreate([
                'parish_id' => $barutaParish->id,
                'name' => 'Baruta'
            ]);

            $valenciaCity = City::firstOrCreate([
                'parish_id' => $sanJose->id,
                'name' => 'Valencia'
            ]);

            $this->command->info(' Ubicaciones creadas correctamente.');

            // =============================================
            // FUNCIÓN AUXILIAR PARA PREGUNTAS ÚNICAS
            // =============================================
            $getUniqueQuestions = function(): array {
                $shuffled = $this->securityQuestions;
                shuffle($shuffled);

                $selected = [];
                $usedKeys = [];

                while (count($selected) < 3) {
                    $randomKey = array_rand($shuffled);
                    if (!in_array($randomKey, $usedKeys)) {
                        $usedKeys[] = $randomKey;
                        $selected[] = $shuffled[$randomKey];
                    }
                }

                return $selected;
            };

            // =============================================
            // CREAR USUARIOS DE PRUEBA
            // =============================================
            $this->command->info(' Creando usuarios de prueba...');

            $usersData = [
                'superadmin' => [
                    'name' => 'Carlos',
                    'last_name' => 'Rodríguez',
                    'phone' => '+58 412 1234567',
                    'id_type' => 'V',
                    'id_number' => '12345678',
                    'bio' => 'Super Administrador de MSO Grupo Inmobiliario.',
                    'specialization' => 'Dirección General',
                    'country_id' => $venezuela->id,
                    'state_id' => $distritoCapital->id,
                    'municipality_id' => $libertador->id,
                    'parish_id' => $altagracia->id,
                    'city_id' => $caracas->id,
                ],
                'admin' => [
                    'name' => 'María',
                    'last_name' => 'González',
                    'phone' => '+58 414 2345678',
                    'id_type' => 'V',
                    'id_number' => '23456789',
                    'bio' => 'Administradora con más de 5 años de experiencia en el sector inmobiliario.',
                    'specialization' => 'Gestión de Propiedades',
                    'country_id' => $venezuela->id,
                    'state_id' => $miranda->id,
                    'municipality_id' => $baruta->id,
                    'parish_id' => $barutaParish->id,
                    'city_id' => $barutaCity->id,
                ],
                'asesor1' => [
                    'name' => 'Luis',
                    'last_name' => 'Martínez',
                    'phone' => '+58 416 3456789',
                    'id_type' => 'V',
                    'id_number' => '34567890',
                    'bio' => 'Asesor inmobiliario especializado en propiedades residenciales de lujo.',
                    'specialization' => 'Propiedades de Lujo',
                    'social_links' => json_encode([
                        'whatsapp' => '584163456789',
                        'instagram' => 'luis.martinez.inmobiliaria',
                        'facebook' => 'luismartinez.asesor',
                    ]),
                    'country_id' => $venezuela->id,
                    'state_id' => $carabobo->id,
                    'municipality_id' => $valencia->id,
                    'parish_id' => $sanJose->id,
                    'city_id' => $valenciaCity->id,
                ],
                'asesor2' => [
                    'name' => 'Ana Lucía',
                    'last_name' => 'Fernández',
                    'phone' => '+58 412 9876543',
                    'id_type' => 'V',
                    'id_number' => '45678901',
                    'bio' => 'Especialista en alquileres comerciales y oficinas.',
                    'specialization' => 'Inmuebles Comerciales',
                    'social_links' => json_encode([
                        'whatsapp' => '584129876543',
                        'instagram' => 'ana.fernandez.inmuebles',
                        'linkedin' => 'ana-fernandez-inmobiliaria',
                    ]),
                    'country_id' => $venezuela->id,
                    'state_id' => $distritoCapital->id,
                    'municipality_id' => $libertador->id,
                    'parish_id' => $catedral->id,
                    'city_id' => $caracas2->id,
                ],
                'auditor' => [
                    'name' => 'Roberto',
                    'last_name' => 'Sánchez',
                    'phone' => '+58 414 5678901',
                    'id_type' => 'V',
                    'id_number' => '56789012',
                    'bio' => 'Auditor financiero especializado en el sector inmobiliario.',
                    'specialization' => 'Auditoría y Control',
                    'country_id' => $venezuela->id,
                    'state_id' => $miranda->id,
                    'municipality_id' => $baruta->id,
                    'parish_id' => $barutaParish->id,
                    'city_id' => $barutaCity->id,
                ],
                'cliente1' => [
                    'name' => 'Pedro',
                    'last_name' => 'Pérez',
                    'phone' => '04141234567',
                    'id_type' => 'V',
                    'id_number' => '15975346',
                    'bio' => null,
                    'specialization' => null,
                    'country_id' => $venezuela->id,
                    'state_id' => $distritoCapital->id,
                    'municipality_id' => $libertador->id,
                    'parish_id' => $altagracia->id,
                    'city_id' => $caracas->id,
                ],
                'cliente2' => [
                    'name' => 'María',
                    'last_name' => 'Rodríguez',
                    'phone' => '04241234567',
                    'id_type' => 'V',
                    'id_number' => '26789456',
                    'bio' => null,
                    'specialization' => null,
                    'country_id' => $venezuela->id,
                    'state_id' => $carabobo->id,
                    'municipality_id' => $valencia->id,
                    'parish_id' => $sanJose->id,
                    'city_id' => $valenciaCity->id,
                ],
                'editor' => [
                    'name' => 'Laura',
                    'last_name' => 'Editora',
                    'phone' => '+58 424 5678901',
                    'id_type' => 'V',
                    'id_number' => '78901234',
                    'bio' => 'Editora de contenido inmobiliario.',
                    'specialization' => 'Edición de Propiedades',
                    'country_id' => $venezuela->id,
                    'state_id' => $miranda->id,
                    'municipality_id' => $baruta->id,
                    'parish_id' => $barutaParish->id,
                    'city_id' => $barutaCity->id,
                ],
                'supervisor' => [
                    'name' => 'Jorge',
                    'last_name' => 'Supervisor',
                    'phone' => '+58 414 6789012',
                    'id_type' => 'V',
                    'id_number' => '89012345',
                    'bio' => 'Supervisor de procesos inmobiliarios.',
                    'specialization' => 'Supervisión',
                    'country_id' => $venezuela->id,
                    'state_id' => $carabobo->id,
                    'municipality_id' => $valencia->id,
                    'parish_id' => $sanJose->id,
                    'city_id' => $valenciaCity->id,
                ],
                'marketing' => [
                    'name' => 'Ana',
                    'last_name' => 'Marketing',
                    'phone' => '+58 412 9876543',
                    'id_type' => 'V',
                    'id_number' => '90123456',
                    'bio' => 'Especialista en marketing inmobiliario.',
                    'specialization' => 'Marketing Digital',
                    'country_id' => $venezuela->id,
                    'state_id' => $distritoCapital->id,
                    'municipality_id' => $libertador->id,
                    'parish_id' => $altagracia->id,
                    'city_id' => $caracas->id,
                ],
            ];

            foreach ($this->testUsers as $key => $userCredentials) {
                $userData = $usersData[$key];
                $questions = $getUniqueQuestions();
                $answers = $this->securityAnswers[$key];

                $existingUser = User::where('email', $userCredentials['email'])->first();

                if ($existingUser) {
                    $this->command->info("   Usuario {$userCredentials['email']} ya existe, actualizando...");

                    $existingUser->update([
                        'name' => $userData['name'],
                        'last_name' => $userData['last_name'],
                        'phone' => $userData['phone'],
                        'id_type' => $userData['id_type'],
                        'id_number' => $userData['id_number'],
                        'bio' => $userData['bio'],
                        'specialization' => $userData['specialization'] ?? null,
                        'social_links' => $userData['social_links'] ?? null,
                        'country_id' => $userData['country_id'],
                        'state_id' => $userData['state_id'],
                        'municipality_id' => $userData['municipality_id'],
                        'parish_id' => $userData['parish_id'],
                        'city_id' => $userData['city_id'],
                        'is_active' => true,
                        'email_verified_at' => now(),
                        'security_questions' => $questions,
                        'security_answer_1' => Hash::make($answers[0]),
                        'security_answer_2' => Hash::make($answers[1]),
                        'security_answer_3' => Hash::make($answers[2]),
                        'security_questions_set_at' => now(),
                    ]);

                    $existingUser->syncRoles([$userCredentials['role']]);

                    $this->command->info("    Usuario actualizado: {$userCredentials['email']}");
                } else {
                    $user = User::create([
                        'name' => $userData['name'],
                        'last_name' => $userData['last_name'],
                        'email' => $userCredentials['email'],
                        'password' => Hash::make($userCredentials['password']),
                        'phone' => $userData['phone'],
                        'id_type' => $userData['id_type'],
                        'id_number' => $userData['id_number'],
                        'bio' => $userData['bio'],
                        'specialization' => $userData['specialization'] ?? null,
                        'social_links' => $userData['social_links'] ?? null,
                        'country_id' => $userData['country_id'],
                        'state_id' => $userData['state_id'],
                        'municipality_id' => $userData['municipality_id'],
                        'parish_id' => $userData['parish_id'],
                        'city_id' => $userData['city_id'],
                        'is_active' => true,
                        'email_verified_at' => now(),
                        'security_questions' => $questions,
                        'security_answer_1' => Hash::make($answers[0]),
                        'security_answer_2' => Hash::make($answers[1]),
                        'security_answer_3' => Hash::make($answers[2]),
                        'security_questions_set_at' => now(),
                    ]);

                    $user->assignRole($userCredentials['role']);

                    $this->command->info("    Usuario creado: {$userCredentials['email']}");
                }
            }

            // =============================================
            // MOSTRAR CREDENCIALES
            // =============================================
            $this->command->newLine();
            $this->command->info('========================================');
            $this->command->info('       CREDENCIALES DE ACCESO');
            $this->command->info('========================================');

            foreach ($this->testUsers as $key => $user) {
                $role = $user['role'];
                $email = $user['email'];
                $password = $user['password'];

                $this->command->info(" {$role}:");
                $this->command->info("     {$email}");
                $this->command->info("     {$password}");
                $this->command->info('');
            }

            $this->command->info('========================================');
            $this->command->info('');

            $this->command->info(' RESPUESTAS DE SEGURIDAD:');
            $this->command->info('========================================');
            foreach ($this->securityAnswers as $key => $answers) {
                $userData = $this->testUsers[$key];
                $role = $userData['role'];
                $this->command->info(" {$role}:");
                $this->command->info("   1. {$answers[0]}");
                $this->command->info("   2. {$answers[1]}");
                $this->command->info("   3. {$answers[2]}");
                $this->command->info('');
            }
            $this->command->info('========================================');
            $this->command->info('');

            // =============================================
            // LIMPIAR CACHÉ
            // =============================================
            Cache::forget('permissions_grouped');
            Cache::forget('roles_with_permissions');
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            DB::commit();

            $this->command->info(' RolePermissionSeeder completado exitosamente!');
            $this->command->info(' Total de permisos del sistema: ' . count($systemPermissions));
            $this->command->info(' Total de permisos del sidebar: ' . count($sidebarPermissions));
            $this->command->info(' Total de permisos combinados: ' . (count($systemPermissions) + count($sidebarPermissions)));
            $this->command->info(' Total de usuarios creados/actualizados: ' . count($this->testUsers));

        } catch (\Exception $e) {
            DB::rollBack();

            $this->command->error(' Error en RolePermissionSeeder:');
            $this->command->error('   ' . $e->getMessage());
            $this->command->error('   Archivo: ' . $e->getFile() . ':' . $e->getLine());

            Log::error('Error en RolePermissionSeeder', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Obtener 3 preguntas de seguridad únicas
     */
    private function getUniqueSecurityQuestions(): array
    {
        $shuffled = $this->securityQuestions;
        shuffle($shuffled);

        $selected = [];
        $usedKeys = [];

        while (count($selected) < 3) {
            $randomKey = array_rand($shuffled);
            if (!in_array($randomKey, $usedKeys)) {
                $usedKeys[] = $randomKey;
                $selected[] = $shuffled[$randomKey];
            }
        }

        return $selected;
    }
}
