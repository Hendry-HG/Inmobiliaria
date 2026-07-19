<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceGallery;
use App\Models\User;
use App\Models\SiteConfiguration;
use App\Models\Country;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver servicios')->only(['index', 'show']);
        $this->middleware('permission:crear servicios')->only(['create', 'store']);
        $this->middleware('permission:editar servicios')->only(['edit', 'update']);
        $this->middleware('permission:eliminar servicios')->only(['destroy']);
    }

    /**
     * Listar todos los servicios
     */
    public function index()
    {
        $services = Service::with('gallery')
            ->orderBy('order')
            ->get();

        $asesores = User::role('Asesor Inmobiliario')
            ->where('is_active', true)
            ->get();

        // Datos para los filtros de ubicación (si los necesitas)
        $countries = Country::orderBy('name')->get();
        $states = collect();
        $municipalities = collect();
        $parishes = collect();
        $cities = collect();

        return view('modulos.servicios.index', compact(
            'services',
            'asesores',
            'countries',
            'states',
            'municipalities',
            'parishes',
            'cities'
        ));
    }

    /**
     * Mostrar formulario para crear un nuevo servicio
     */
    public function create()
    {
        return view('modulos.servicios.create');
    }

    /**
     * Guardar un nuevo servicio
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'badge' => 'nullable|string|max:50',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'external_url' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $data = $request->except(['image', 'gallery_images']);

        // Generar slug
        $data['slug'] = \Illuminate\Support\Str::slug($request->title);

        // Procesar imagen principal
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'public');
            $data['image'] = $path;
        }

        // Crear el servicio
        $service = Service::create($data);

        // Procesar galería de imágenes
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $file) {
                $path = $file->store('services/gallery', 'public');
                ServiceGallery::create([
                    'service_id' => $service->id,
                    'image_path' => $path,
                    'order' => $index,
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    /**
     * Mostrar un servicio específico
     */
    public function show($id)
    {
        $service = Service::with('gallery')->findOrFail($id);
        return view('modulos.servicios.show', compact('service'));
    }

    /**
     * Mostrar formulario para editar un servicio
     */
    public function edit($id)
    {
        $service = Service::with('gallery')->findOrFail($id);
        return view('modulos.servicios.edit', compact('service'));
    }

    /**
     * Actualizar un servicio
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'badge' => 'nullable|string|max:50',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'external_url' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $data = $request->except(['image', 'gallery_images', 'deleted_images']);

        // Procesar imagen principal
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($service->image && Storage::disk('public')->exists($service->image)) {
                Storage::disk('public')->delete($service->image);
            }
            $path = $request->file('image')->store('services', 'public');
            $data['image'] = $path;
        }

        // Actualizar el servicio
        $service->update($data);

        // Procesar eliminación de imágenes de la galería
        $deletedImages = json_decode($request->input('deleted_images', '[]'), true);
        if (!empty($deletedImages)) {
            $imagesToDelete = ServiceGallery::whereIn('id', $deletedImages)->get();
            foreach ($imagesToDelete as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }
        }

        // Procesar nuevas imágenes de la galería
        if ($request->hasFile('gallery_images')) {
            $currentMaxOrder = $service->gallery()->max('order') ?? 0;
            foreach ($request->file('gallery_images') as $index => $file) {
                $path = $file->store('services/gallery', 'public');
                ServiceGallery::create([
                    'service_id' => $service->id,
                    'image_path' => $path,
                    'order' => $currentMaxOrder + $index + 1,
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    /**
     * Eliminar un servicio
     */
    public function destroy($id)
    {
        $service = Service::with('gallery')->findOrFail($id);

        // Eliminar imagen principal
        if ($service->image && Storage::disk('public')->exists($service->image)) {
            Storage::disk('public')->delete($service->image);
        }

        // Eliminar imágenes de la galería
        foreach ($service->gallery as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        $service->delete();

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio eliminado exitosamente.');
    }

    /**
     * Reordenar servicios (AJAX)
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:services,id',
        ]);

        foreach ($request->order as $index => $id) {
            Service::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Obtener la vista pública de servicios (para el frontend)
     */
    public function publicIndex()
    {
        $services = Service::with('gallery')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $asesores = User::role('Asesor Inmobiliario')
            ->with(['properties' => function($query) {
                $query->where('status', 'publicada');
            }])
            ->where('is_active', true)
            ->get();

        $galleryImages = collect();
        $config = SiteConfiguration::getConfig();

        return view('modulos.servicios.public', compact('services', 'asesores', 'galleryImages', 'config'));
    }
}
