<?php

namespace App\Http\Controllers;

use App\Models\Characteristic;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class CharacteristicController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Characteristic::latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $characteristics = $query->paginate(10)->withQueryString();

        return Inertia::render('Characteristics/Index', [
            'characteristics' => $characteristics,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:characteristics,name',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Characteristic::create($validated);

        return redirect()->back()->with('success', 'Característica criada com sucesso.');
    }

    public function update(Request $request, Characteristic $characteristic): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:characteristics,name,' . $characteristic->id,
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $characteristic->update($validated);

        return redirect()->back()->with('success', 'Característica atualizada com sucesso.');
    }

    public function destroy(Characteristic $characteristic): RedirectResponse
    {
        $characteristic->delete();

        return redirect()->back()->with('success', 'Característica removida com sucesso.');
    }
}
