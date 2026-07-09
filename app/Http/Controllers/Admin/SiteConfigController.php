<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteConfiguration;
use App\Models\Property;
use App\Traits\AuditTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; // ✅ IMPORTAR AUTH

class SiteConfigController extends Controller
{
    use AuditTrait;

    public function __construct()
    {
        $this->middleware('permission:ver configuración');
        $this->middleware('permission:editar configuración')->only(['update', 'reset']);
    }

    /**
     * Mostrar formulario de configuración
     */
    public function index()
    {
        $config = SiteConfiguration::getConfig();

        // Obtener todas las propiedades publicadas con sus vistas
        $allProperties = Property::where('status', 'publicada')
            ->with('primaryImage')
            ->orderBy('views', 'desc')
            ->get();

        // Obtener las propiedades actualmente destacadas
        $featuredIds = $config->featured_properties ?? [];
        if (is_string($featuredIds)) {
            $featuredIds = json_decode($featuredIds, true) ?? [];
        }

        // Propiedades destacadas con sus datos
        $featuredProperties = Property::whereIn('id', $featuredIds)
            ->where('status', 'publicada')
            ->with('primaryImage')
            ->get();

        // Estadísticas rápidas para el panel
        $stats = [
            'total_properties' => Property::where('status', 'publicada')->count(),
            'total_views' => Property::where('status', 'publicada')->sum('views'),
            'most_viewed' => Property::where('status', 'publicada')
                ->orderBy('views', 'desc')
                ->first(),
        ];

        return view('modulos.config.index', compact(
            'config',
            'allProperties',
            'featuredProperties',
            'featuredIds',
            'stats'
        ));
    }

    /**
     * Actualizar configuración
     */
    public function update(Request $request)
    {
        $request->validate([
            'hero_badge' => 'nullable|string|max:100',
            'hero_title_line1' => 'nullable|string|max:255',
            'hero_title_line2' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_images_new.*' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'featured_badge' => 'nullable|string|max:100',
            'featured_title' => 'nullable|string|max:255',
            'featured_properties' => 'nullable|array',
            'featured_properties.*' => 'integer|exists:properties,id',
            'support_whatsapp' => 'nullable|string|max:50',
            'support_instagram' => 'nullable|string|max:100',
            'support_phone' => 'nullable|string|max:50',
            'support_email' => 'nullable|email|max:255',
            'footer_text' => 'nullable|string|max:500',
        ]);

        $config = SiteConfiguration::getConfig();

        // Guardar valores antiguos para auditoría
        $oldValues = $config->toArray();

        // ACTUALIZAR IMÁGENES ELIMINADAS
        $deletedImages = json_decode($request->input('deleted_hero_images', '[]'), true);
        $currentImages = $config->hero_images ?? [];

        if (is_string($currentImages)) {
            $currentImages = json_decode($currentImages, true) ?? [];
        }

        // Eliminar imágenes marcadas
        $remainingImages = [];
        foreach ($currentImages as $index => $image) {
            if (!in_array($index, $deletedImages)) {
                $remainingImages[] = $image;
            }
        }

        // AGREGAR NUEVAS IMÁGENES
        $newImages = [];
        if ($request->hasFile('hero_images_new')) {
            foreach ($request->file('hero_images_new') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('hero-images', 'public');
                    $newImages[] = asset('storage/' . $path);
                }
            }
        }

        // Combinar imágenes existentes y nuevas
        $allImages = array_merge($remainingImages, $newImages);

        // PROCESAR PROPIEDADES DESTACADAS
        $featuredProperties = $request->input('featured_properties', []);

        if (is_string($featuredProperties)) {
            $featuredProperties = explode(',', $featuredProperties);
        }

        $featuredProperties = array_filter($featuredProperties, function($id) {
            return is_numeric($id) && $id > 0;
        });
        $featuredProperties = array_map('intval', $featuredProperties);
        $featuredProperties = array_values($featuredProperties);

        // ACTUALIZAR CONFIGURACIÓN
        $config->update([
            'hero_badge' => $request->hero_badge,
            'hero_title_line1' => $request->hero_title_line1,
            'hero_title_line2' => $request->hero_title_line2,
            'hero_subtitle' => $request->hero_subtitle,
            'hero_images' => $allImages,
            'featured_badge' => $request->featured_badge,
            'featured_title' => $request->featured_title,
            'featured_properties' => $featuredProperties,
            'support_whatsapp' => $request->support_whatsapp,
            'support_instagram' => $request->support_instagram,
            'support_phone' => $request->support_phone,
            'support_email' => $request->support_email,
            'footer_text' => $request->footer_text,
        ]);

        // 🔥 AUDITORÍA - ACTUALIZACIÓN DE CONFIGURACIÓN
        $changes = [];
        $fieldLabels = [
            'hero_badge' => 'badge del hero',
            'hero_title_line1' => 'título hero línea 1',
            'hero_title_line2' => 'título hero línea 2',
            'hero_subtitle' => 'subtítulo hero',
            'hero_images' => 'imágenes del hero',
            'featured_badge' => 'badge destacadas',
            'featured_title' => 'título destacadas',
            'featured_properties' => 'propiedades destacadas',
            'support_whatsapp' => 'WhatsApp',
            'support_instagram' => 'Instagram',
            'support_phone' => 'teléfono',
            'support_email' => 'email de soporte',
            'footer_text' => 'texto del footer',
        ];

        foreach ($config->getChanges() as $key => $value) {
            if ($key !== 'updated_at' && isset($oldValues[$key])) {
                $label = $fieldLabels[$key] ?? $key;
                $oldVal = is_array($oldValues[$key]) ? '[' . implode(', ', $oldValues[$key]) . ']' : ($oldValues[$key] ?? 'vacío');
                $newVal = is_array($value) ? '[' . implode(', ', $value) . ']' : ($value ?? 'vacío');
                $changes[] = "{$label}: '{$oldVal}' → '{$newVal}'";
            }
        }

        if (!empty($changes)) {
            $this->logUpdated($config, $oldValues, $changes);
        } else {
            $this->logAudit('updated', $config, $oldValues, $config->toArray(),
                (Auth::user()?->full_name ?? 'Sistema') . ' ACTUALIZÓ la configuración del sitio (sin cambios visibles)'
            );
        }

        return redirect()->route('admin.config.index')
            ->with('success', 'Configuración actualizada correctamente.');
    }

    /**
     * Eliminar una imagen del hero
     */
    public function deleteImage($index)
    {
        try {
            $config = SiteConfiguration::getConfig();
            $images = $config->hero_images ?? [];

            if (is_string($images)) {
                $images = json_decode($images, true) ?? [];
            }

            if (!isset($images[$index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Imagen no encontrada'
                ], 404);
            }

            $imagePath = $images[$index];
            if (strpos($imagePath, '/storage/') !== false) {
                $path = str_replace('/storage/', '', $imagePath);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            unset($images[$index]);
            $images = array_values($images);

            $config->hero_images = $images;
            $config->save();

            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resetear configuración a valores por defecto
     */
    public function reset()
    {
        $config = SiteConfiguration::getConfig();

        // Guardar valores antiguos para auditoría
        $oldValues = $config->toArray();

        // Eliminar imágenes del storage
        $images = $config->hero_images ?? [];
        if (is_string($images)) {
            $images = json_decode($images, true) ?? [];
        }

        foreach ($images as $image) {
            if (strpos($image, '/storage/') !== false) {
                $path = str_replace('/storage/', '', $image);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        // Restaurar valores por defecto
        $config->update(SiteConfiguration::createDefault()->toArray());

        // 🔥 AUDITORÍA - RESETEO DE CONFIGURACIÓN
        $this->logAudit('updated', $config, $oldValues, $config->toArray(),
            (Auth::user()?->full_name ?? 'Sistema') . ' RESTAURÓ la configuración del sitio a valores por defecto'
        );

        return redirect()->route('admin.config.index')
            ->with('success', 'Configuración restaurada a valores por defecto.');
    }

    /**
     * Buscar propiedades para autocompletar (AJAX)
     */
    public function searchProperties(Request $request)
    {
        $search = $request->get('q', '');

        $properties = Property::where('status', 'publicada')
            ->where(function($query) use ($search) {
                $query->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%");
            })
            ->with('primaryImage')
            ->orderBy('views', 'desc')
            ->limit(20)
            ->get()
            ->map(function($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'location' => $property->location,
                    'price' => $property->formatted_price,
                    'views' => $property->views,
                    'image' => $property->primaryImage?->image_path
                        ? asset('storage/' . $property->primaryImage->image_path)
                        : null,
                ];
            });

        return response()->json($properties);
    }
}
