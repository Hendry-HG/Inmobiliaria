<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\AuditTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/**
 * Controlador para la gestion administrativa de categorias de propiedades.
 *
 * Este controlador maneja las operaciones CRUD sobre las categorias utilizadas
 * para clasificar las propiedades inmobiliarias (por ejemplo: apartamentos,
 * casas, locales comerciales, etc.). Incluye busqueda, filtrado por estado,
 * paginacion y limpieza de cache despues de cada operacion de escritura.
 *
 * Cada operacion de creacion, actualizacion y eliminacion queda registrada
 * a traves del AuditTrait para mantener un historial de cambios realizados
 * por los usuarios autenticados.
 *
 * Se aplica middleware de autenticacion y rol (Super Admin o Administrador)
 * a todas las acciones del controlador.
 */
class CategoryController extends Controller
{
    use AuditTrait;

    /**
     * Constructor del controlador.
     *
     * Aplica middlewares de autenticacion y verificacion de rol a todas las
     * rutas del controlador. Solo los usuarios con rol 'Super Admin' o
     * 'Administrador' pueden acceder a las acciones de este controlador.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:Super Admin|Administrador']);
    }

    /**
     * Lista todas las categorias con opciones de busqueda y filtrado.
     *
     * Este metodo soporta los siguientes parametros de consulta:
     * - search (string): Busca categorias cuyo nombre o descripcion contengan
     *   el texto proporcionado, utilizando el operador LIKE con comodines.
     * - status (string): Filtra por estado de la categoria. 'active' retorna
     *   solo categorias activas, cualquier otro valor retorna las inactivas.
     *
     * Las categorias se cargan con el conteo de propiedades asociadas
     * (withCount('properties')) y se ordenan primero por campo 'order'
     * y luego por nombre. La consulta se pagina en grupos de 15 registros
     * manteniendo los parametros de consulta en la paginacion.
     *
     * @param  \Illuminate\Http\Request  $request Solicitud HTTP con los parametros de busqueda y filtrado opcionales.
     * @return \Illuminate\View\View Retorna la vista 'modulos.categoria.index' con las categorias paginadas.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('properties');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', $search)
                  ->orWhere('description', 'LIKE', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->orderBy('order')
                           ->orderBy('name')
                           ->paginate(15)
                           ->withQueryString();

        return view('modulos.categoria.index', compact('categories'));
    }

    /**
     * Muestra el formulario para crear una nueva categoria.
     *
     * Renderiza el formulario comun de creacion/edicion sin datos precargados.
     *
     * @return \Illuminate\View\View Retorna la vista 'modulos.categoria.form'.
     */
    public function create()
    {
        return view('modulos.categoria.form');
    }

    /**
     * Almacena una nueva categoria en la base de datos.
     *
     * Este metodo realiza las siguientes operaciones:
     * 1. Valida los datos del formulario: nombre (requerido y unico), descripcion,
     *    icono, estado activo y orden de visualizacion.
     * 2. Genera un slug automaticamente a partir del nombre de la categoria.
     * 3. Crea el registro de la categoria en la tabla 'categories'.
     * 4. Limpia la clave 'categories_list' del cache para que la siguiente
     *    consulta obtenga los datos actualizados.
     * 5. Registra la accion de auditoria indicando que usuario creo la categoria.
     *
     * En caso de error, retorna a la pagina anterior con los datos ingresados
     * y un mensaje descriptivo del problema.
     *
     * @param  \Illuminate\Http\Request  $request Solicitud HTTP con los datos del formulario.
     * @return \Illuminate\Http\RedirectResponse Redirige al indice de categorias con mensaje de exito o error.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        try {
            $category = Category::create([
                'name' => $validated['name'],
                'slug' => str($validated['name'])->slug(),
                'description' => $validated['description'] ?? null,
                'icon' => $validated['icon'] ?? null,
                'is_active' => $request->has('is_active'),
                'order' => $validated['order'] ?? 0,
            ]);

            // Limpiar cache
            Cache::forget('categories_list');

            $this->logCreated($category, Auth::user()->full_name . ' creó la categoría \'' . $category->name . '\'');

            return redirect()->route('admin.categories.index')
                ->with('success', 'Categoría creada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Hubo un problema al crear la categoría: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el formulario para editar una categoria existente.
     *
     * Utiliza la resolución de ruta model binding para obtener la categoria
     * directamente por su ID y pasarla a la vista de formulario.
     *
     * @param  \App\Models\Category  $category Instancia de la categoria a editar (resuelta automaticamente por Laravel).
     * @return \Illuminate\View\View Retorna la vista 'modulos.categoria.form' con la categoria precargada.
     */
    public function edit(Category $category)
    {
        return view('modulos.categoria.form', compact('category'));
    }

    /**
     * Actualiza una categoria existente en la base de datos.
     *
     * Este metodo realiza las siguientes operaciones:
     * 1. Valida los datos del formulario: nombre (requerido, unico excepto la
     *    categoria actual utilizando Rule::unique con ignore), descripcion, icono,
     *    estado activo y orden de visualizacion.
     * 2. Captura los valores anteriores de la categoria antes de la actualizacion
     *    para el registro de auditoria.
     * 3. Actualiza el registro de la categoria con los nuevos datos, regenerando
     *    el slug a partir del nombre actualizado.
     * 4. Limpia la clave 'categories_list' del cache para que los cambios
     *    se reflejen inmediatamente en las consultas posteriores.
     * 5. Registra la accion de auditoria con los valores anteriores y los
     *    cambios realizados ($category->changes()).
     *
     * En caso de error, retorna a la pagina anterior con los datos ingresados
     * y un mensaje descriptivo del problema.
     *
     * @param  \Illuminate\Http\Request  $request Solicitud HTTP con los datos actualizados.
     * @param  \App\Models\Category  $category Instancia de la categoria a actualizar (resuelta automaticamente por Laravel).
     * @return \Illuminate\Http\RedirectResponse Redirige al indice de categorias con mensaje de exito o error.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        try {
            $oldValues = $category->toArray();
            $category->update([
                'name' => $validated['name'],
                'slug' => str($validated['name'])->slug(),
                'description' => $validated['description'] ?? null,
                'icon' => $validated['icon'] ?? null,
                'is_active' => $request->has('is_active'),
                'order' => $validated['order'] ?? 0,
            ]);

            // Limpiar cache
            Cache::forget('categories_list');

            $this->logUpdated($category, $oldValues, $category->changes(), Auth::user()->full_name . ' actualizó la categoría \'' . $category->name . '\'');

            return redirect()->route('admin.categories.index')
                ->with('success', 'Categoría actualizada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Hubo un problema al actualizar la categoría.']);
        }
    }

    /**
     * Elimina una categoria de la base de datos.
     *
     * Este metodo realiza las siguientes operaciones:
     * 1. Verifica que la categoria no tenga propiedades asociadas. Si las tiene,
     *    retorna un error indicando la cantidad de propiedades que impiden la
     *    eliminacion (proteccion de integridad referencial).
     * 2. Registra la accion de auditoria antes de eliminar el registro.
     * 3. Elimina el registro de la categoria de la base de datos.
     * 4. Limpia la clave 'categories_list' del cache para que la eliminacion
     *    se refleje inmediatamente en las consultas posteriores.
     *
     * En caso de error durante la eliminacion, retorna a la pagina anterior
     * con un mensaje descriptivo del problema.
     *
     * @param  \App\Models\Category  $category Instancia de la categoria a eliminar (resuelta automaticamente por Laravel).
     * @return \Illuminate\Http\RedirectResponse Redirige al indice de categorias con mensaje de exito o error.
     */
    public function destroy(Category $category)
    {
        try {
            // Verificar si tiene propiedades asociadas
            if ($category->properties()->count() > 0) {
                return redirect()->back()->withErrors([
                    'error' => 'No se puede eliminar la categoría porque tiene ' . $category->properties()->count() . ' propiedades asociadas.'
                ]);
            }

            $this->logDeleted($category, Auth::user()->full_name . ' eliminó la categoría \'' . $category->name . '\'');

            $category->delete();

            // Limpiar cache
            Cache::forget('categories_list');

            return redirect()->route('admin.categories.index')
                ->with('success', 'Categoría eliminada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Hubo un problema al eliminar la categoría.'
            ]);
        }
    }

    /**
     * Cambia el estado de activacion de una categoria (activo/inactivo).
     *
     * Este metodo alterna el valor del campo 'is_active' de la categoria:
     * si esta activa la desactiva, y si esta inactiva la activa. Despues
     * de cambiar el estado, limpia la clave 'categories_list' del cache
     * para que el cambio se refleje inmediatamente en las consultas.
     *
     * A diferencia de los metodos CRUD estandar, este metodo no registra
     * accion de auditoria ni recibe datos adicionales del formulario,
     * solo alterna el estado booleano existente.
     *
     * @param  \App\Models\Category  $category Instancia de la categoria cuyo estado se va a cambiar (resuelta automaticamente por Laravel).
     * @return \Illuminate\Http\RedirectResponse Redirige a la pagina anterior con un mensaje de confirmacion o error.
     */
    public function toggleStatus(Category $category)
    {
        try {
            $category->is_active = !$category->is_active;
            $category->save();

            // Limpiar cache
            Cache::forget('categories_list');

            return redirect()->back()
                ->with('success', 'Estado de la categoría actualizado.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Hubo un problema al cambiar el estado.'
            ]);
        }
    }
}
