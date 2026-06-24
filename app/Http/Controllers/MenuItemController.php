<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class MenuItemController extends Controller
{
    /**
     * Mostra uma lista de todos os itens do menu.
     */
    public function index(): Response
    {
        $menuItems = MenuItem::with(['parent', 'category', 'product'])
            ->orderBy('order')
            ->get();

        return Inertia::render('MenuItems/Index', [
            'menuItems' => $menuItems,
        ]);
    }

    /**
     * Mostra o formulário para criar um novo item de menu.
     */
    public function create(): Response
    {
        return Inertia::render('MenuItems/Create', [
            'categories' => Category::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'parentCandidates' => MenuItem::whereNull('parent_id')->orderBy('order')->get(),
        ]);
    }

    /**
     * Guarda um novo item de menu na base de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:category,product,custom',
            'parent_id' => 'nullable|exists:menu_items,id',
            'url' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'product_id' => 'nullable|exists:products,id',
            'order' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        MenuItem::create($request->all());

        return redirect()->route('menu-items.index')->with('success', 'Item de menu criado com sucesso.');
    }

    /**
     * Mostra o formulário para editar um item de menu existente.
     */
    public function edit(MenuItem $menuItem): Response
    {
        return Inertia::render('MenuItems/Edit', [
            'menuItem' => $menuItem,
            'categories' => Category::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'parentCandidates' => MenuItem::whereNull('parent_id')
                ->where('id', '!=', $menuItem->id)
                ->orderBy('order')
                ->get(),
        ]);
    }

    /**
     * Atualiza um item de menu existente na base de dados.
     */
    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:category,product,custom',
            'parent_id' => 'nullable|exists:menu_items,id',
            'url' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'product_id' => 'nullable|exists:products,id',
            'order' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        if ($request->parent_id == $menuItem->id) {
            return back()->withErrors(['parent_id' => 'Um item de menu não pode ser pai de si mesmo.']);
        }

        $menuItem->update($request->all());

        return redirect()->route('menu-items.index')->with('success', 'Item de menu atualizado com sucesso.');
    }

    /**
     * Remove um item de menu da base de dados.
     */
    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();
        return redirect()->route('menu-items.index')->with('success', 'Item de menu apagado com sucesso.');
    }
}
