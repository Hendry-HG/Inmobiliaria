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
     * Constructor - Asegura que el usuario esté autenticado
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra la lista de propiedades favoritas del usuario
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
     * Agrega una propiedad a favoritos
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
     * Elimina una propiedad de favoritos
     */
    public function destroy($propertyId, Request $request)
    {
        try {
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
                    'message' => 'Error al eliminar de favoritos'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al eliminar de favoritos');
        }
    }

    /**
     * Verifica si una propiedad está en favoritos del usuario
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
     * Obtiene el total de favoritos del usuario
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
     * Elimina todos los favoritos del usuario
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
