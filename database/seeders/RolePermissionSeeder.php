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

class RolePermissionSeeder extends Seeder
{
    // Lista de preguntas de seguridad predefinidas
    private $securityQuestions = [
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

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // =============================================
        // CREAR TODOS LOS PERMISOS
        // =============================================
        $permissions = [
            // Panel de control
            'ver panel de control',

            // Usuarios
            'ver usuarios', 'crear usuario', 'editar usuario', 'eliminar usuario',

            // Roles
            'ver roles', 'crear rol', 'editar rol', 'eliminar rol',

            // Ubicaciones
            'ver países', 'crear país', 'editar país', 'eliminar país',
            'ver estados', 'crear estado', 'editar estado', 'eliminar estado',
            'ver municipios', 'crear municipio', 'editar municipio', 'eliminar municipio',
            'ver parroquias', 'crear parroquia', 'editar parroquia', 'eliminar parroquia',
            'ver ciudades', 'crear ciudad', 'editar ciudad', 'eliminar ciudad',

            // Propiedades
            'ver propiedades', 'crear propiedad', 'editar propiedad', 'eliminar propiedad', 'publicar propiedad',

            // Citas
            'ver citas', 'crear cita', 'editar cita', 'eliminar cita',

            // LEADS
            'ver leads', 'crear lead', 'editar lead', 'eliminar lead',

            // SERVICIOS - NUEVOS PERMISOS
            'ver servicios', 'crear servicios', 'editar servicios', 'eliminar servicios',

            // Auditoría
            'ver logs de auditoria',
            'ver reportes',
            'exportar reportes',

            // Configuración
            'ver configuración', 'editar configuración',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info(' Permisos creados: ' . count($permissions));

        // =============================================
        // CREAR ROLES Y ASIGNAR PERMISOS
        // =============================================

        // 1. SUPER ADMIN - Todos los permisos
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
        $this->command->info(' Super Admin: todos los permisos');

        // 2. ADMINISTRADOR
        $admin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'ver panel de control',
            'ver usuarios', 'crear usuario', 'editar usuario',
            'ver países', 'ver estados', 'ver municipios', 'ver parroquias', 'ver ciudades',
            'ver propiedades', 'crear propiedad', 'editar propiedad', 'eliminar propiedad', 'publicar propiedad',
            'ver citas', 'crear cita', 'editar cita', 'eliminar cita',
            'ver leads', 'crear lead', 'editar lead', 'eliminar lead',
            'ver servicios', 'crear servicios', 'editar servicios', 'eliminar servicios',
            'ver reportes', 'exportar reportes',
            'ver configuración', 'editar configuración',
        ]);
        $this->command->info(' Administrador: permisos asignados (incluye leads y servicios)');

        // 3. ASESOR INMOBILIARIO
        $asesor = Role::firstOrCreate(['name' => 'Asesor Inmobiliario', 'guard_name' => 'web']);
        $asesor->syncPermissions([
            'ver panel de control',
            'ver propiedades', 'crear propiedad', 'editar propiedad', 'publicar propiedad',
            'ver citas', 'crear cita', 'editar cita',
            'ver leads', 'crear lead', 'editar lead',
        ]);
        $this->command->info(' Asesor Inmobiliario: permisos asignados');

        // 4. AUDITOR - SOLO LECTURA
        $auditor = Role::firstOrCreate(['name' => 'Auditor', 'guard_name' => 'web']);
        $auditor->syncPermissions([
            'ver panel de control',
            'ver usuarios',
            'ver propiedades',
            'ver citas',
            'ver leads',
            'ver servicios',
            'ver logs de auditoria',
            'ver reportes',
            'exportar reportes',
        ]);
        $this->command->info(' Auditor: permisos de solo lectura (incluye leads y servicios)');

        // 5. CLIENTE
        $cliente = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
        $cliente->syncPermissions([
            'ver citas',
            'crear cita',
        ]);
        $this->command->info(' Cliente: permisos asignados');

        // =============================================
        // CREAR UBICACIONES DE PRUEBA
        // =============================================
        $this->command->info(' Creando ubicaciones de prueba...');

        $venezuela = Country::firstOrCreate(
            ['name' => 'Venezuela'],
            ['code' => 'VEN', 'phone_code' => '+58']
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
        // FUNCIÓN AUXILIAR PARA GENERAR PREGUNTAS ALEATORIAS
        // =============================================
        $getRandomQuestions = function() {
            $shuffled = $this->securityQuestions;
            shuffle($shuffled);
            return array_slice($shuffled, 0, 3);
        };

        // =============================================
        // CREAR USUARIOS DE PRUEBA CON PREGUNTAS DE SEGURIDAD
        // =============================================
        $this->command->info('👤 Creando usuarios de prueba con preguntas de seguridad...');

        // 1. SUPER ADMIN
        $questions1 = $getRandomQuestions();
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@mso.com'],
            [
                'name' => 'Carlos',
                'last_name' => 'Rodríguez',
                'password' => bcrypt('SuperAdmin123!'),
                'phone' => '+58 412 1234567',
                'id_type' => 'V',
                'id_number' => '12345678',
                'country_id' => $venezuela->id,
                'state_id' => $distritoCapital->id,
                'municipality_id' => $libertador->id,
                'parish_id' => $altagracia->id,
                'city_id' => $caracas->id,
                'bio' => 'Super Administrador de MSO Grupo Inmobiliario.',
                'specialization' => 'Dirección General',
                'is_active' => true,
                'email_verified_at' => now(),
                // Preguntas de seguridad
                'security_questions' => json_encode($questions1),
                'security_answer_1' => Hash::make('responsesegura1'),
                'security_answer_2' => Hash::make('responsesegura2'),
                'security_answer_3' => Hash::make('responsesegura3'),
                'security_questions_set_at' => now(),
            ]
        );
        $superAdminUser->syncRoles(['Super Admin']);

        // 2. ADMINISTRADOR
        $questions2 = $getRandomQuestions();
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@mso.com'],
            [
                'name' => 'María',
                'last_name' => 'González',
                'password' => bcrypt('Admin123!'),
                'phone' => '+58 414 2345678',
                'id_type' => 'V',
                'id_number' => '23456789',
                'country_id' => $venezuela->id,
                'state_id' => $miranda->id,
                'municipality_id' => $baruta->id,
                'parish_id' => $barutaParish->id,
                'city_id' => $barutaCity->id,
                'bio' => 'Administradora con más de 5 años de experiencia en el sector inmobiliario.',
                'specialization' => 'Gestión de Propiedades',
                'is_active' => true,
                'email_verified_at' => now(),
                // Preguntas de seguridad
                'security_questions' => json_encode($questions2),
                'security_answer_1' => Hash::make('seguridadadmin1'),
                'security_answer_2' => Hash::make('seguridadadmin2'),
                'security_answer_3' => Hash::make('seguridadadmin3'),
                'security_questions_set_at' => now(),
            ]
        );
        $adminUser->syncRoles(['Administrador']);

        // 3. ASESOR 1
        $questions3 = $getRandomQuestions();
        $asesor1 = User::firstOrCreate(
            ['email' => 'asesor1@mso.com'],
            [
                'name' => 'Luis',
                'last_name' => 'Martínez',
                'password' => bcrypt('Asesor123!'),
                'phone' => '+58 416 3456789',
                'id_type' => 'V',
                'id_number' => '34567890',
                'country_id' => $venezuela->id,
                'state_id' => $carabobo->id,
                'municipality_id' => $valencia->id,
                'parish_id' => $sanJose->id,
                'city_id' => $valenciaCity->id,
                'bio' => 'Asesor inmobiliario especializado en propiedades residenciales de lujo.',
                'specialization' => 'Propiedades de Lujo',
                'social_links' => json_encode([
                    'whatsapp' => '584163456789',
                    'instagram' => 'luis.martinez.inmobiliaria',
                    'facebook' => 'luismartinez.asesor',
                ]),
                'is_active' => true,
                'email_verified_at' => now(),
                // Preguntas de seguridad
                'security_questions' => json_encode($questions3),
                'security_answer_1' => Hash::make('seguridadasesor1'),
                'security_answer_2' => Hash::make('seguridadasesor2'),
                'security_answer_3' => Hash::make('seguridadasesor3'),
                'security_questions_set_at' => now(),
            ]
        );
        $asesor1->syncRoles(['Asesor Inmobiliario']);

        // 4. ASESOR 2
        $questions4 = $getRandomQuestions();
        $asesor2 = User::firstOrCreate(
            ['email' => 'asesor2@mso.com'],
            [
                'name' => 'Ana Lucía',
                'last_name' => 'Fernández',
                'password' => bcrypt('Asesor123!'),
                'phone' => '+58 412 9876543',
                'id_type' => 'V',
                'id_number' => '45678901',
                'country_id' => $venezuela->id,
                'state_id' => $distritoCapital->id,
                'municipality_id' => $libertador->id,
                'parish_id' => $catedral->id,
                'city_id' => $caracas2->id,
                'bio' => 'Especialista en alquileres comerciales y oficinas.',
                'specialization' => 'Inmuebles Comerciales',
                'social_links' => json_encode([
                    'whatsapp' => '584129876543',
                    'instagram' => 'ana.fernandez.inmuebles',
                    'linkedin' => 'ana-fernandez-inmobiliaria',
                ]),
                'is_active' => true,
                'email_verified_at' => now(),
                // Preguntas de seguridad
                'security_questions' => json_encode($questions4),
                'security_answer_1' => Hash::make('seguridadasesor2_1'),
                'security_answer_2' => Hash::make('seguridadasesor2_2'),
                'security_answer_3' => Hash::make('seguridadasesor2_3'),
                'security_questions_set_at' => now(),
            ]
        );
        $asesor2->syncRoles(['Asesor Inmobiliario']);

        // 5. AUDITOR
        $questions5 = $getRandomQuestions();
        $auditorUser = User::firstOrCreate(
            ['email' => 'auditor@mso.com'],
            [
                'name' => 'Roberto',
                'last_name' => 'Sánchez',
                'password' => bcrypt('Auditor123!'),
                'phone' => '+58 414 5678901',
                'id_type' => 'V',
                'id_number' => '56789012',
                'country_id' => $venezuela->id,
                'state_id' => $miranda->id,
                'municipality_id' => $baruta->id,
                'parish_id' => $barutaParish->id,
                'city_id' => $barutaCity->id,
                'bio' => 'Auditor financiero especializado en el sector inmobiliario.',
                'specialization' => 'Auditoría y Control',
                'is_active' => true,
                'email_verified_at' => now(),
                // Preguntas de seguridad
                'security_questions' => json_encode($questions5),
                'security_answer_1' => Hash::make('seguridadauditor1'),
                'security_answer_2' => Hash::make('seguridadauditor2'),
                'security_answer_3' => Hash::make('seguridadauditor3'),
                'security_questions_set_at' => now(),
            ]
        );
        $auditorUser->syncRoles(['Auditor']);
        $this->command->info(' Auditor creado: auditor@mso.com / Auditor123!');

        // 6. CLIENTE 1
        $questions6 = $getRandomQuestions();
        $cliente1 = User::firstOrCreate(
            ['email' => 'pedro@test.com'],
            [
                'name' => 'Pedro',
                'last_name' => 'Pérez',
                'password' => bcrypt('Cliente123!'),
                'phone' => '04141234567',
                'id_type' => 'V',
                'id_number' => '15975346',
                'country_id' => $venezuela->id,
                'state_id' => $distritoCapital->id,
                'municipality_id' => $libertador->id,
                'parish_id' => $altagracia->id,
                'city_id' => $caracas->id,
                'is_active' => true,
                'email_verified_at' => now(),
                // Preguntas de seguridad
                'security_questions' => json_encode($questions6),
                'security_answer_1' => Hash::make('seguridadcliente1_1'),
                'security_answer_2' => Hash::make('seguridadcliente1_2'),
                'security_answer_3' => Hash::make('seguridadcliente1_3'),
                'security_questions_set_at' => now(),
            ]
        );
        $cliente1->syncRoles(['Cliente']);

        // 7. CLIENTE 2
        $questions7 = $getRandomQuestions();
        $cliente2 = User::firstOrCreate(
            ['email' => 'maria.cliente@test.com'],
            [
                'name' => 'María',
                'last_name' => 'Rodríguez',
                'password' => bcrypt('Cliente123!'),
                'phone' => '04241234567',
                'id_type' => 'V',
                'id_number' => '26789456',
                'country_id' => $venezuela->id,
                'state_id' => $carabobo->id,
                'municipality_id' => $valencia->id,
                'parish_id' => $sanJose->id,
                'city_id' => $valenciaCity->id,
                'is_active' => true,
                'email_verified_at' => now(),
                // Preguntas de seguridad
                'security_questions' => json_encode($questions7),
                'security_answer_1' => Hash::make('seguridadcliente2_1'),
                'security_answer_2' => Hash::make('seguridadcliente2_2'),
                'security_answer_3' => Hash::make('seguridadcliente2_3'),
                'security_questions_set_at' => now(),
            ]
        );
        $cliente2->syncRoles(['Cliente']);

        $this->command->info(' Seeder finalizado exitosamente!');

        // =============================================
        // MOSTRAR CREDENCIALES
        // =============================================
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('     CREDENCIALES DE ACCESO');
        $this->command->info('========================================');
        $this->command->info(' Super Admin  : superadmin@mso.com / SuperAdmin123!');
        $this->command->info(' Administrador: admin@mso.com / Admin123!');
        $this->command->info(' Asesor 1     : asesor1@mso.com / Asesor123!');
        $this->command->info(' Asesor 2     : asesor2@mso.com / Asesor123!');
        $this->command->info(' Auditor      : auditor@mso.com / Auditor123!');
        $this->command->info(' Cliente 1    : pedro@test.com / Cliente123!');
        $this->command->info(' Cliente 2    : maria.cliente@test.com / Cliente123!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info(' NOTA: Todos los usuarios tienen preguntas de seguridad configuradas.');
        $this->command->info(' Las respuestas de ejemplo son:');
        $this->command->info('   - Super Admin: responsesegura1, responsesegura2, responsesegura3');
        $this->command->info('   - Admin: seguridadadmin1, seguridadadmin2, seguridadadmin3');
        $this->command->info('   - Asesor1: seguridadasesor1, seguridadasesor2, seguridadasesor3');
        $this->command->info('   - Asesor2: seguridadasesor2_1, seguridadasesor2_2, seguridadasesor2_3');
        $this->command->info('   - Auditor: seguridadauditor1, seguridadauditor2, seguridadauditor3');
        $this->command->info('   - Cliente1: seguridadcliente1_1, seguridadcliente1_2, seguridadcliente1_3');
        $this->command->info('   - Cliente2: seguridadcliente2_1, seguridadcliente2_2, seguridadcliente2_3');
        $this->command->info('========================================');
    }
}
