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
    ];

    public function run(): void
    {
        // ✅ USAR TRANSACCIÓN PARA CONSISTENCIA
        DB::beginTransaction();

        try {
            $this->command->info('🚀 Iniciando RolePermissionSeeder...');

            // =============================================
            // 1. RESETEAR CACHÉ DE PERMISOS
            // =============================================
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // =============================================
            // 2. CREAR TODOS LOS PERMISOS
            // =============================================
            $this->command->info('📝 Creando permisos...');

            $permissions = [
                // Panel de control
                'ver panel de control',

                // Usuarios
                'ver usuarios', 'crear usuario', 'editar usuario', 'eliminar usuario',

                // Roles
                'ver roles', 'crear rol', 'editar rol', 'eliminar rol',

                // Ubicaciones
                'ver paises', 'crear paises', 'eliminar paises',
                'ver estados', 'crear estados', 'eliminar estados',
                'ver municipios', 'crear municipios', 'eliminar municipios',
                'ver parroquias', 'crear parroquias', 'eliminar parroquias',
                'ver ciudades', 'crear ciudades', 'eliminar ciudades',

                // Propiedades
                'ver propiedades', 'crear propiedad', 'editar propiedad', 
                'eliminar propiedad', 'publicar propiedad',

                // Citas
                'ver citas', 'crear cita', 'editar cita', 'eliminar cita',

                // LEADS
                'ver leads', 'crear lead', 'editar lead', 'eliminar lead',

                // SERVICIOS
                'ver servicios', 'crear servicios', 'editar servicios', 'eliminar servicios',

                // Auditoría
                'ver logs de auditoria',
                'ver reportes',
                'exportar reportes',

                // Configuración
                'ver configuración', 'editar configuración',
                'actualizar configuracion telefonica',
            ];

            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web'
                ]);
            }

            $this->command->info('✅ Permisos creados: ' . count($permissions));

            // =============================================
            // 3. CREAR ROLES Y ASIGNAR PERMISOS
            // =============================================
            $this->command->info('👤 Creando roles...');

            // SUPER ADMIN - TODOS los permisos
            $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
            $superAdmin->syncPermissions(Permission::all());
            $this->command->info('✅ Super Admin: todos los permisos');

            // ADMINISTRADOR
            $admin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
            $adminPermissions = [
                'ver panel de control',
                'ver usuarios', 'crear usuario', 'editar usuario',
                'ver paises', 'ver estados', 'ver municipios', 'ver parroquias', 'ver ciudades',
                'ver propiedades', 'crear propiedad', 'editar propiedad', 'eliminar propiedad', 'publicar propiedad',
                'ver citas', 'crear cita', 'editar cita', 'eliminar cita',
                'ver leads', 'crear lead', 'editar lead', 'eliminar lead',
                'ver servicios', 'crear servicios', 'editar servicios', 'eliminar servicios',
                'ver reportes', 'exportar reportes',
                'ver configuración', 'editar configuración',
                'actualizar configuracion telefonica',
            ];
            $admin->syncPermissions($adminPermissions);
            $this->command->info('✅ Administrador: permisos asignados');

            // ASESOR INMOBILIARIO
            $asesor = Role::firstOrCreate(['name' => 'Asesor Inmobiliario', 'guard_name' => 'web']);
            $asesorPermissions = [
                'ver panel de control',
                'ver propiedades', 'crear propiedad', 'editar propiedad', 'publicar propiedad',
                'ver citas', 'crear cita', 'editar cita',
                'ver leads', 'crear lead', 'editar lead',
            ];
            $asesor->syncPermissions($asesorPermissions);
            $this->command->info('✅ Asesor Inmobiliario: permisos asignados');

            // AUDITOR - SOLO LECTURA
            $auditor = Role::firstOrCreate(['name' => 'Auditor', 'guard_name' => 'web']);
            $auditorPermissions = [
                'ver panel de control',
                'ver usuarios',
                'ver propiedades',
                'ver citas',
                'ver leads',
                'ver servicios',
                'ver logs de auditoria',
                'ver reportes',
                'exportar reportes',
            ];
            $auditor->syncPermissions($auditorPermissions);
            $this->command->info('✅ Auditor: permisos de solo lectura');

            // CLIENTE
            $cliente = Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
            $cliente->syncPermissions(['ver citas', 'crear cita']);
            $this->command->info('✅ Cliente: permisos asignados');

            // =============================================
            // 4. CREAR UBICACIONES DE PRUEBA
            // =============================================
            $this->command->info('📍 Creando ubicaciones de prueba...');

            // PAÍS: Venezuela
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

            // ESTADOS
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

            // MUNICIPIOS
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

            // PARROQUIAS
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

            // CIUDADES
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

            $this->command->info('✅ Ubicaciones creadas correctamente.');

            // =============================================
            // 5. FUNCIÓN AUXILIAR PARA PREGUNTAS ÚNICAS
            // =============================================
            $getUniqueQuestions = function(): array {
                $shuffled = $this->securityQuestions;
                shuffle($shuffled);
                
                // ✅ Asegurar que sean 3 preguntas diferentes
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
            // 6. CREAR USUARIOS DE PRUEBA
            // =============================================
            $this->command->info('👤 Creando usuarios de prueba...');

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
            ];

            // ✅ CREAR CADA USUARIO
            foreach ($this->testUsers as $key => $userCredentials) {
                $userData = $usersData[$key];
                $questions = $getUniqueQuestions();
                $answers = $this->securityAnswers[$key];

                // ✅ VERIFICAR SI EL USUARIO YA EXISTE
                $existingUser = User::where('email', $userCredentials['email'])->first();

                if ($existingUser) {
                    $this->command->info("⚠️ Usuario {$userCredentials['email']} ya existe, actualizando...");
                    
                    // ✅ ACTUALIZAR USUARIO EXISTENTE
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

                    // ✅ SINCRONIZAR ROL
                    $existingUser->syncRoles([$userCredentials['role']]);
                    
                    $this->command->info("✅ Usuario actualizado: {$userCredentials['email']}");
                } else {
                    // ✅ CREAR NUEVO USUARIO
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

                    // ✅ ASIGNAR ROL
                    $user->assignRole($userCredentials['role']);
                    
                    $this->command->info("✅ Usuario creado: {$userCredentials['email']}");
                }
            }

            // =============================================
            // 7. MOSTRAR CREDENCIALES
            // =============================================
            $this->command->newLine();
            $this->command->info('========================================');
            $this->command->info('     🎯 CREDENCIALES DE ACCESO');
            $this->command->info('========================================');
            
            foreach ($this->testUsers as $key => $user) {
                $role = $user['role'];
                $email = $user['email'];
                $password = $user['password'];
                
                $this->command->info(" {$role}:");
                $this->command->info("   📧 {$email}");
                $this->command->info("   🔑 {$password}");
                $this->command->info('');
            }
            
            $this->command->info('========================================');
            $this->command->info('');
            
            // ✅ MOSTRAR RESPUESTAS DE SEGURIDAD
            $this->command->info('🔐 RESPUESTAS DE SEGURIDAD (para recuperación):');
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
            // 8. CONFIRMAR Y COMMIT
            // =============================================
            DB::commit();

            $this->command->info('✅ RolePermissionSeeder completado exitosamente!');
            $this->command->info('🎉 Todos los usuarios tienen preguntas de seguridad configuradas.');
            $this->command->info('📊 Total de usuarios creados/actualizados: ' . count($this->testUsers));

        } catch (\Exception $e) {
            // ✅ ROLLBACK EN CASO DE ERROR
            DB::rollBack();
            
            $this->command->error('❌ Error en RolePermissionSeeder:');
            $this->command->error('   ' . $e->getMessage());
            $this->command->error('   Archivo: ' . $e->getFile() . ':' . $e->getLine());
            
            // ✅ REGISTRAR EN LOG
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