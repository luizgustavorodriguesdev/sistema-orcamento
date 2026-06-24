<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class ThemeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Theme::latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $themes = $query->paginate(10)->withQueryString();

        return Inertia::render('Themes/Index', [
            'themes' => $themes,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:themes,name',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Theme::create($validated);

        return redirect()->back()->with('success', 'Tema criado com sucesso.');
    }

    public function update(Request $request, Theme $theme): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:themes,name,' . $theme->id,
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $theme->update($validated);

        return redirect()->back()->with('success', 'Tema atualizado com sucesso.');
    }

    public function destroy(Theme $theme): RedirectResponse
    {
        $theme->delete();

        return redirect()->back()->with('success', 'Tema removido com sucesso.');
    }
}
