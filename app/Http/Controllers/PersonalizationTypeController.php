<?php

namespace App\Http\Controllers;

use App\Models\PersonalizationType;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class PersonalizationTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = PersonalizationType::latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $items = $query->paginate(10)->withQueryString();

        return Inertia::render('PersonalizationTypes/Index', [
            'items' => $items,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:personalization_types,name',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        PersonalizationType::create($validated);

        return redirect()->back()->with('success', 'Tipo de Personalização criado com sucesso.');
    }

    public function update(Request $request, PersonalizationType $personalizationType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:personalization_types,name,' . $personalizationType->id,
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $personalizationType->update($validated);

        return redirect()->back()->with('success', 'Tipo de Personalização atualizado com sucesso.');
    }

    public function destroy(PersonalizationType $personalizationType): RedirectResponse
    {
        $personalizationType->delete();

        return redirect()->back()->with('success', 'Tipo de Personalização removido com sucesso.');
    }
}
