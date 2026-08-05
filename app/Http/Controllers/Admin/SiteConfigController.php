<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteConfiguration;
use App\Models\Property;
use App\Traits\AuditTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador de Configuración del Sitio
 *
 * Gestiona la configuración general del sitio web inmobiliario, incluyendo:
 * - Sección Hero (badge, títulos, subtítulo e imágenes del carrusel principal)
 * - Propiedades destacadas en la página de inicio
 * - Información de contacto y soporte (WhatsApp, Instagram, teléfono, email)
 * - Texto del pie de página (footer)
 * - Estadísticas generales del sitio
 *
 * Utiliza el modelo SiteConfiguration (patrón singleton) para almacenar
 * toda la configuración en una sola fila de la base de datos.
 *
 * Reglas de permisos:
 * - 'ver configuración': acceso de lectura a todas las acciones
 * - 'editar configuración': requerido para actualizar y restaurar valores
 */
class SiteConfigController extends Controller
{
    use AuditTrait;

    /**
     * Constructor del controlador
     *
     * Aplica middlewares de permisos a las acciones del controlador.
     * - Todos los métodos requieren el permiso 'ver configuración'
     * - Solo update() y reset() requieren adicionalmente 'editar configuración'
     */
    public function __construct()
    {
        $this->middleware('permission:ver configuración');
        $this->middleware('permission:editar configuración')->only(['update', 'reset']);
    }

    /**
     * Muestra el formulario de configuración del sitio
     *
     * Carga y muestra la configuración actual del sitio, incluyendo:
     * - Datos del hero (badge, títulos, imágenes)
     * - Propiedades publicadas disponibles para destacar
     * - Propiedades actualmente destacadas (seleccionadas)
     * - Estadísticas: total de propiedades, vistas totales y propiedad más vista
     *
     * @return \Illuminate\View\View Vista modulos.config.index
     */
    public function index()
    {
        $config = SiteConfiguration::getConfig();

        $allProperties = Property::where('status', 'publicada')
            ->with('primaryImage')
            ->orderBy('views', 'desc')
            ->get();

        $featuredIds = $config->featured_properties ?? [];
        if (is_string($featuredIds)) {
            $featuredIds = json_decode($featuredIds, true) ?? [];
        }

        $featuredProperties = Property::whereIn('id', $featuredIds)
            ->where('status', 'publicada')
            ->with('primaryImage')
            ->get();

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
     * Actualiza la configuración del sitio
     *
     * Procesa el formulario de edición y actualiza todos los campos.
     * Flujo del método:
     * 1. Validación de todos los campos del formulario
     * 2. Procesamiento de imágenes del hero (eliminación, subida y combinación)
     * 3. Procesamiento de propiedades destacadas (conversión y validación de IDs)
     * 4. Actualización de todos los campos en la base de datos
     * 5. Registro de auditoría con los cambios realizados
     *
     * @param  \Illuminate\Http\Request  $request  Datos del formulario
     * @return \Illuminate\Http\RedirectResponse
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
        $oldValues = $config->toArray();

        // ==========================================
        // PROCESAR IMÁGENES DEL HERO
        // ==========================================

        // 1. Eliminar imágenes marcadas por el usuario (recibe URLs en JSON)
        $deletedImages = json_decode($request->input('deleted_hero_images', '[]'), true);
        $currentImages = $config->hero_images ?? [];

        if (is_string($currentImages)) {
            $currentImages = json_decode($currentImages, true) ?? [];
        }

        // Normalizar URLs marcadas para eliminar
        $deletedKeys = array_map(fn($url) => $this->imageKey($url), $deletedImages);

        // Filtrar: conservar solo las imágenes NO marcadas para eliminar
        $remainingImages = [];
        foreach ($currentImages as $image) {
            if (in_array($this->imageKey($image), $deletedKeys)) {
                // Eliminar el archivo físico si existe
                if (strpos($image, '/storage/') !== false) {
                    $path = str_replace('/storage/', '', $image);
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            } else {
                $remainingImages[] = $image;
            }
        }

        // 2. Subir nuevas imágenes al storage público (carpeta hero-images)
        $newImages = [];
        if ($request->hasFile('hero_images_new')) {
            foreach ($request->file('hero_images_new') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('hero-images', 'public');
                    $newImages[] = asset('storage/' . $path);
                }
            }
        }

        // 3. Combinar imágenes existentes con las nuevas
        $allImages = array_merge($remainingImages, $newImages);

        // ==========================================
        // PROCESAR PROPIEDADES DESTACADAS
        // ==========================================

        $featuredProperties = $request->input('featured_properties', []);

        // Si viene como string separado por comas, convertir a array
        if (is_string($featuredProperties)) {
            $featuredProperties = explode(',', $featuredProperties);
        }

        // Filtrar IDs numéricos válidos y reindexar
        $featuredProperties = array_filter($featuredProperties, function($id) {
            return is_numeric($id) && $id > 0;
        });
        $featuredProperties = array_map('intval', $featuredProperties);
        $featuredProperties = array_values($featuredProperties);

        // ==========================================
        // ACTUALIZAR CONFIGURACIÓN EN BD
        // ==========================================

        $config->update([
            // Hero
            'hero_badge' => $request->hero_badge,
            'hero_title_line1' => $request->hero_title_line1,
            'hero_title_line2' => $request->hero_title_line2,
            'hero_subtitle' => $request->hero_subtitle,
            'hero_images' => $allImages,

            // Featured
            'featured_badge' => $request->featured_badge,
            'featured_title' => $request->featured_title,
            'featured_properties' => $featuredProperties,

            // Support
            'support_whatsapp' => $request->support_whatsapp,
            'support_instagram' => $request->support_instagram,
            'support_phone' => $request->support_phone,
            'support_email' => $request->support_email,

            // Footer
            'footer_text' => $request->footer_text,
        ]);

        // ==========================================
        // AUDITORÍA
        // ==========================================

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

        // Comparar valores antiguos con los nuevos para generar descripción legible
        foreach ($config->getChanges() as $key => $value) {
            if ($key !== 'updated_at' && isset($oldValues[$key])) {
                $label = $fieldLabels[$key] ?? $key;
                $oldVal = is_array($oldValues[$key]) ? '[' . implode(', ', $oldValues[$key]) . ']' : ($oldValues[$key] ?? 'vacío');
                $newVal = is_array($value) ? '[' . implode(', ', $value) . ']' : ($value ?? 'vacío');
                $changes[] = "{$label}: '{$oldVal}' → '{$newVal}'";
            }
        }

        // Registrar cambio con descripción detallada o log genérico si no hay cambios
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
     * Elimina una imagen específica del carrusel del hero (endpoint AJAX)
     *
     * Recibe la URL de la imagen a eliminar en el cuerpo de la petición,
     * borra el archivo físico del storage y actualiza el array de imágenes.
     *
     * @param  \Illuminate\Http\Request  $request  Petición con el campo 'url'
     * @return \Illuminate\Http\JsonResponse  Respuesta JSON con resultado
     */
    public function deleteImage(Request $request)
    {
        try {
            $config = SiteConfiguration::getConfig();
            $images = $config->hero_images ?? [];

            if (is_string($images)) {
                $images = json_decode($images, true) ?? [];
            }

            $targetKey = $this->imageKey($request->input('url'));

            // Buscar y eliminar la imagen por su URL
            $found = false;
            $remainingImages = [];
            foreach ($images as $image) {
                if ($this->imageKey($image) === $targetKey) {
                    // Eliminar archivo físico del disco público
                    if (strpos($image, '/storage/') !== false) {
                        $path = str_replace('/storage/', '', $image);
                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                    }
                    $found = true;
                } else {
                    $remainingImages[] = $image;
                }
            }

            // Verificar que la imagen exista
            if (!$found) {
                return response()->json([
                    'success' => false,
                    'message' => 'Imagen no encontrada'
                ], 404);
            }

            $config->hero_images = array_values($remainingImages);
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
     * Obtiene una clave normalizada de la URL de una imagen para comparar
     * correctamente aunque cambie el dominio (127.0.0.1, localhost, etc.).
     */
    private function imageKey($url)
    {
        $url = trim((string) $url);

        if (strpos($url, '/storage/') !== false) {
            return substr($url, strpos($url, '/storage/'));
        }

        return $url;
    }

    /**
     * Restaura la configuración del sitio a sus valores por defecto
     *
     * 1. Guarda los valores actuales para auditoría
     * 2. Elimina todas las imágenes del hero del storage público
     * 3. Restaura todos los campos a valores predeterminados
     * 4. Registra la acción en el log de auditoría
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset()
    {
        $config = SiteConfiguration::getConfig();
        $oldValues = $config->toArray();

        // Eliminar todas las imágenes del hero del disco público
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

        // Restaurar valores por defecto (sin imágenes predefinidas)
        $config->update([
            'hero_badge' => 'Exclusividad & Confort',
            'hero_title_line1' => 'El Arte de',
            'hero_title_line2' => 'Vivir Bien',
            'hero_subtitle' => 'Descubre una curaduría exclusiva de propiedades de lujo en las mejores zonas de Venezuela.',
            'hero_images' => [],
            'featured_badge' => 'Colección Exclusiva',
            'featured_title' => 'Propiedades Destacadas',
            'featured_properties' => [],
            'support_whatsapp' => '58XXXXXXXXX',
            'support_instagram' => 'msoinmobiliaria',
            'support_phone' => null,
            'support_email' => null,
            'footer_text' => '© ' . date('Y') . ' MSO Inmobiliaria. Todos los derechos reservados.',
        ]);

        $this->logAudit('updated', $config, $oldValues, $config->toArray(),
            (Auth::user()?->full_name ?? 'Sistema') . ' RESTAURÓ la configuración del sitio a valores por defecto'
        );

        return redirect()->route('admin.config.index')
            ->with('success', 'Configuración restaurada a valores por defecto.');
    }

    /**
     * Busca propiedades por término de búsqueda (endpoint AJAX para autocompletado)
     *
     * Busca propiedades publicadas por título, ID o ubicación (búsqueda parcial).
     * Retorna máximo 20 resultados ordenados por vistas (más populares primero),
     * incluyendo la URL de la imagen principal para vista previa en el autocomplete.
     *
     * @param  \Illuminate\Http\Request  $request  Parámetro 'q' con el término de búsqueda
     * @return \Illuminate\Http\JsonResponse  Array JSON con las propiedades encontradas
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
