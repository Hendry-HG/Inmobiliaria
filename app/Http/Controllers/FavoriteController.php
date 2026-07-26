<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    /**
     * Constructor del controlador de favoritos.
     *
     * Aplica el middleware 'auth' a todos los metodos del controlador
     * para garantizar que solo usuarios autenticados puedan gestionar
     * sus propiedades favoritas. Cualquier intento de acceso sin
     * sesion activa sera redirigido a la pagina de login.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra la lista de propiedades favoritas del usuario autenticado.
     *
     * Flujo de la consulta:
     * 1. Obtiene los registros de favoritos del usuario ordenados por
     *    fecha de creacion (mas recientes primero).
     * 2. Carga eager de las relaciones de cada propiedad: imagenes,
     *    estado, ciudad y municipio.
     * 3. Filtra solo propiedades con estado 'publicada' para evitar
     *    mostrar propiedades desactivadas o eliminadas.
     * 4. Transforma la coleccion de favoritos en una coleccion de
     *    propiedades, adjuntando la fecha en que fue marcada como
     *    favorita (favorited_at).
     * 5. Construye un paginador manual con los datos transformados.
     *
     * Admite peticiones AJAX con parametro 'count_only' para devolver
     * unicamente el total de favoritos sin cargar las propiedades.
     *
     * @param  \Illuminate\Http\Request  $request  Peticion HTTP entrante.
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                if ($request->ajax()) {
                    return response()->json(['error' => 'Usuario no autenticado'], 401);
                }
                return redirect()->route('login');
            }

            // Obtener las propiedades favoritas con paginación usando Eloquent
            $favorites = Favorite::where('user_id', $user->id)
                ->with(['property' => function($query) {
                    $query->with(['images', 'stateRelation', 'cityRelation', 'municipalityRelation'])
                          ->where('status', 'publicada');
                }])
                ->orderBy('created_at', 'desc')
                ->paginate(12);

            // Transformar la colección para que sea más fácil de usar en la vista
            $properties = collect();
            foreach ($favorites as $favorite) {
                if ($favorite->property) {
                    $property = $favorite->property;
                    $property->favorited_at = $favorite->created_at;
                    $properties->push($property);
                }
            }

            // Crear un paginador manual con los datos transformados
            $paginatedProperties = new \Illuminate\Pagination\LengthAwarePaginator(
                $properties,
                $favorites->total(),
                $favorites->perPage(),
                $favorites->currentPage(),
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // Si es petición AJAX para el contador
            if ($request->ajax() && $request->has('count_only')) {
                $total = Favorite::where('user_id', $user->id)->count();
                return response()->json([
                    'success' => true,
                    'total' => $total
                ]);
            }

            return view('modulos.favorites.index', compact('paginatedProperties'));

        } catch (\Exception $e) {
            Log::error('Error en FavoriteController@index: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error al cargar los favoritos: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error al cargar los favoritos: ' . $e->getMessage());
        }
    }

    /**
     * Agrega una propiedad a la lista de favoritos del usuario.
     *
     * Flujo de la operacion:
     * 1. Valida que el ID de la propiedad exista en la base de datos.
     * 2. Verifica que la propiedad tenga estado 'publicada'.
     * 3. Comprueba que el usuario no ya tenga esta propiedad en favoritos
     *    para evitar duplicados.
     * 4. Si no existe duplicado, crea el registro en la tabla favorites
     *    y recuenta el total de favoritos del usuario.
     * 5. Responde con JSON para peticiones AJAX/JSON o con redirect
     *    para peticiones normales del navegador.
     *
     * El conteo total se devuelve en la respuesta para que el frontend
     * pueda actualizar el badge del contador de favoritos en tiempo real.
     *
     * @param  \Illuminate\Http\Request  $request  Debe contener 'property_id' requerido.
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'property_id' => 'required|exists:properties,id'
            ]);

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes iniciar sesión para agregar favoritos',
                    'redirect' => route('login')
                ], 401);
            }

            $propertyId = $request->property_id;

            // Verificar si la propiedad existe y está publicada
            $property = Property::where('id', $propertyId)
                ->where('status', 'publicada')
                ->first();

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'La propiedad no existe o no está disponible'
                ], 404);
            }

            // Verificar si ya existe
            $existing = Favorite::where('user_id', $user->id)
                ->where('property_id', $propertyId)
                ->first();

            if (!$existing) {
                Favorite::create([
                    'user_id' => $user->id,
                    'property_id' => $propertyId
                ]);

                $total = Favorite::where('user_id', $user->id)->count();

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => '✓ Propiedad agregada a favoritos',
                        'is_favorite' => true,
                        'total' => $total
                    ]);
                }

                return redirect()->back()->with('success', 'Propiedad agregada a favoritos');
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta propiedad ya está en tus favoritos',
                    'is_favorite' => true
                ], 409);
            }

            return redirect()->back()->with('info', 'Esta propiedad ya está en tus favoritos');

        } catch (\Exception $e) {
            Log::error('Error en FavoriteController@store: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al agregar a favoritos: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al agregar a favoritos');
        }
    }

    /**
     * Elimina una propiedad especifica de la lista de favoritos del usuario.
     *
     * Flujo de la operacion:
     * 1. Si el parametro $propertyId es 'clear-all', delega al metodo
     *    clearAll() para eliminar todos los favoritos.
     * 2. Valida que el ID sea numerico.
     * 3. Busca el registro de favorito que coincida con el usuario y
     *    la propiedad indicada.
     * 4. Si existe, lo elimina y recuenta el total restante de favoritos.
     * 5. Si no existe, informa que la propiedad no estaba en favoritos.
     *
     * El metodo responde tanto con JSON (peticiones AJAX/API) como con
     * redirect (formularios tradicionales), y siempre devuelve el total
     * actualizado para mantener sincronizado el contador del frontend.
     *
     * @param  string|int          $propertyId  ID de la propiedad o 'clear-all'.
     * @param  \Illuminate\Http\Request  $request  Peticion HTTP entrante.
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($propertyId, Request $request)
    {
        try {

            if ($propertyId === 'clear-all') {
                Log::warning('Redirigiendo "clear-all" a clearAll');
                return $this->clearAll($request);
            }

            // Validar que propertyId sea un número
            if (!is_numeric($propertyId)) {
                Log::error('Property ID no es numérico: ' . $propertyId);
                return response()->json([
                    'success' => false,
                    'message' => 'ID de propiedad inválido'
                ], 400);
            }

            $user = Auth::user();

            if (!$user) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes iniciar sesión'
                    ], 401);
                }
                return redirect()->route('login');
            }

            // Verificar si existe el favorito
            $favorite = Favorite::where('user_id', $user->id)
                ->where('property_id', $propertyId)
                ->first();

            if ($favorite) {
                $favorite->delete();

                $total = Favorite::where('user_id', $user->id)->count();

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => '✓ Propiedad eliminada de favoritos',
                        'is_favorite' => false,
                        'total' => $total
                    ]);
                }

                return redirect()->back()->with('success', 'Propiedad eliminada de favoritos');
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La propiedad no estaba en tus favoritos'
                ], 404);
            }

            return redirect()->back()->with('warning', 'La propiedad no estaba en tus favoritos');

        } catch (\Exception $e) {
            Log::error('Error en FavoriteController@destroy: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar de favoritos: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al eliminar de favoritos');
        }
    }

    /**
     * Verifica si una propiedad especifica esta en la lista de favoritos
     * del usuario autenticado.
     *
     * Metodo util para que el frontend determine si mostrar el icono de
     * corazon lleno o vacio en las tarjetas de propiedades. Devuelve
     * unicamente un booleano 'is_favorite' y el estado de autenticacion.
     * Si el usuario no esta autenticado, is_favorite sera false.
     *
     * @param  int                   $propertyId  ID de la propiedad a consultar.
     * @param  \Illuminate\Http\Request  $request       Peticion HTTP entrante.
     * @return \Illuminate\Http\JsonResponse
     */
    public function check($propertyId, Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'is_favorite' => false,
                    'authenticated' => false
                ]);
            }

            $isFavorite = Favorite::where('user_id', $user->id)
                ->where('property_id', $propertyId)
                ->exists();

            return response()->json([
                'is_favorite' => $isFavorite,
                'authenticated' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error en FavoriteController@check: ' . $e->getMessage());

            return response()->json([
                'is_favorite' => false,
                'error' => true
            ], 500);
        }
    }

    /**
     * Obtiene el total de propiedades favoritas del usuario autenticado.
     *
     * Endpoint JSON diseñado para mantener sincronizado el badge del
     * contador de favoritos en la interfaz. Devuelve el conteo total
     * junto con el estado de autenticacion. Si el usuario no esta
     * autenticado, el total es 0.
     *
     * @param  \Illuminate\Http\Request  $request  Peticion HTTP entrante.
     * @return \Illuminate\Http\JsonResponse
     */
    public function count(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'total' => 0,
                    'authenticated' => false
                ]);
            }

            $total = Favorite::where('user_id', $user->id)->count();

            return response()->json([
                'success' => true,
                'total' => $total,
                'authenticated' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error en FavoriteController@count: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'total' => 0,
                'error' => true
            ], 500);
        }
    }

    /**
     * Elimina todos los favoritos del usuario autenticado de una sola vez.
     *
     * Ejecuta un DELETE masivo sobre la tabla favorites filtrando por
     * el usuario actual. Devuelve la cantidad de registros eliminados
     * en el mensaje de respuesta. Este metodo es invocado directamente
     * por destroy() cuando el parametro $propertyId es 'clear-all'.
     *
     * Despues de la eliminacion, redirige a la pagina de favoritos
     * con un mensaje de exito que indica cuantas propiedades se
     * removieron.
     *
     * @param  \Illuminate\Http\Request  $request  Peticion HTTP entrante.
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function clearAll(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes iniciar sesión'
                    ], 401);
                }
                return redirect()->route('login');
            }

            $deleted = Favorite::where('user_id', $user->id)->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Se eliminaron {$deleted} propiedades de favoritos",
                    'total' => 0
                ]);
            }

            return redirect()->route('favorites.index')
                ->with('success', "Se eliminaron {$deleted} propiedades de favoritos");

        } catch (\Exception $e) {
            Log::error('Error en FavoriteController@clearAll: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar los favoritos'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al eliminar los favoritos');
        }
    }
}
