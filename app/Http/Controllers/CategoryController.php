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
            'categories' => Category::latest()->paginate(10),
        ]);
    }

    /**
     * Mostra o formulário para criar uma nova categoria.
     */
    public function create(): Response
    {
        return Inertia::render('Categories/Create');
    }

    /**
     * Guarda uma nova categoria na base de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:'.Category::class,
            'description' => 'nullable|string',
        ]);

        Category::create($request->all());

        return redirect()->route('categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    /**
     * Mostra o formulário para editar uma categoria existente.
     */
    public function edit(Category $category): Response
    {
        return Inertia::render('Categories/Edit', [
            'category' => $category,
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
        ]);

        $category->update($request->all());

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
