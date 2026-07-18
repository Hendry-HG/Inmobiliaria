<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Super Admin|Administrador']);
    }

    public function index(Request $request)
    {
        $query = Category::withCount('properties');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where('name', 'LIKE', $search)
                  ->orWhere('description', 'LIKE', $search);
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

    public function create()
    {
        return view('modulos.categoria.form');
    }

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

            return redirect()->route('admin.categories.index')
                ->with('success', 'Categoría creada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Hubo un problema al crear la categoría: ' . $e->getMessage()]);
        }
    }

    public function edit(Category $category)
    {
        return view('modulos.categoria.form', compact('category'));
    }

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

            return redirect()->route('admin.categories.index')
                ->with('success', 'Categoría actualizada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Hubo un problema al actualizar la categoría.']);
        }
    }

    public function destroy(Category $category)
    {
        try {
            // Verificar si tiene propiedades asociadas
            if ($category->properties()->count() > 0) {
                return redirect()->back()->withErrors([
                    'error' => 'No se puede eliminar la categoría porque tiene ' . $category->properties()->count() . ' propiedades asociadas.'
                ]);
            }

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
