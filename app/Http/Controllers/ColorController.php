<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class ColorController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Color::latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $colors = $query->paginate(10)->withQueryString();

        return Inertia::render('Colors/Index', [
            'colors' => $colors,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:colors,name',
            'hex_code' => 'nullable|string|max:7',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Color::create($validated);

        return redirect()->back()->with('success', 'Cor criada com sucesso.');
    }

    public function update(Request $request, Color $color): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:colors,name,' . $color->id,
            'hex_code' => 'nullable|string|max:7',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $color->update($validated);

        return redirect()->back()->with('success', 'Cor atualizada com sucesso.');
    }

    public function destroy(Color $color): RedirectResponse
    {
        $color->delete();

        return redirect()->back()->with('success', 'Cor removida com sucesso.');
    }
}
