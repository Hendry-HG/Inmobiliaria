<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Category;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use App\Models\User;
use App\Traits\AuditTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    use AuditTrait;

    public function __construct()
    {
        $this->middleware('auth')->except(['catalog', 'showPublic', 'countProperties', 'showById']);
        $this->middleware('throttle:60,1')->only(['catalog', 'showPublic']);
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $query = Property::with([
            'user', 'category', 'primaryImage',
            'countryRelation', 'stateRelation', 'municipalityRelation',
            'parishRelation', 'cityRelation'
        ]);

        if ($user->hasRole('Asesor Inmobiliario')) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', $search)
                  ->orWhere('description', 'LIKE', $search)
                  ->orWhere('address', 'LIKE', $search);
            });
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('municipality_id')) {
            $query->where('municipality_id', $request->municipality_id);
        }

        if ($request->filled('parish_id')) {
            $query->where('parish_id', $request->parish_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if (($user->hasRole('Super Admin') || $user->hasRole('Administrador')) && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $properties = $query->latest()->paginate(10)->withQueryString();

        $categories = Category::all();
        $countries = Cache::remember('countries_list', 86400, function() {
            return Country::orderBy('name')->get();
        });

        $states = collect();
        $municipalities = collect();
        $parishes = collect();
        $cities = collect();

        if ($request->filled('country_id')) {
            $states = Cache::remember("states_country_{$request->country_id}", 86400, function() use ($request) {
                return State::where('country_id', $request->country_id)->orderBy('name')->get();
            });
        }

        if ($request->filled('state_id')) {
            $municipalities = Cache::remember("municipalities_state_{$request->state_id}", 86400, function() use ($request) {
                return Municipality::where('state_id', $request->state_id)->orderBy('name')->get();
            });
        }

        if ($request->filled('municipality_id')) {
            $parishes = Cache::remember("parishes_municipality_{$request->municipality_id}", 86400, function() use ($request) {
                return Parish::where('municipality_id', $request->municipality_id)->orderBy('name')->get();
            });
        }

        if ($request->filled('parish_id')) {
            $cities = Cache::remember("cities_parish_{$request->parish_id}", 86400, function() use ($request) {
                return City::where('parish_id', $request->parish_id)->orderBy('name')->get();
            });
        }

        $asesores = collect();
        if ($user->hasRole('Super Admin') || $user->hasRole('Administrador')) {
            $asesores = User::role('Asesor Inmobiliario')->orderBy('name')->get();
        }

        $isAdmin = $user->hasRole('Super Admin') || $user->hasRole('Administrador');
        $isAsesor = $user->hasRole('Asesor Inmobiliario');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('modulos.propiedades._rows', compact(
                    'properties', 'isAdmin', 'isAsesor'
                ))->render(),
                'pagination' => $properties->links()->toHtml(),
                'total' => $properties->total()
            ]);
        }

        return view('modulos.propiedades.index', compact(
            'properties', 'categories', 'countries', 'states',
            'municipalities', 'parishes', 'cities', 'asesores',
            'isAdmin', 'isAsesor'
        ));
    }

    public function catalog(Request $request)
    {
        $venezuelaId = 1;

        $query = Property::with([
            'user', 'category', 'primaryImage',
            'countryRelation', 'stateRelation', 'municipalityRelation', 'cityRelation'
        ])->where('status', 'publicada')->where('country_id', $venezuelaId);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', $search)
                  ->orWhere('description', 'LIKE', $search)
                  ->orWhere('address', 'LIKE', $search);
            });
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('municipality_id')) {
            $query->where('municipality_id', $request->municipality_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        if ($request->filled('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }

        if ($request->filled('parking_spaces')) {
            $query->where('parking_spaces', '>=', $request->parking_spaces);
        }

        if ($request->filled('min_area')) {
            $query->where('area', '>=', $request->min_area);
        }

        if ($request->filled('max_area')) {
            $query->where('area', '<=', $request->max_area);
        }

        $orderBy = $request->get('order_by', 'latest');
        switch ($orderBy) {
            case 'price_asc': $query->orderBy('price', 'asc'); break;
            case 'price_desc': $query->orderBy('price', 'desc'); break;
            case 'oldest': $query->oldest(); break;
            default: $query->latest(); break;
        }

        $properties = $query->paginate(12)->withQueryString();

        $categories = Category::orderBy('name')->get();

        $states = State::where('country_id', $venezuelaId)
            ->withCount(['properties' => function($q) {
                $q->where('status', 'publicada');
            }])
            ->orderBy('name')
            ->get();

        $municipalities = collect();
        if ($request->filled('state_id')) {
            $municipalities = Municipality::where('state_id', $request->state_id)
                ->withCount(['properties' => function($q) {
                    $q->where('status', 'publicada');
                }])
                ->orderBy('name')
                ->get();
        }

        $cities = collect();
        if ($request->filled('municipality_id')) {
            $parishIds = Parish::where('municipality_id', $request->municipality_id)->pluck('id');

            if ($parishIds->isNotEmpty()) {
                $cities = City::whereIn('parish_id', $parishIds)
                    ->withCount(['properties' => function($q) {
                        $q->where('status', 'publicada');
                    }])
                    ->orderBy('name')
                    ->get();
            }
        }

        $totalProperties = Property::where('status', 'publicada')
            ->where('country_id', $venezuelaId)
            ->count();

        return view('modulos.catalogo.index', compact(
            'properties', 'categories', 'states',
            'municipalities', 'cities', 'totalProperties'
        ));
    }

    public function showPublic(Property $property)
    {
        if ($property->status !== 'publicada') {
            abort(404);
        }

        $property->load([
            'images', 'user', 'category',
            'countryRelation', 'stateRelation',
            'municipalityRelation', 'parishRelation', 'cityRelation'
        ]);

        $property->incrementViews();

        $similarProperties = Property::where('status', 'publicada')
            ->where('id', '!=', $property->id)
            ->where(function($q) use ($property) {
                $q->where('category_id', $property->category_id)
                  ->orWhere('city_id', $property->city_id)
                  ->orWhere('state_id', $property->state_id);
            })
            ->with('primaryImage')
            ->latest()
            ->limit(4)
            ->get();

        return view('modulos.catalogo.show', compact('property', 'similarProperties'));
    }

    public function showById($id)
    {
        $property = Property::with([
            'images', 'user', 'category',
            'stateRelation', 'municipalityRelation', 'cityRelation'
        ])->findOrFail($id);

        if ($property->status !== 'publicada') {
            abort(404, 'La propiedad no está disponible públicamente.');
        }

        $property->incrementViews();

        return view('modulos.catalogo.show', compact('property'));
    }

    public function countProperties(Request $request)
    {
        $query = Property::where('status', 'publicada');

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        return response()->json(['count' => $query->count()]);
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasRole('Super Admin') && !$user->hasRole('Administrador') && !$user->hasRole('Asesor Inmobiliario')) {
            abort(403, 'No tienes permiso para crear propiedades.');
        }

        $categories = Category::all();
        $countries = Cache::remember('countries_list', 86400, function() {
            return Country::orderBy('name')->get();
        });

        $states = collect();
        $municipalities = collect();
        $parishes = collect();
        $cities = collect();

        $isAdmin = $user->hasRole('Super Admin') || $user->hasRole('Administrador');
        $isAsesor = $user->hasRole('Asesor Inmobiliario');

        return view('modulos.propiedades.create', compact(
            'categories', 'countries', 'states',
            'municipalities', 'parishes', 'cities',
            'isAdmin', 'isAsesor'
        ));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasRole('Super Admin') && !$user->hasRole('Administrador') && !$user->hasRole('Asesor Inmobiliario')) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => ['required', Rule::in(['venta', 'alquiler', 'venta/alquiler'])],
            'status' => ['required', Rule::in(['borrador', 'pendiente', 'publicada', 'vendida', 'alquilada', 'inactiva'])],
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'municipality_id' => 'required|exists:municipalities,id',
            'parish_id' => 'nullable|exists:parishes,id',
            'city_id' => 'nullable|exists:cities,id',
            'address' => 'nullable|string|max:500',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'parking_spaces' => 'nullable|integer|min:0',
            'area' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'required|array|min:1|max:15',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'images.required' => 'Debes subir al menos 1 imagen de la propiedad.',
            'images.min' => 'Debes subir al menos 1 imagen de la propiedad.',
            'images.max' => 'Máximo 15 imágenes permitidas.',
            'images.*.mimes' => 'Solo se permiten imágenes en formato JPG o PNG.',
            'images.*.max' => 'Cada imagen debe pesar menos de 2MB.',
        ]);

        try {

            $data = $request->except(['images', 'deleted_images', 'is_featured', 'featured_until']);
            $data['user_id'] = $user->id;
            $data['features'] = $request->input('features', []);
            $data['location'] = $this->buildLocationString($data);
            $data['is_featured'] = false; // Por defecto, nunca destacada al crear

            $data['description'] = strip_tags($data['description'], '<p><br><strong><em><u><ul><ol><li><h1><h2><h3><h4>');

            $property = Property::create($data);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('properties', 'public');

                    $thumbnailPath = null;
                    try {
                        $image = \Intervention\Image\Facades\Image::make($file);
                        $thumbnail = $image->fit(300, 200);
                        $thumbnailName = 'thumb_' . uniqid() . '.jpg';
                        $thumbnailPath = 'properties/thumbnails/' . $thumbnailName;
                        Storage::disk('public')->put($thumbnailPath, (string) $thumbnail->encode('jpg', 80));
                    } catch (\Exception $e) {
                        Log::warning('Error generando thumbnail: ' . $e->getMessage());
                    }

                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image_path' => $path,
                        'thumbnail_path' => $thumbnailPath,
                        'is_primary' => ($index === 0),
                        'order' => $index,
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            $this->logCreated($property, (Auth::user()?->full_name ?? 'Sistema') . ' CREÓ la propiedad "' . $property->title . '"');

            $notifyRoles = ['Super Admin', 'Administrador', 'Auditor'];
            $usersToNotify = User::role($notifyRoles)->get();

            foreach ($usersToNotify as $u) {
                if ($u->id === $user->id) continue;
                $u->createNotification(
                    'Nueva Propiedad Creada',
                    "El asesor {$user->name} ha creado una nueva propiedad: {$property->title}",
                    'info',
                    route('asesor.properties.show', $property->id),
                    ['property_id' => $property->id]
                );
            }

            $redirectRoute = $user->hasRole('Asesor Inmobiliario')
                ? route('asesor.properties.index')
                : route('admin.properties.index');

            return redirect($redirectRoute)->with('success', 'Propiedad creada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al crear propiedad', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Hubo un problema al crear la propiedad.']);
        }
    }

    public function edit(Property $property)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole('Asesor Inmobiliario') && $property->user_id !== $user->id) {
            abort(403, 'No tienes permiso para editar esta propiedad.');
        }

        if (!$user->hasRole('Super Admin') && !$user->hasRole('Administrador') && !$user->hasRole('Asesor Inmobiliario')) {
            abort(403, 'No tienes permiso para editar propiedades.');
        }

        $property->load(['images', 'countryRelation', 'stateRelation', 'municipalityRelation', 'parishRelation', 'cityRelation']);

        $categories = Category::all();
        $countries = Cache::remember('countries_list', 86400, function() {
            return Country::orderBy('name')->get();
        });

        $states = State::where('country_id', $property->country_id)->orderBy('name')->get();
        $municipalities = Municipality::where('state_id', $property->state_id)->orderBy('name')->get();
        $parishes = Parish::where('municipality_id', $property->municipality_id)->orderBy('name')->get();
        $cities = City::where('parish_id', $property->parish_id)->orderBy('name')->get();

        $isAdmin = $user->hasRole('Super Admin') || $user->hasRole('Administrador');
        $isAsesor = $user->hasRole('Asesor Inmobiliario');

        return view('modulos.propiedades.create', compact(
            'property', 'categories', 'countries', 'states',
            'municipalities', 'parishes', 'cities',
            'isAdmin', 'isAsesor'
        ));
    }

    public function update(Request $request, Property $property)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole('Asesor Inmobiliario') && $property->user_id !== $user->id) {
            abort(403, 'No tienes permiso para actualizar esta propiedad.');
        }

        if (!$user->hasRole('Super Admin') && !$user->hasRole('Administrador') && !$user->hasRole('Asesor Inmobiliario')) {
            abort(403, 'No tienes permiso para actualizar propiedades.');
        }

        $deletedImages = [];
        if ($request->filled('deleted_images')) {
            $ids = explode(',', $request->input('deleted_images'));
            $deletedImages = $property->images()
                ->whereIn('id', $ids)
                ->pluck('id')
                ->toArray();
        }

        $existingImagesCount = $property->images()->whereNotIn('id', $deletedImages)->count();
        $newImagesCount = $request->hasFile('images') ? count($request->file('images')) : 0;
        $totalImages = $existingImagesCount + $newImagesCount;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'type' => ['required', Rule::in(['venta', 'alquiler', 'venta/alquiler'])],
            'status' => ['required', Rule::in(['borrador', 'pendiente', 'publicada', 'vendida', 'alquilada', 'inactiva'])],
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'municipality_id' => 'required|exists:municipalities,id',
            'parish_id' => 'nullable|exists:parishes,id',
            'city_id' => 'nullable|exists:cities,id',
            'address' => 'nullable|string|max:500',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'parking_spaces' => 'nullable|integer|min:0',
            'area' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'images' => $totalImages > 15 ? 'max:0' : 'nullable|array|max:15',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'images.max' => 'Máximo 15 imágenes en total.',
            'images.*.mimes' => 'Solo se permiten imágenes en formato JPG o PNG.',
            'images.*.max' => 'Cada imagen debe pesar menos de 2MB.',
        ]);

        if ($totalImages < 1) {
            return back()->withErrors(['images' => 'La propiedad debe tener al menos 1 imagen.'])->withInput();
        }

        try {
            $oldValues = $property->toArray();


            $data = $request->except(['images', 'deleted_images', 'is_featured', 'featured_until']);
            $data['features'] = $request->input('features', []);
            $data['location'] = $this->buildLocationString($data);
            // is_featured NO se actualiza aquí, solo lo hace SiteConfiguration

            $data['description'] = strip_tags($data['description'], '<p><br><strong><em><u><ul><ol><li><h1><h2><h3><h4>');

            $property->update($data);

            if (!empty($deletedImages)) {
                $imagesToDelete = $property->images()->whereIn('id', $deletedImages)->get();
                foreach ($imagesToDelete as $image) {
                    if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                    if ($image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
                        Storage::disk('public')->delete($image->thumbnail_path);
                    }
                    $image->delete();
                }
            }

            if ($request->hasFile('images')) {
                $hasPrimary = $property->images()->where('is_primary', true)->exists();
                $currentMaxOrder = $property->images()->max('order') ?? 0;

                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('properties', 'public');

                    $thumbnailPath = null;
                    try {
                        $image = \Intervention\Image\Facades\Image::make($file);
                        $thumbnail = $image->fit(300, 200);
                        $thumbnailName = 'thumb_' . uniqid() . '.jpg';
                        $thumbnailPath = 'properties/thumbnails/' . $thumbnailName;
                        Storage::disk('public')->put($thumbnailPath, (string) $thumbnail->encode('jpg', 80));
                    } catch (\Exception $e) {
                        Log::warning('Error generando thumbnail: ' . $e->getMessage());
                    }

                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image_path' => $path,
                        'thumbnail_path' => $thumbnailPath,
                        'is_primary' => !$hasPrimary && $index === 0 && $existingImagesCount === 0,
                        'order' => $currentMaxOrder + $index + 1,
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            $changes = [];
            $fieldLabels = [
                'title' => 'título', 'description' => 'descripción',
                'price' => 'precio', 'status' => 'estado', 'type' => 'tipo',
                'location' => 'ubicación', 'address' => 'dirección',
                'bedrooms' => 'habitaciones', 'bathrooms' => 'baños',
                'parking_spaces' => 'estacionamientos', 'area' => 'área',
                'land_area' => 'área de terreno', 'floors' => 'pisos',
                'year_built' => 'año de construcción',

            ];

            foreach ($data as $key => $value) {
                if (isset($oldValues[$key]) && $oldValues[$key] != $value && $key !== 'updated_at') {
                    $label = $fieldLabels[$key] ?? $key;
                    $changes[] = "{$label}: '" . ($oldValues[$key] ?? 'vacío') . "' → '" . ($value ?? 'vacío') . "'";
                }
            }

            if (!empty($changes)) {
                $this->logUpdated($property, $oldValues, $changes);
            }

            $auditors = User::role('Auditor')->get();
            foreach ($auditors as $auditor) {
                if ($auditor->id === $user->id) continue;
                $auditor->createNotification(
                    'Propiedad Actualizada',
                    "La propiedad {$property->title} ha sido actualizada por {$user->name}",
                    'warning',
                    route('asesor.properties.show', $property->id),
                    ['property_id' => $property->id]
                );
            }

            $redirectRoute = $user->hasRole('Asesor Inmobiliario')
                ? route('asesor.properties.index')
                : route('admin.properties.index');

            return redirect($redirectRoute)->with('success', 'Propiedad actualizada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar propiedad', [
                'property_id' => $property->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Hubo un problema al actualizar la propiedad.']);
        }
    }

    public function show(Property $property)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole('Asesor Inmobiliario') && $property->user_id !== $user->id) {
            abort(403, 'No tienes permiso para ver esta propiedad.');
        }

        $property->load([
            'images', 'user', 'category',
            'countryRelation', 'stateRelation',
            'municipalityRelation', 'parishRelation', 'cityRelation'
        ]);

        $property->incrementViews();

        $isAdmin = $user->hasRole('Super Admin') || $user->hasRole('Administrador');
        $isAsesor = $user->hasRole('Asesor Inmobiliario');

        return view('modulos.propiedades.show', compact('property', 'isAdmin', 'isAsesor'));
    }

    public function destroy(Property $property)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole('Asesor Inmobiliario') && $property->user_id !== $user->id) {
            abort(403, 'No tienes permiso para eliminar esta propiedad.');
        }

        if (!$user->hasRole('Super Admin') && !$user->hasRole('Administrador') && !$user->hasRole('Asesor Inmobiliario')) {
            abort(403, 'No tienes permiso para eliminar propiedades.');
        }

        try {
            $this->logDeleted($property, (Auth::user()?->full_name ?? 'Sistema') . ' ELIMINÓ la propiedad "' . $property->title . '"');

            foreach ($property->images as $image) {
                if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                if ($image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
                    Storage::disk('public')->delete($image->thumbnail_path);
                }
                $image->delete();
            }

            $property->delete();

            $route = $user->hasRole('Asesor Inmobiliario')
                ? route('asesor.properties.index')
                : route('admin.properties.index');

            return redirect($route)->with('success', 'Propiedad eliminada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar propiedad', [
                'property_id' => $property->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->withErrors(['error' => 'Hubo un problema al eliminar la propiedad.']);
        }
    }

    private function buildLocationString($data)
    {
        $parts = [];

        if (!empty($data['country_id'])) {
            $country = Country::find($data['country_id']);
            if ($country) $parts[] = $country->name;
        }

        if (!empty($data['state_id'])) {
            $state = State::find($data['state_id']);
            if ($state) $parts[] = $state->name;
        }

        if (!empty($data['municipality_id'])) {
            $municipality = Municipality::find($data['municipality_id']);
            if ($municipality) $parts[] = $municipality->name;
        }

        if (!empty($data['parish_id'])) {
            $parish = Parish::find($data['parish_id']);
            if ($parish) $parts[] = $parish->name;
        }

        if (!empty($data['city_id'])) {
            $city = City::find($data['city_id']);
            if ($city) $parts[] = $city->name;
        }

        if (!empty($data['address'])) {
            $parts[] = $data['address'];
        }

        return implode(', ', $parts);
    }

    public function getStates($countryId)
    {
        return Cache::remember("states_country_{$countryId}", 86400, function() use ($countryId) {
            return State::where('country_id', $countryId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    public function getMunicipalities($stateId)
    {
        return Cache::remember("municipalities_state_{$stateId}", 86400, function() use ($stateId) {
            return Municipality::where('state_id', $stateId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    public function getParishes($municipalityId)
    {
        return Cache::remember("parishes_municipality_{$municipalityId}", 86400, function() use ($municipalityId) {
            return Parish::where('municipality_id', $municipalityId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    public function getCities($parishId)
    {
        return Cache::remember("cities_parish_{$parishId}", 86400, function() use ($parishId) {
            return City::where('parish_id', $parishId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }
}
