<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Servicio centralizado de permisos y autorizacion para la aplicacion.
 *
 * Actua como capa de abstraccion sobre Spatie Permission, encapsulando toda
 * la logica de verificacion de permisos y roles del sistema. Su proposito
 * principal es ofrecer una API unificada para que controladores, vistas Blade
 * y middleware puedan consultar el estado de autorizacion de un usuario sin
 * acoplarse directamente a Spatie.
 *
 * Jerarquia de roles:
 * - Super Admin: Posee todos los permisos de forma implicita. En la mayoria
 *   de metodos se retorna true inmediatamente sin consultar la base de datos.
 *   La excepcion es canSeeSidebarItem(), donde el Super Admin RESPETA los
 *   permisos asignados en la tabla pivot, garantizando que solo vea en el
 *   sidebar los modulos que le han sido otorgados expresamente.
 * - Administrador: Permisos elevados para gestion de usuarios, roles,
 *   propiedades y configuracion del sistema.
 * - Asesor Inmobiliario: Acceso a leads, citas, propiedades y chat.
 * - Auditor: Acceso de lectura a logs de auditoria, reportes y leads.
 * - Cliente: Acceso limitado a propiedades, favoritos y chat.
 *
 * Estrategia de cache:
 * - El metodo clearCache() invalida la clave "user_permissions_{id}" en
 *   Cache::forget(), lo que permite que despues de un cambio de permisos
 *   el usuario obtenga datos frescos en la siguiente consulta.
 * - Se espera que los modelos User o Role llamen a clearCache() despues
 *   de sincronizar permisos para mantener la consistencia.
 *
 * Flujo general de datos:
 * 1. El servicio recibe un usuario via constructor o setUser().
 * 2. Cada metodo de verificacion obtiene el usuario through getUser().
 * 3. Se evalua primero si el usuario es Super Admin (bypass rapido).
 * 4. Si no es Super Admin, se delega a los metodos de Spatie
 *    (hasPermissionTo, hasRole, hasAnyPermission, etc.).
 * 5. Para el sidebar, se usa hasDirectPermission() en lugar de la cache
 *    interna de Spatie, asegurando verificacion directa en la base de datos.
 */
class PermissionService
{
    /**
     * Instancia del usuario autenticado sobre el cual se verifican permisos.
     *
     * Se establece en el constructor (por defecto el usuario de Auth) o
     * mediante setUser(). Puede ser reemplazado para verificar permisos
     * de un usuario diferente al autenticado actualmente.
     *
     * @var \App\Models\User|null
     */
    protected $user;

    /**
     * Mapa de roles a sus rutas de dashboard correspondientes.
     *
     * Cada entrada asocia el nombre exacto del rol en Spatie con el nombre
     * de la ruta definida en routes/web.php. Se utiliza en getDashboardRoute()
     * para redirigir al usuario a su panel principal segun su rol principal.
     * Si el rol no se encuentra en el mapa, se usa la ruta generica "dashboard.generic".
     *
     * @var array<string, string>
     */
    protected array $dashboardMap = [
        'Super Admin' => 'super-admin.dashboard',
        'Administrador' => 'admin.dashboard',
        'Asesor Inmobiliario' => 'asesor.dashboard',
        'Auditor' => 'auditor.dashboard',
        'Cliente' => 'cliente.dashboard',
    ];

    /**
     * Mapa de permisos del sidebar a sus etiquetas legibles.
     *
     * Cada clave es el identificador unico del permiso que se verifica
     * en la base de datos (tabla permissions de Spatie). El valor es
     * la etiqueta que se muestra en la interfaz del sidebar.
     *
     * Este array es la fuente de verdad para getSidebarItems(), que
     * recorre cada entrada y verifica si el usuario actual tiene el
     * permiso correspondiente via canSeeSidebarItem().
     *
     * @var array<string, string>
     */
    protected array $sidebarPermissions = [
        'sidebar.dashboard' => 'Dashboard',
        'sidebar.catalogo' => 'Catálogo',
        'sidebar.users' => 'Gestión de Usuarios',
        'sidebar.roles' => 'Roles y Permisos',
        'sidebar.properties' => 'Propiedades',
        'sidebar.leads' => 'Leads',
        'sidebar.appointments' => 'Citas',
        'sidebar.chat' => 'Chat',
        'sidebar.audit' => 'Auditoría',
        'sidebar.reports' => 'Reportes Gerenciales',
        'sidebar.config' => 'Configuración',
        'sidebar.favorites' => 'Mis Favoritos',
        'sidebar.servicios' => 'Servicios',
        'sidebar.categorias' => 'Categorías',
        'sidebar.ubicaciones' => 'Ubicaciones',
        'sidebar.telefonos' => 'Formato Telefónico',
        'sidebar.profile' => 'Mi Perfil',
    ];

    /**
     * Constructor del servicio de permisos.
     *
     * Inicializa el servicio con el usuario que sera evaluado. Si no se
     * proporciona un usuario, se toma automaticamente el usuario autenticado
     * via Auth::user(). Esto permite tanto el uso normal (usuario actual)
     * como la verificacion de permisos de otros usuarios (contexto admin).
     *
     * @param \App\Models\User|null $user Usuario sobre el cual verificar permisos.
     *                                    Si es null, se usa el usuario autenticado.
     */
    public function __construct($user = null)
    {
        $this->user = $user ?? Auth::user();
    }

    /**
     * Establece el usuario sobre el cual se verificarán los permisos.
     *
     * Permite cambiar dinamicamente el usuario del servicio, util cuando
     * se necesita verificar permisos de otro usuario sin crear una nueva
     * instancia del servicio. Implementa el patron builder para permitir
     * encadenamiento de llamadas.
     *
     * @param  \App\Models\User $user Nuevo usuario a evaluar.
     * @return self Instancia actual del servicio (para encadenamiento).
     */
    public function setUser($user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Obtiene el usuario actual del servicio.
     *
     * Metodo interno utilizado por todos los metodos de verificacion de
     * permisos. Primero verifica si existe un usuario en la propiedad
     * $this->user; si no, recurre a Auth::user() como fallback. Esto
     * garantiza que siempre se tenga un usuario valido antes de consultar
     * permisos en Spatie.
     *
     * @return \App\Models\User|null Usuario encontrado o null si no hay sesion activa.
     */
    protected function getUser()
    {
        // Si ya tenemos un usuario en la propiedad, usarlo
        if ($this->user) {
            return $this->user;
        }

        // Si no hay usuario, intentar obtener de Auth
        return Auth::user();
    }



    /**
     * Verifica si el usuario tiene un permiso especifico.
     *
     * Flujo de ejecucion:
     * 1. Obtiene el usuario via getUser().
     * 2. Si no hay usuario, retorna false.
     * 3. Si el usuario tiene el rol "Super Admin", retorna true sin
     *    consultar la base de datos (bypass rapido).
     * 4. Delega la verificacion a Spatie's hasPermissionTo().
     *
     * @param  string $permission Nombre exacto del permiso en la tabla permissions.
     * @return bool True si el usuario posee el permiso, false en caso contrario.
     */
    public function hasPermission(string $permission): bool
    {
        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        // Super Admin tiene todos los permisos
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }

    /**
     * Verifica si el usuario tiene al menos uno de los permisos indicados.
     *
     * Util para menus y funcionalidades que requieren cualquiera de varios
     * permisos (por ejemplo: "crear usuario" O "editar usuario" O "eliminar usuario").
     * El Super Admin obtiene true de forma inmediata.
     *
     * @param  array $permissions Lista de nombres de permisos a verificar.
     * @return bool True si el usuario posee al menos uno de los permisos.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasAnyPermission($permissions);
    }

    /**
     * Verifica si el usuario posee todos los permisos indicados.
     *
     * A diferencia de hasAnyPermission(), este metodo exige que el usuario
     * tenga TODOS los permisos de la lista. El Super Admin obtiene true
     * de forma inmediata.
     *
     * @param  array $permissions Lista de nombres de permisos que deben estar todos presentes.
     * @return bool True si el usuario posee la totalidad de los permisos.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasAllPermissions($permissions);
    }

    /**
     * Verifica si el usuario tiene un rol especifico.
     *
     * Delega directamente a Spatie's hasRole(). A diferencia de los metodos
     * de permisos, este NO aplica bypass para Super Admin porque se utiliza
     * internamente para IDENTIFICAR si el usuario es Super Admin, lo cual
     * crearia un bucle infinito.
     *
     * @param  string $role Nombre exacto del rol en la tabla roles.
     * @return bool True si el usuario posee el rol.
     */
    public function hasRole(string $role): bool
    {
        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        return $user->hasRole($role);
    }

    /**
     * Verifica si el usuario tiene al menos uno de los roles indicados.
     *
     * Se usa en metodos compuestos como canManageUsers() o canViewLeads()
     * para determinar acceso basado en el rol del usuario sin necesidad
     * de verificar permisos individuales.
     *
     * @param  array $roles Lista de nombres de roles a verificar.
     * @return bool True si el usuario posee al menos uno de los roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        return $user->hasAnyRole($roles);
    }

    /**
     * Obtiene la lista de nombres de roles asignados al usuario.
     *
     * Consulta la relacion roles del modelo User a traves de Spatie y
     * extrae unicamente los nombres. Se utiliza internamente por
     * getMainRole() y getRoleDisplayName().
     *
     * @return array Lista de nombres de roles (por ejemplo: ['Super Admin']).
     */
    public function getRoles(): array
    {
        $user = $this->getUser();

        if (!$user) {
            return [];
        }

        return $user->roles()->pluck('name')->toArray();
    }

    /**
     * Obtiene el primer rol (rol principal) del usuario.
     *
     * Retorna el primer elemento del array devuelto por getRoles(). En el
     * contexto de esta aplicacion, cada usuario deberia tener un solo rol
     * principal que determina su dashboard, su sidebar y sus permisos
     * generales. Si el usuario no tiene roles, retorna null.
     *
     * @return string|null Nombre del rol principal o null si no tiene roles.
     */
    public function getMainRole(): ?string
    {
        $roles = $this->getRoles();
        return $roles[0] ?? null;
    }

    /**
     * Obtiene el nombre visible del rol principal para mostrar en la interfaz.
     *
     * Mapea el nombre interno del rol (usado en Spatie) a su nombre en
     * formato presentacion. Por ejemplo, "Super Admin" se convierte en
     * "Super Administrador". Si el rol no se encuentra en el mapa, retorna
     * el nombre del rol tal cual. Si no hay rol, retorna "Usuario" como
     * valor por defecto.
     *
     * @return string Nombre legible del rol para mostrar en la UI.
     */
    public function getRoleDisplayName(): string
    {
        $roleNames = [
            'Super Admin' => 'Super Administrador',
            'Administrador' => 'Administrador',
            'Asesor Inmobiliario' => 'Asesor Inmobiliario',
            'Auditor' => 'Auditor',
            'Cliente' => 'Cliente',
        ];

        $role = $this->getMainRole();
        return $roleNames[$role] ?? $role ?? 'Usuario';
    }

    /**
     * Obtiene la URL del dashboard correspondiente al rol principal del usuario.
     *
     * Consulta el array $dashboardMap para encontrar la ruta asociada al
     * rol principal del usuario. Si el rol no tiene una entrada en el mapa
     * (por ejemplo, un rol nuevo no contemplado), retorna la ruta generica
     * "dashboard.generic". Se utiliza en middleware y controladores para
     * redirigir al usuario a su panel principal despues del login.
     *
     * @return string URL completa del dashboard (generada por route()).
     */
    public function getDashboardRoute(): string
    {
        $role = $this->getMainRole();

        if (isset($this->dashboardMap[$role])) {
            return route($this->dashboardMap[$role]);
        }

        return route('dashboard.generic');
    }

    // ==========================================
    // MÉTODOS DEL SIDEBAR
    // ==========================================

    /**
     * Determina si el usuario puede ver un elemento del sidebar.
     *
     * Este metodo tiene un comportamiento INTENCIONALMENTE DIFERENTE del resto
     * de metodos de permisos respecto al Super Admin:
     *
     * - En hasPermission(), hasAnyPermission() y hasAllPermissions(), el
     *   Super Admin recibe true de forma inmediata (bypass total).
     * - En canSeeSidebarItem(), el Super Admin NO recibe bypass. Debe tener
     *   el permiso asignado explicitamente en la base de datos para ver cada
     *   modulo del sidebar.
     *
     * Razon de disenio: Permite controlar granularmente que modulos son
     * visibles en el panel lateral incluso para el Super Admin. Esto es
     * util en escenarios donde se quiere ocultar ciertos modulos del
     * sidebar por defecto, o donde el Super Admin solo deberia ver los
     * modulos que le han sido asignados administrativamente.
     *
     * Flujo de verificacion:
     * 1. Obtiene el usuario via getUser().
     * 2. Si no hay usuario, retorna false.
     * 3. Verifica si el usuario tiene el permiso de forma directa
     *    (hasDirectPermission) o via rol (hasPermissionTo) en Spatie.
     * 4. Retorna true solo si al menos una de las dos verificaciones es positiva.
     *
     * @param  string $permission Identificador del permiso del sidebar (clave de $sidebarPermissions).
     * @return bool True si el usuario tiene acceso al elemento del sidebar.
     */
    public function canSeeSidebarItem(string $permission): bool
    {
        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        // Para sidebar, el Super Admin TAMBIÉN respeta los permisos guardados
        // Si no tiene el permiso asignado en la DB, no lo ve
        return $user->hasDirectPermission($permission) || $user->hasPermissionTo($permission);
    }

    /**
     * Construye la lista completa de elementos del sidebar con su visibilidad.
     *
     * Recorre todas las entradas de $sidebarPermissions y, para cada una,
     * verifica via canSeeSidebarItem() si el usuario actual tiene acceso.
     * Retorna un array asociativo donde cada clave es el identificador del
     * permiso y el valor contiene la etiqueta, si es visible y el permiso
     * asociado. Este array se inyecta en las vistas Blade para renderizar
     * dinamicamente el menu lateral segun los permisos del usuario.
     *
     * @return array<string, array{label: string, visible: bool, permission: string}>
     */
    public function getSidebarItems(): array
    {
        $items = [];

        foreach ($this->sidebarPermissions as $permission => $label) {
            $items[$permission] = [
                'label' => $label,
                'visible' => $this->canSeeSidebarItem($permission),
                'permission' => $permission,
            ];
        }

        return $items;
    }

    // ==========================================
    // PERMISOS COMPUESTOS
    // ==========================================

    /**
     * Verifica si el usuario puede gestionar usuarios del sistema.
     *
     * Retorna true si el usuario tiene el rol Super Admin o Administrador,
     * o si posee cualquiera de los permisos: "ver usuarios", "gestionar usuarios",
     * "crear usuario", "editar usuario", "eliminar usuario".
     *
     * @return bool True si el usuario tiene acceso a la gestion de usuarios.
     */
    public function canManageUsers(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Administrador']) ||
               $this->hasAnyPermission(['ver usuarios', 'gestionar usuarios', 'crear usuario', 'editar usuario', 'eliminar usuario']);
    }

    /**
     * Verifica si el usuario puede gestionar roles y permisos.
     *
     * Solo el Super Admin o usuarios con permisos especificos de roles
     * (ver, crear, editar, eliminar) pueden acceder a esta funcionalidad.
     * La gestion de roles es una operacion critica del sistema que afecta
     * los permisos de todos los usuarios.
     *
     * @return bool True si el usuario tiene acceso a la gestion de roles.
     */
    public function canManageRoles(): bool
    {
        return $this->hasRole('Super Admin') ||
               $this->hasAnyPermission(['ver roles', 'crear rol', 'editar rol', 'eliminar rol']);
    }

    /**
     * Verifica si el usuario puede gestionar propiedades inmobiliarias.
     *
     * Retorna true si el usuario NO es Cliente, o si posee permisos
     * especificos de propiedades. La logica inversa (!hasRole('Cliente'))
     * permite que todos los roles internos (Asesor, Auditor, Admin, etc.)
     * tengan acceso por defecto, mientras que el Cliente solo accede si
     * tiene permisos explicitos.
     *
     * @return bool True si el usuario tiene acceso a la gestion de propiedades.
     */
    public function canManageProperties(): bool
    {
        return !$this->hasRole('Cliente') ||
               $this->hasAnyPermission(['ver propiedades', 'gestionar propiedades', 'crear propiedad', 'editar propiedad', 'eliminar propiedad']);
    }

    /**
     * Verifica si el usuario puede visualizar leads inmobiliarios.
     *
     * Accesible para Super Admin, Administrador, Asesor Inmobiliario y
     * Auditor por rol, o para cualquier usuario con permisos especificos
     * de leads. El Cliente no tiene acceso por defecto a los leads.
     *
     * @return bool True si el usuario puede acceder al modulo de leads.
     */
    public function canViewLeads(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Administrador', 'Asesor Inmobiliario', 'Auditor']) ||
               $this->hasAnyPermission(['ver leads', 'gestionar leads', 'crear lead', 'editar lead', 'eliminar lead']);
    }

    /**
     * Verifica si el usuario puede gestionar (crear, editar, eliminar) leads.
     *
     * A diferencia de canViewLeads(), este metodo es mas restrictivo: solo
     * Super Admin, Administrador y Asesor Inmobiliario pueden gestionar
     * leads por rol. El Auditor solo tiene acceso de lectura. Tambien se
     * verifican permisos individuales de creacion, edicion y eliminacion.
     *
     * @return bool True si el usuario puede modificar leads.
     */
    public function canManageLeads(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Administrador', 'Asesor Inmobiliario']) ||
               $this->hasAnyPermission(['crear lead', 'editar lead', 'eliminar lead', 'gestionar leads']);
    }

    /**
     * Verifica si el usuario puede acceder al modulo de auditoria.
     *
     * Disponible para Super Admin, Administrador y Auditor por rol, o para
     * usuarios con permisos de "ver logs de auditoria", "acceso auditoria"
     * o "ver reportes". El modulo de auditoria es de solo lectura y permite
     * revisar el historial de acciones del sistema.
     *
     * @return bool True si el usuario puede acceder a la auditoria.
     */
    public function canViewAudit(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Administrador', 'Auditor']) ||
               $this->hasAnyPermission(['ver logs de auditoria', 'acceso auditoria', 'ver reportes']);
    }

    /**
     * Verifica si el usuario puede acceder a reportes gerenciales.
     *
     * Disponible para usuarios con permisos "ver reportes" o "exportar
     * reportes", o para Super Admin, Administrador y Auditor por rol.
     * Los reportes incluyen metricas y estadisticas del negocio
     * inmobiliario.
     *
     * @return bool True si el usuario puede acceder a los reportes.
     */
    public function canViewReports(): bool
    {
        return $this->hasAnyPermission(['ver reportes', 'exportar reportes']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador', 'Auditor']);
    }

    /**
     * Verifica si el usuario puede acceder al modulo de chat.
     *
     * Solo los roles de Asesor Inmobiliario y Cliente tienen acceso al chat.
     * Este metodo refleja la naturaleza comunicativa de estas roles: el asesor
     * se comunica con clientes prospecto y el cliente interactua con su asesor.
     * Los demas roles (Admin, Auditor, Super Admin) no tienen acceso directo
     * al chat por disenio del negocio.
     *
     * @return bool True si el usuario tiene acceso al chat.
     */
    public function canChat(): bool
    {
        if ($this->hasAnyRole(['Asesor Inmobiliario', 'Cliente'])) {
            return true;
        }

        if ($this->hasPermission('chat access') && $this->hasAnyRole(['Asesor Inmobiliario', 'Cliente'])) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si el usuario puede gestionar citas inmobiliarias.
     *
     * Accesible para usuarios con permisos de citas (ver, crear, editar,
     * eliminar, gestionar) o para Super Admin, Administrador y Asesor
     * Inmobiliario por rol. El Asesor es quien normalmente agenda y
     * gestiona citas con clientes.
     *
     * @return bool True si el usuario puede gestionar citas.
     */
    public function canManageAppointments(): bool
    {
        return $this->hasAnyPermission(['ver citas', 'crear cita', 'editar cita', 'eliminar cita', 'gestionar citas']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador', 'Asesor Inmobiliario']);
    }

    /**
     * Verifica si el usuario puede gestionar servicios inmobiliarios.
     *
     * Accesible para usuarios con permisos de servicios (ver, crear, editar,
     * eliminar) o para Super Admin y Administrador por rol. Los servicios
     * son el catalogo de prestaciones que ofrece la inmobiliaria.
     *
     * @return bool True si el usuario puede gestionar el catalogo de servicios.
     */
    public function canManageServices(): bool
    {
        return $this->hasAnyPermission(['ver servicios', 'crear servicios', 'editar servicios', 'eliminar servicios']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador']);
    }

    /**
     * Verifica si el usuario puede gestionar categorias de propiedades.
     *
     * Accesible para usuarios con permisos de categorias (ver, crear, editar,
     * eliminar) o para Super Admin y Administrador por rol. Las categorias
     * organizan las propiedades por tipo (venta, alquiler, etc.).
     *
     * @return bool True si el usuario puede gestionar categorias.
     */
    public function canManageCategories(): bool
    {
        return $this->hasAnyPermission(['ver categorias', 'crear categoria', 'editar categoria', 'eliminar categoria']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador']);
    }

    /**
     * Verifica si el usuario puede gestionar ubicaciones geograficas.
     *
     * Accesible para usuarios con permisos de paises y estados (ver, crear,
     * eliminar) o para Super Admin y Administrador por rol. Las ubicaciones
     * forman la jerarquia geografica (pais > estado > ciudad) utilizada
     * para clasificar propiedades.
     *
     * @return bool True si el usuario puede gestionar ubicaciones.
     */
    public function canManageLocations(): bool
    {
        return $this->hasAnyPermission(['ver paises', 'crear paises', 'eliminar paises', 'ver estados', 'crear estados', 'eliminar estados']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador']);
    }

    /**
     * Verifica si el usuario puede acceder a la configuracion del sistema.
     *
     * Accesible para usuarios con permisos de configuracion (ver, editar,
     * actualizar configuracion telefonica) o para Super Admin y
     * Administrador por rol. Incluye ajustes generales del sistema,
     * formatos telefonicos y otras preferencias globales.
     *
     * @return bool True si el usuario puede modificar la configuracion.
     */
    public function canManageConfig(): bool
    {
        return $this->hasAnyPermission(['ver configuración', 'editar configuración', 'actualizar configuracion telefonica']) ||
               $this->hasAnyRole(['Super Admin', 'Administrador']);
    }

    /**
     * Verifica si el usuario puede acceder a sus propiedades favoritas.
     *
     * Retorna true siempre. Todos los usuarios autenticados pueden ver
     * sus propiedades marcadas como favoritas, independientemente de su
     * rol o permisos. Es una funcionalidad personal para el usuario.
     *
     * @return bool Siempre retorna true.
     */
    public function canViewFavorites(): bool
    {
        return true;
    }

    /**
     * Verifica si el usuario puede acceder al dashboard principal.
     *
     * Retorna true siempre. Todos los usuarios autenticados pueden acceder
     * a su dashboard, ya que cada rol tiene su propia ruta de dashboard
     * definida en $dashboardMap.
     *
     * @return bool Siempre retorna true.
     */
    public function canAccessDashboard(): bool
    {
        return true;
    }

    /**
     * Retorna un array con el estado de todos los permisos compuestos del sidebar.
     *
     * Agrega el resultado de cada metodo can*() en un array asociativo donde
     * la clave es el nombre del metodo y el valor es un booleano. Se utiliza
     * para inyectar en las vistas Blade un mapa completo de permisos que
     * permite renderizar condicionalmente secciones de la interfaz.
     *
     * @return array<string, bool> Array asociativo con nombres de metodos como claves
     *                             y resultados booleanos como valores.
     */
    public function getSidebarPermissions(): array
    {
        return [
            'canManageUsers' => $this->canManageUsers(),
            'canManageRoles' => $this->canManageRoles(),
            'canManageProperties' => $this->canManageProperties(),
            'canViewLeads' => $this->canViewLeads(),
            'canManageLeads' => $this->canManageLeads(),
            'canViewAudit' => $this->canViewAudit(),
            'canViewReports' => $this->canViewReports(),
            'canChat' => $this->canChat(),
            'canManageAppointments' => $this->canManageAppointments(),
            'canManageServices' => $this->canManageServices(),
            'canManageCategories' => $this->canManageCategories(),
            'canManageLocations' => $this->canManageLocations(),
            'canManageConfig' => $this->canManageConfig(),
            'canViewFavorites' => $this->canViewFavorites(),
            'canAccessDashboard' => $this->canAccessDashboard(),
        ];
    }

    /**
     * Limpia la cache de permisos del usuario actual.
     *
     * Elimina la clave "user_permissions_{id}" del sistema de cache de Laravel.
     * Esto es necesario cuando los permisos o roles de un usuario cambian
     * (por ejemplo, despues de sincronizar permisos via Spatie), para
     * garantizar que la siguiente consulta de permisos lea datos frescos
     * desde la base de datos en lugar de retornar datos obsoletos de cache.
     *
     * Solo ejecuta la limpieza si hay un usuario asignado al servicio.
     *
     * @return void
     */
    public function clearCache(): void
    {
        if ($this->user) {
            Cache::forget('user_permissions_' . $this->user->id);
        }
    }

    // ==========================================
    // METODO DE DEPURACION
    // ==========================================

    /**
     * Metodo de depuracion que retorna informacion detallada sobre un permiso.
     *
     * Util para diagnosticos en entorno de desarrollo o soporte. Consulta
     * multiples fuentes de datos (tabla permissions, roles del usuario,
     * permisos directos, permisos via rol) y retorna un array completo con
     * el estado de cada una. No aplica bypass de Super Admin para mostrar
     * informacion real del usuario.
     *
     * Flujo de datos:
     * 1. Verifica si el permiso existe en la tabla permissions (guard web).
     * 2. Verifica si el usuario tiene el permiso de forma directa.
     * 3. Itera sobre los roles del usuario verificando si alguno otorga
     *    el permiso.
     * 4. Consulta el resultado de Spatie's hasPermissionTo() que combina
     *    ambos metodos anteriores.
     *
     * @param  string $permission Nombre del permiso a depurar.
     * @return array{success: bool, user_id: int|null, user_email: string|null,
     *               user_is_active: bool, roles: array, all_permissions: array,
     *               permission_exists: bool, has_direct_permission: bool,
     *               has_via_role: bool, has_permission_spatie: bool,
     *               is_super_admin: bool, guard_name: string|null}
     */
    public function debug(string $permission): array
    {
        $user = $this->getUser();

        if (!$user) {
            return [
                'success' => false,
                'error' => 'No user found',
                'user_id' => null,
            ];
        }

        $permissionExists = \Spatie\Permission\Models\Permission::where('name', $permission)
            ->where('guard_name', 'web')
            ->exists();

        $hasDirect = $user->hasDirectPermission($permission);
        $hasViaRole = false;
        $roles = $user->roles;

        foreach ($roles as $role) {
            if ($role->hasPermissionTo($permission)) {
                $hasViaRole = true;
                break;
            }
        }

        return [
            'success' => true,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_is_active' => $user->is_active ?? false,
            'roles' => $roles->pluck('name')->toArray(),
            'all_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            'permission_exists' => $permissionExists,
            'has_direct_permission' => $hasDirect,
            'has_via_role' => $hasViaRole,
            'has_permission_spatie' => $user->hasPermissionTo($permission),
            'is_super_admin' => $user->hasRole('Super Admin'),
            'guard_name' => $permissionExists ? \Spatie\Permission\Models\Permission::where('name', $permission)->first()->guard_name : null,
        ];
    }
}
