<?php

namespace App\Http\Controllers;

use App\Models\InventorySession;
use App\Models\InventoryItem;
use App\Models\Warehouse;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        $sessions = InventorySession::with('warehouse', 'creator')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $warehouses = Warehouse::where('is_active', true)->get();

        return Inertia::render('Inventory/Index', [
            'sessions' => $sessions,
            'warehouses' => $warehouses
        ]);
    }

    /**
     * Inicia uma nova sessão de inventário.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'description' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $session = InventorySession::create([
                'warehouse_id' => $validated['warehouse_id'],
                'description' => $validated['description'],
                'status' => 'Em_Andamento',
                'created_by' => Auth::id(),
            ]);

            // Snapshot do estoque teórico atual de todos os produtos ativos para o depósito selecionado
            $products = Product::where('track_stock', true)->get();

            foreach ($products as $product) {
                // Obtém a quantidade atual no depósito específico
                $warehouseStock = $product->warehouses()->where('warehouse_id', $session->warehouse_id)->first();
                $expectedQty = $warehouseStock ? $warehouseStock->pivot->quantity : 0;

                InventoryItem::create([
                    'inventory_session_id' => $session->id,
                    'product_id' => $product->id,
                    'expected_quantity' => $expectedQty,
                    'counted_quantity' => null, // Contagem física inicial é nula
                ]);
            }
        });

        return redirect()->route('inventory.index')->with('success', 'Sessão de inventário iniciada com sucesso!');
    }

    /**
     * Visualiza a folha de conferência de um inventário em andamento.
     */
    public function show(InventorySession $inventory)
    {
        $inventory->load('warehouse', 'creator', 'items.product');

        return Inertia::render('Inventory/Show', [
            'session' => $inventory
        ]);
    }

    /**
     * Salva as contagens físicas em andamento.
     */
    public function saveCounts(Request $request, InventorySession $inventory)
    {
        if ($inventory->status !== 'Em_Andamento') {
            return redirect()->route('inventory.show', $inventory->id)->with('error', 'Esta sessão de inventário já foi fechada!');
        }

        $validated = $request->validate([
            'counts' => 'required|array',
            'counts.*.product_id' => 'required|exists:products,id',
            'counts.*.counted_quantity' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($inventory, $validated) {
            foreach ($validated['counts'] as $countData) {
                $item = InventoryItem::where('inventory_session_id', $inventory->id)
                    ->where('product_id', $countData['product_id'])
                    ->first();

                if ($item) {
                    $item->update([
                        'counted_quantity' => $countData['counted_quantity']
                    ]);
                }
            }
        });

        return redirect()->route('inventory.show', $inventory->id)->with('success', 'Rascunho de contagens físicas salvo!');
    }

    /**
     * Finaliza a contagem e aplica a reconciliação corretiva no estoque.
     */
    public function reconcile(Request $request, InventorySession $inventory)
    {
        if ($inventory->status !== 'Em_Andamento') {
            return redirect()->route('inventory.show', $inventory->id)->with('error', 'Esta sessão de inventário já foi fechada!');
        }

        // Salva as últimas contagens enviadas antes de fechar
        $validated = $request->validate([
            'counts' => 'required|array',
            'counts.*.product_id' => 'required|exists:products,id',
            'counts.*.counted_quantity' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($inventory, $validated) {
            foreach ($validated['counts'] as $countData) {
                $item = InventoryItem::where('inventory_session_id', $inventory->id)
                    ->where('product_id', $countData['product_id'])
                    ->first();

                if ($item) {
                    $item->update([
                        'counted_quantity' => $countData['counted_quantity']
                    ]);
                }
            }
        });

        // Executa a reconciliação dos deltas via StockService
        StockService::reconcileInventory($inventory);

        return redirect()->route('inventory.index')->with('success', 'Reconciliação de estoque do inventário concluída!');
    }

    /**
     * Cancela uma sessão de inventário em andamento.
     */
    public function cancel(InventorySession $inventory)
    {
        if ($inventory->status !== 'Em_Andamento') {
            return redirect()->route('inventory.index')->with('error', 'Apenas inventários em andamento podem ser cancelados!');
        }

        $inventory->status = 'Cancelado';
        $inventory->completed_at = now();
        $inventory->save();

        return redirect()->route('inventory.index')->with('success', 'Sessão de inventário cancelada com sucesso!');
    }
}
