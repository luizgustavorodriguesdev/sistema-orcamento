<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::orderBy('id', 'desc')->paginate(10);
        return Inertia::render('Warehouses/Index', [
            'warehouses' => $warehouses
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')->with('success', 'Depósito criado com sucesso!');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')->with('success', 'Depósito atualizado com sucesso!');
    }

    public function destroy(Warehouse $warehouse)
    {
        // Impede a exclusão se houver saldo de estoque associado
        $hasStock = $warehouse->products()->wherePivot('quantity', '>', 0)->exists();
        if ($hasStock) {
            return redirect()->route('warehouses.index')->with('error', 'Não é possível excluir um depósito que possui saldo de estoque!');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Depósito excluído com sucesso!');
    }
}
