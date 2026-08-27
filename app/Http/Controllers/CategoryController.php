<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Mostra uma lista de todas as categorias.
     */
    public function index(): Response
    {
        return Inertia::render('Categories/Index', [
            'categories' => Category::with('parent')->latest()->paginate(10),
        ]);
    }

    /**
     * Mostra o formulário para criar uma nova categoria.
     */
    public function create(): Response
    {
        return Inertia::render('Categories/Create', [
            'parentCategories' => Category::whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }

    /**
     * Guarda uma nova categoria na base de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'show_in_highlighted_menu' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
            'mega_menu_banner' => 'nullable|image|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $data = $request->except(['image', 'mega_menu_banner']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['show_in_highlighted_menu'] = $request->boolean('show_in_highlighted_menu');
        $data['parent_id'] = $request->filled('parent_id') ? $request->input('parent_id') : null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $data['image_path'] = $path;
        }

        if ($request->hasFile('mega_menu_banner')) {
            $bannerPath = $request->file('mega_menu_banner')->store('categories/banners', 'public');
            $data['mega_menu_banner_path'] = $bannerPath;
        }

        Category::create($data);

        return redirect()->route('categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    /**
     * Mostra o formulário para editar uma categoria existente.
     */
    public function edit(Category $category): Response
    {
        return Inertia::render('Categories/Edit', [
            'category' => $category,
            'parentCategories' => Category::whereNull('parent_id')
                ->where('id', '!=', $category->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Atualiza uma categoria existente na base de dados.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'show_in_highlighted_menu' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
            'mega_menu_banner' => 'nullable|image|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $data = $request->except(['image', 'mega_menu_banner']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['show_in_highlighted_menu'] = $request->boolean('show_in_highlighted_menu');
        $data['parent_id'] = $request->filled('parent_id') ? $request->input('parent_id') : null;

        if ($request->hasFile('image')) {
            if ($category->image_path && \Storage::disk('public')->exists($category->image_path)) {
                \Storage::disk('public')->delete($category->image_path);
            }
            $path = $request->file('image')->store('categories', 'public');
            $data['image_path'] = $path;
        }

        if ($request->hasFile('mega_menu_banner')) {
            if ($category->mega_menu_banner_path && \Storage::disk('public')->exists($category->mega_menu_banner_path)) {
                \Storage::disk('public')->delete($category->mega_menu_banner_path);
            }
            $bannerPath = $request->file('mega_menu_banner')->store('categories/banners', 'public');
            $data['mega_menu_banner_path'] = $bannerPath;
        }

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    /**
     * Remove uma categoria da base de dados.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Categoria apagada com sucesso.');
    }
}
