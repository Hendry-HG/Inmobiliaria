<?php

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

/**
 * Modelo de usuario del sistema inmobiliario.
 *
 * Representa a todos los usuarios del sistema: clientes, asesores inmobiliarios,
 * administradores y auditores. Gestiona datos personales, ubicación geográfica,
 * seguridad (preguntas de seguridad), favoritos, notificaciones y roles/permisos
 * a través del paquete Spatie.
 *
 * Extiende de Authenticatable para autenticación y usa SoftDeletes para
 * eliminación lógica de registros.
 *
 * Flujo de datos:
 * - Los datos personales e identificación se cargan desde formularios del perfil.
 * - La ubicación se resuelve mediante relaciones con Country, State, Municipality,
 *   Parish y City.
 * - Las preguntas de seguridad se almacenan hasheadas y se verifican con Hash::check.
 * - Los roles determinan el acceso a módulos del sistema (propiedades, citas, leads).
 */
class User extends Authenticatable implements CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, CanResetPassword;

    /**
     * Atributos mass-assignables (fillable).
     *
     * Define los campos que pueden ser asignados masivamente a través de
     * create(), fill() o Form Requests. Organizados en categorías:
     * - Datos personales: nombre, apellido, email, contraseña, teléfono.
     * - Perfil: foto, biografía, especialización, enlaces sociales.
     * - Identificación: tipo y número de documento.
     * - Ubicación: dirección y referencias geográficas (country, state, etc.).
     * - Estado: activo, en línea, última actividad, verificación de email.
     * - Seguridad: preguntas y respuestas de seguridad (hasheadas).
     */
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

    /**
     * Atributos ocultos en serializaciones (JSON, toArray, etc.).
     *
     * Protege información sensible para que nunca se exponga en respuestas
     * HTTP o APIs: contraseña hasheada, token de recordarme y las tres
     * respuestas de seguridad.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'security_answer_1',
        'security_answer_2',
        'security_answer_3',
    ];

    /**
     * Conversiones de tipo de atributos.
     *
     * Define cómo se interpretan y almacenan los atributos en la base de datos:
     * - email_verified_at: fecha/hora de verificación de email.
     * - password: se hashea automáticamente al asignarse.
     * - social_links: se almacena como JSON (array) en la BD.
     * - is_active / is_online: booleanos para estado del usuario.
     * - last_seen_at: fecha/hora de última actividad.
     * - security_questions: array JSON con las preguntas seleccionadas.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'social_links' => 'array',
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
        'security_questions' => 'array',
    ];

    
    /**
     * Accesor: nombre completo del usuario.
     *
     * Concatena name y last_name. Si no hay apellido, devuelve solo el nombre.
     * Se accede como $user->full_name.
     *
     * @return string Nombre completo o solo nombre si no tiene apellido.
     */
    public function getFullNameAttribute()
    {
        if ($this->last_name) {
            return $this->name . ' ' . $this->last_name;
        }
        return $this->name;
    }

    /**
     * Accesor: iniciales del usuario.
     *
     * Genera iniciales a partir del nombre y apellido. Si no hay apellido,
     * usa las dos primeras letras del nombre. Si el nombre tiene dos o más
     * palabras, usa la primera letra de las dos primeras palabras.
     * Se accede como $user->initials.
     *
     * @return string Iniciales en mayúsculas (ej: "JD", "MA").
     */
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

    /**
     * Accesor: URL completa de la foto de perfil.
     *
     * Si el usuario tiene una foto personalizada, retorna la URL desde el
     * storage público. Si no, genera una imagen placeholder con las iniciales
     * del usuario usando ui-avatars.com.
     * Se accede como $user->profile_photo_url.
     *
     * @return string URL de la imagen de perfil.
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&color=7F9CF5&background=EBF4FF&size=200';
    }

    /**
     * Accesor: identificación completa (tipo-número).
     *
     * Concatena el tipo de documento con el número, separados por guión.
     * Por ejemplo: "V-12345678" o "J-123456789". Si falta algún dato, retorna null.
     * Se accede como $user->full_id.
     *
     * @return string|null Identificación completa o null si falta algún campo.
     */
    public function getFullIdAttribute()
    {
        if ($this->id_type && $this->id_number) {
            return $this->id_type . '-' . $this->id_number;
        }
        return null;
    }

    // ==========================================
    //  UBICACIÓN
    // ==========================================

    /**
     * Accesor: nombre del país del usuario.
     *
     * Obtiene el nombre del país a través de la relación country.
     * Retorna "No especificado" si no tiene país asociado.
     * Se accede como $user->country_name.
     *
     * @return string Nombre del país o valor por defecto.
     */
    public function getCountryNameAttribute()
    {
        return data_get($this, 'country.name', 'No especificado');
    }

    /**
     * Accesor: nombre del estado del usuario.
     *
     * Obtiene el nombre del estado a través de la relación state.
     * Retorna "No especificado" si no tiene estado asociado.
     * Se accede como $user->state_name.
     *
     * @return string Nombre del estado o valor por defecto.
     */
    public function getStateNameAttribute()
    {
        return data_get($this, 'state.name', 'No especificado');
    }

    /**
     * Accesor: nombre del municipio del usuario.
     *
     * Obtiene el nombre del municipio a través de la relación municipality.
     * Retorna "No especificado" si no tiene municipio asociado.
     * Se accede como $user->municipality_name.
     *
     * @return string Nombre del municipio o valor por defecto.
     */
    public function getMunicipalityNameAttribute()
    {
        return data_get($this, 'municipality.name', 'No especificado');
    }

    /**
     * Accesor: nombre de la parroquia del usuario.
     *
     * Obtiene el nombre de la parroquia a través de la relación parish.
     * Retorna "No especificado" si no tiene parroquia asociada.
     * Se accede como $user->parish_name.
     *
     * @return string Nombre de la parroquia o valor por defecto.
     */
    public function getParishNameAttribute()
    {
        return data_get($this, 'parish.name', 'No especificado');
    }

    /**
     * Accesor: nombre de la ciudad del usuario.
     *
     * Obtiene el nombre de la ciudad a través de la relación userCity.
     * Retorna "No especificado" si no tiene ciudad asociada.
     * Se accede como $user->city_name.
     *
     * @return string Nombre de la ciudad o valor por defecto.
     */
    public function getCityNameAttribute()
    {
        return data_get($this, 'userCity.name', 'No especificado');
    }

    /**
     * Accesor: ubicación completa formateada.
     *
     * Construye una cadena con país, estado y ciudad separados por comas.
     * Solo incluye los niveles que tengan valor. Si no hay ningún nivel
     * definido, retorna "No especificada".
     * Se accede como $user->full_location.
     *
     * @return string Ubicación formateada (ej: "Venezuela, Zulia, Maracaibo").
     */
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

    /**
     * Verifica si el usuario tiene preguntas de seguridad configuradas.
     *
     * Valida que exista la fecha de configuración y que las tres preguntas
     * y sus respuestas estén presentes y no vacías. Se usa para determinar
     * si se puede iniciar el flujo de recuperación de contraseña por
     * preguntas de seguridad.
     *
     * @return bool True si el usuario tiene preguntas y respuestas configuradas.
     */
    public function hasSecurityQuestions(): bool
    {
        return !is_null($this->security_questions_set_at) &&
               !empty($this->security_questions) &&
               !empty($this->security_answer_1) &&
               !empty($this->security_answer_2) &&
               !empty($this->security_answer_3);
    }

    /**
     * Verifica la respuesta a una pregunta de seguridad específica.
     *
     * Compara la respuesta proporcionada con la almacenada (hasheada)
     * usando Hash::check. El índice es 0-based pero se convierte internamente
     * al nombre del campo (security_answer_1, security_answer_2, etc.).
     *
     * Flujo de datos:
     * - Recibe el índice de la pregunta y la respuesta en texto plano.
     * - Obtiene el campo correspondiente del modelo (hash almacenado).
     * - Compara usando verificación de hash para nunca comparar texto plano.
     *
     * @param int    $questionIndex Índice de la pregunta (0, 1 o 2).
     * @param string $answer       Respuesta en texto plano a verificar.
     *
     * @return bool True si la respuesta es correcta, false si no o si el campo está vacío.
     */
    public function verifySecurityAnswer(int $questionIndex, string $answer): bool
    {
        $answerField = 'security_answer_' . ($questionIndex + 1);

        if (empty($this->$answerField)) {
            return false;
        }

        return Hash::check($answer, $this->$answerField);
    }

    /**
     * Obtiene las preguntas de seguridad con su índice y campo de respuesta.
     *
     * Transforma el array de preguntas almacenadas en un array asociativo
     * que incluye el índice numérico, el texto de la pregunta y el nombre
     * del campo de respuesta correspondiente. Útil para formularios de
     * verificación de seguridad donde se necesita saber qué campo actualizar.
     *
     * @return array Array de arrays con keys: index, question, answer_field.
     *               Retorna array vacío si no hay preguntas configuradas.
     */
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

    /**
     * Relación: país del usuario.
     *
     * Relaciona el usuario con su país a través de country_id.
     * Se usa para resolver el nombre del país en los accesorres.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Relación: estado del usuario.
     *
     * Relaciona el usuario con su estado a través de state_id.
     * Se usa para resolver el nombre del estado en los accesorres.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Relación: municipio del usuario.
     *
     * Relaciona el usuario con su municipio a través de municipality_id.
     * Se usa para resolver el nombre del municipio en los accesorres.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    /**
     * Relación: parroquia del usuario.
     *
     * Relaciona el usuario con su parroquia a través de parish_id.
     * Se usa para resolver el nombre de la parroquia en los accesorres.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    /**
     * Relación: ciudad del usuario.
     *
     * Relaciona el usuario con su ciudad a través de city_id.
     * Se llama "userCity" para evitar conflicto con el trait "HasFactory"
     * o el nombre base "city". Se usa para resolver el nombre de la ciudad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userCity()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    // ==========================================
    // RELACIONES DEL SISTEMA
    // ==========================================

    /**
     * Relación: propiedades publicadas por el usuario.
     *
     * Un usuario (generalmente un asesor) puede publicar múltiples propiedades.
     * Relaciona con el modelo Property a través de user_id.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'user_id');
    }

    /**
     * Relación: citas donde el usuario es el cliente.
     *
     * Un cliente puede agendar múltiples citas para visitar propiedades.
     * Relaciona con Appointment a través de user_id (el campo del cliente).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointmentsAsClient()
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    /**
     * Relación: citas donde el usuario es el asesor asignado.
     *
     * Un asesor inmobiliario puede ser asignado a múltiples citas.
     * Relaciona con Appointment a través de asesor_id.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointmentsAsAsesor()
    {
        return $this->hasMany(Appointment::class, 'asesor_id');
    }

    // ==========================================
    // RELACIONES DE FAVORITOS
    // ==========================================

    /**
     * Relación: registros de favoritos del usuario.
     *
     * Relaciona con el modelo Favorite (tabla pivote) a través de user_id.
     * Se usa principalmente para contar favoritos o gestionar el registro
     * pivote directamente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    /**
     * Relación: propiedades marcadas como favoritas por el usuario.
     *
     * Relación many-to-many entre User y Property a través de la tabla pivote
     * "favorites". Incluye timestamps y datos del pivote (id, created_at).
     * Ordenadas por fecha de favorito de más reciente a más antiguo.
     * Se usa para listar las propiedades favoritas del usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoriteProperties()
    {
        return $this->belongsToMany(Property::class, 'favorites', 'user_id', 'property_id')
                    ->withTimestamps()
                    ->withPivot('id', 'created_at')
                    ->orderBy('favorites.created_at', 'desc');
    }

    /**
     * Relación alias de favoriteProperties().
     *
     * Proporciona un nombre alternativo más semántico para la misma
     * relación many-to-many. Permite usar $user->favoritedProperties()
     * de forma más clara en el contexto de la aplicación.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoritedProperties()
    {
        return $this->favoriteProperties();
    }

    /**
     * Relación: leads generados por el usuario.
     *
     * Un usuario puede generar múltiples leads (intereses en propiedades).
     * Relaciona con el modelo Lead. La dirección de la relación depende
     * de la configuración del modelo Lead (puede ser usuario como lead
     * o como quien recibe el lead).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    // ==========================================
    // NOTIFICACIONES PERSONALIZADAS
    // ==========================================

    /**
     * Relación: todas las notificaciones del usuario.
     *
     * Relaciona con el modelo UserNotification, que es un sistema de
     * notificaciones propio (no usa el de Laravel). Ordenadas de más
     * reciente a más antiguo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications()
    {
        return $this->hasMany(UserNotification::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relación: notificaciones no leídas del usuario.
     *
     * Filtra las notificaciones donde read_at es NULL, es decir, las que
     * el usuario aún no ha visto. Se usa para el badge de notificaciones
     * y para determinar si hay notificaciones pendientes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unreadNotifications()
    {
        return $this->hasMany(UserNotification::class)->whereNull('read_at')->orderBy('created_at', 'desc');
    }

    /**
     * Relación: notificaciones leídas del usuario.
     *
     * Filtra las notificaciones donde read_at no es NULL, es decir, las que
     * el usuario ya ha visto. Se usa para mostrar historial de notificaciones
     * y para limpiar notificaciones antiguas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function readNotifications()
    {
        return $this->hasMany(UserNotification::class)->whereNotNull('read_at')->orderBy('created_at', 'desc');
    }

    /**
     * Crea una notificación personalizada para el usuario.
     *
     * Genera un registro en la tabla user_notifications con los datos
     * proporcionados. Se usa desde controladores o servicios para notificar
     * eventos como nuevas citas, cambios de estado, mensajes, etc.
     *
     * Flujo de datos:
     * - Recibe título, mensaje, tipo, URL opcional y datos adicionales.
     * - Crea el registro a través de la relación notifications.
     * - Retorna el modelo UserNotification creado.
     *
     * @param string     $title   Título de la notificación.
     * @param string     $message Contenido/mensaje de la notificación.
     * @param string     $type    Tipo de notificación (info, success, warning, error).
     * @param string|null $url    URL opcional a la que redirige la notificación.
     * @param array|null $data    Datos adicionales en formato array (se almacena como JSON).
     *
     * @return \App\Models\UserNotification Modelo de notificación creado.
     */
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

    /**
     * Cuenta las notificaciones no leídas del usuario.
     *
     * Ejecuta un COUNT sobre las notificaciones sin leer. Se usa para
     * mostrar el número de notificaciones pendientes en la interfaz
     * (badge, campana, etc.).
     *
     * @return int Cantidad de notificaciones no leídas.
     */
    public function unreadNotificationsCount()
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Marca todas las notificaciones no leídas como leídas.
     *
     * Actualiza el campo read_at con la fecha/hora actual en todas las
     * notificaciones pendientes del usuario. Se usa cuando el usuario
     * abre el panel de notificaciones o hace clic en "marcar todo como leído".
     *
     * @return int Número de registros actualizados.
     */
    public function markAllNotificationsAsRead()
    {
        return $this->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Elimina todas las notificaciones leídas del usuario.
     *
     * Borra permanentemente las notificaciones que ya fueron leídas.
     * Se usa para limpiar el historial de notificaciones y liberar espacio.
     *
     * @return int Número de registros eliminados.
     */
    public function clearReadNotifications()
    {
        return $this->readNotifications()->delete();
    }

    /**
     * Elimina todas las notificaciones del usuario.
     *
     * Borra permanentemente todas las notificaciones (leídas y no leídas).
     * Se usa para una limpieza completa del historial de notificaciones.
     *
     * @return int Número de registros eliminados.
     */
    public function clearAllNotifications()
    {
        return $this->notifications()->delete();
    }

    // ==========================================
    // MÉTODOS DE ROL (HELPERS)
    // ==========================================

    /**
     * Verifica si el usuario tiene el rol de Super Administrador.
     *
     * El Super Admin tiene acceso total al sistema, incluyendo gestión
     * de usuarios, configuración global y todos los módulos.
     *
     * @return bool True si el usuario tiene el rol "Super Admin".
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Verifica si el usuario tiene el rol de Administrador.
     *
     * El Administrador gestiona propiedades, usuarios y configuraciones
     * del sistema, pero con menos permisos que el Super Admin.
     *
     * @return bool True si el usuario tiene el rol "Administrador".
     */
    public function isAdmin()
    {
        return $this->hasRole('Administrador');
    }

    /**
     * Verifica si el usuario tiene el rol de Asesor Inmobiliario.
     *
     * El asesor gestiona propiedades asignadas, agenda citas con clientes
     * y gestiona leads. Es el rol principal de venta en el sistema.
     *
     * @return bool True si el usuario tiene el rol "Asesor Inmobiliario".
     */
    public function isAsesor()
    {
        return $this->hasRole('Asesor Inmobiliario');
    }

    /**
     * Verifica si el usuario tiene el rol de Auditor.
     *
     * El auditor revisa operaciones, genera reportes y supervisa actividades
     * del sistema sin permisos de modificación directa.
     *
     * @return bool True si el usuario tiene el rol "Auditor".
     */
    public function isAuditor()
    {
        return $this->hasRole('Auditor');
    }

    /**
     * Verifica si el usuario tiene el rol de Cliente.
     *
     * El cliente busca propiedades, agenda citas, guarda favoritos y
     * genera leads. Es el rol con permisos más limitados del sistema.
     *
     * @return bool True si el usuario tiene el rol "Cliente".
     */
    public function isCliente()
    {
        return $this->hasRole('Cliente');
    }

    /**
     * Accesor: nombre del rol principal del usuario.
     *
     * Obtiene el primer rol asignado al usuario. Si no tiene ningún rol,
     * retorna "Sin Rol". Se usa para mostrar el rol en la interfaz.
     * Se accede como $user->main_role.
     *
     * @return string Nombre del rol principal o "Sin Rol".
     */
    public function getMainRoleAttribute()
    {
        $role = $this->roles->first();
        return $role ? $role->name : 'Sin Rol';
    }

    // ==========================================
    // MÉTODOS DE FAVORITOS
    // ==========================================

    /**
     * Agrega una propiedad a los favoritos del usuario.
     *
     * Usa syncWithoutDetaching para evitar duplicados: si la propiedad
     * ya está en favoritos, no se vuelve a insertar. Opera sobre la
     * relación many-to-many con la tabla pivote "favorites".
     *
     * @param \App\Models\Property $property Propiedad a agregar como favorita.
     *
     * @return bool True si se insertó, false si ya existía.
     */
    public function addFavorite(Property $property)
    {
        return $this->favoriteProperties()->syncWithoutDetaching([$property->id]);
    }

    /**
     * Elimina una propiedad de los favoritos del usuario.
     *
     * Elimina el registro de la tabla pivote "favorites" correspondiente
     * a la propiedad indicada. Opera sobre la relación many-to-many.
     *
     * @param \App\Models\Property $property Propiedad a eliminar de favoritos.
     *
     * @return int Número de registros eliminados.
     */
    public function removeFavorite(Property $property)
    {
        return $this->favoriteProperties()->detach($property->id);
    }

    /**
     * Verifica si una propiedad está en los favoritos del usuario.
     *
     * Consulta la tabla pivote "favorites" para determinar si la propiedad
     * ya fue marcada como favorita por el usuario.
     *
     * @param \App\Models\Property $property Propiedad a verificar.
     *
     * @return bool True si la propiedad es favorita del usuario.
     */
    public function hasFavorite(Property $property)
    {
        return $this->favoriteProperties()->where('property_id', $property->id)->exists();
    }

    /**
     * Accesor: array de IDs de propiedades favoritas.
     *
     * Obtiene solo los IDs de las propiedades marcadas como favoritas.
     * Se usa para verificar rápidamente si una propiedad es favorita
     * o para filtrar propiedades en vistas.
     * Se accede como $user->favorite_ids.
     *
     * @return array Array de IDs de propiedades favoritas.
     */
    public function getFavoriteIdsAttribute()
    {
        return $this->favoriteProperties()->pluck('property_id')->toArray();
    }

    /**
     * Accesor: cantidad total de propiedades favoritas.
     *
     * Cuenta el número de registros en la tabla pivote "favorites"
     * para el usuario actual. Se usa para mostrar estadísticas o badges.
     * Se accede como $user->favorites_count.
     *
     * @return int Número de propiedades favoritas.
     */
    public function getFavoritesCountAttribute()
    {
        return $this->favorites()->count();
    }

    // ==========================================
    // SCOPES (FILTROS DE CONSULTA)
    // ==========================================

    /**
     * Scope: filtra usuarios activos.
     *
     * Restringe la consulta a usuarios donde is_active = true.
     * Se usa para excluir usuarios desactivados del sistema.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Consulta a filtrar.
     *
     * @return \Illuminate\Database\Eloquent\Builder Consulta filtrada.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: filtra usuarios con rol de Asesor Inmobiliario.
     *
     * Utiliza el trait HasRoles de Spatie para filtrar por el rol
     * "Asesor Inmobiliario". Se usa para listar asesores disponibles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Consulta a filtrar.
     *
     * @return \Illuminate\Database\Eloquent\Builder Consulta filtrada.
     */
    public function scopeAsesores($query)
    {
        return $query->role('Asesor Inmobiliario');
    }

    /**
     * Scope: filtra usuarios con rol de Cliente.
     *
     * Utiliza el trait HasRoles de Spatie para filtrar por el rol
     * "Cliente". Se usa para listar clientes registrados en el sistema.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Consulta a filtrar.
     *
     * @return \Illuminate\Database\Eloquent\Builder Consulta filtrada.
     */
    public function scopeClientes($query)
    {
        return $query->role('Cliente');
    }

    /**
     * Scope: filtra usuarios que tienen preguntas de seguridad configuradas.
     *
     * Restringe la consulta a usuarios donde security_questions_set_at
     * no es NULL. Se usa para identificar usuarios que pueden usar el
     * flujo de recuperación por preguntas de seguridad.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Consulta a filtrar.
     *
     * @return \Illuminate\Database\Eloquent\Builder Consulta filtrada.
     */
    public function scopeWithSecurityQuestions($query)
    {
        return $query->whereNotNull('security_questions_set_at');
    }

    /**
     * Scope: filtra usuarios que NO tienen preguntas de seguridad configuradas.
     *
     * Restringe la consulta a usuarios donde security_questions_set_at
     * es NULL. Se usa para identificar usuarios que aún no han configurado
     * sus preguntas de seguridad.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Consulta a filtrar.
     *
     * @return \Illuminate\Database\Eloquent\Builder Consulta filtrada.
     */
    public function scopeWithoutSecurityQuestions($query)
    {
        return $query->whereNull('security_questions_set_at');
    }

    /**
     * Envía la notificación de restablecimiento de contraseña.
     *
     * Implementación del método de la interfaz CanResetPassword. Envía
     * un email al usuario con un token único para restablecer su contraseña.
     * El token se genera automáticamente por Laravel y se almacena en la
     * tabla password_resets.
     *
     * @param string $token Token único de restablecimiento generado por Laravel.
     *
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Obtiene la jerarquía completa de ubicación del usuario.
     *
     * Retorna un array asociativo con cada nivel de la ubicación
     * (país, estado, municipio, parroquia, ciudad) y la cadena
     * formateada completa. Se usa para APIs o vistas que necesitan
     * toda la información de ubicación en una sola llamada.
     *
     * @return array Array con keys: country, state, municipality, parish, city, full.
     */
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

    /**
     * Relación: registros de auditoría donde el usuario es el sujeto.
     *
     * Relación polimórfica morphMany. Registra todas las acciones donde
     * el usuario es el objeto afectado (ej: "se actualizó el perfil de Juan").
     * El campo subject_type y subject_id en AuditLog apunta a este modelo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function auditLogsAsSubject()
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }

    /**
     * Relación: registros de auditoría donde el usuario es el autor.
     *
     * Registra todas las acciones realizadas por el usuario (ej: "Juan
     * creó una propiedad"). El campo user_id en AuditLog almacena el ID
     * del usuario que ejecutó la acción.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function auditLogsAsAuthor()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}
