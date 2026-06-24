<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    /**
     * Lista todas as páginas customizadas no painel administrativo.
     */
    public function index(): Response
    {
        return Inertia::render('Pages/Index', [
            'pages' => Page::latest()->paginate(10),
        ]);
    }

    /**
     * Exibe o formulário de criação de páginas.
     */
    public function create(): Response
    {
        return Inertia::render('Pages/Create');
    }

    /**
     * Salva uma nova página no banco de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        Page::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'content' => $request->content,
            'custom_css' => $request->custom_css,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('pages.index')->with('success', 'Página criada com sucesso.');
    }

    /**
     * Exibe o formulário de edição de uma página.
     */
    public function edit(Page $page): Response
    {
        return Inertia::render('Pages/Edit', [
            'page' => $page,
        ]);
    }

    /**
     * Atualiza uma página existente no banco de dados.
     */
    public function update(Request $request, Page $page): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($page->id)],
            'content' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $page->update([
            'title' => $request->title,
            'slug' => $request->slug,
            'content' => $request->content,
            'custom_css' => $request->custom_css,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('pages.index')->with('success', 'Página atualizada com sucesso.');
    }

    /**
     * Remove uma página do banco de dados.
     */
    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('pages.index')->with('success', 'Página excluída com sucesso.');
    }
}
