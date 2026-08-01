<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with('supplier', 'creator')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return Inertia::render('PurchaseOrders/Index', [
            'orders' => $orders
        ]);
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::where('track_stock', true)->orderBy('name')->get();

        return Inertia::render('PurchaseOrders/Create', [
            'suppliers' => $suppliers,
            'products' => $products
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'po_number' => 'required|string|max:50|unique:purchase_orders,po_number',
            'observations' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            $order = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'po_number' => $validated['po_number'],
                'status' => 'Rascunho',
                'total_amount' => $totalAmount,
                'observations' => $validated['observations'],
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')->with('success', 'Ordem de compra criada com sucesso!');
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items.product', 'supplier');
        $suppliers = Supplier::all();
        $products = Product::where('track_stock', true)->orderBy('name')->get();

        return Inertia::render('PurchaseOrders/Edit', [
            'order' => $purchaseOrder,
            'suppliers' => $suppliers,
            'products' => $products
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['Rascunho', 'Aprovado'])) {
            return redirect()->route('purchase-orders.index')->with('error', 'Apenas ordens em Rascunho ou Aprovadas podem ser editadas!');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'po_number' => 'required|string|max:50|unique:purchase_orders,po_number,' . $purchaseOrder->id,
            'observations' => 'nullable|string',
            'status' => 'required|string|in:Rascunho,Aprovado,Cancelado',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($purchaseOrder, $validated) {
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'po_number' => $validated['po_number'],
                'status' => $validated['status'],
                'total_amount' => $totalAmount,
                'observations' => $validated['observations'],
            ]);

            // Atualiza itens (simples: deleta e reinsere)
            $purchaseOrder->items()->delete();
            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')->with('success', 'Ordem de compra atualizada com sucesso!');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'Rascunho') {
            return redirect()->route('purchase-orders.index')->with('error', 'Apenas ordens em status de Rascunho podem ser deletadas!');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'Ordem de compra excluída com sucesso!');
    }

    /**
     * Exibe o formulário de check-in de recebimento da ordem de compra.
     */
    public function checkinForm(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'Recebido') {
            return redirect()->route('purchase-orders.index')->with('error', 'Esta ordem de compra já foi totalmente recebida!');
        }

        $purchaseOrder->load('items.product', 'supplier');
        $warehouses = Warehouse::where('is_active', true)->get();

        return Inertia::render('PurchaseOrders/CheckIn', [
            'order' => $purchaseOrder,
            'warehouses' => $warehouses
        ]);
    }

    /**
     * Processa a entrada física de mercadorias.
     */
    public function checkin(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'received' => 'required|array',
            'received.*' => 'required|integer|min:0',
        ]);

        StockService::receivePurchaseOrder($purchaseOrder, $validated['received'], $validated['warehouse_id']);

        return redirect()->route('purchase-orders.index')->with('success', 'Check-in de recebimento registrado com sucesso!');
    }

    /**
     * Painel de sugestões de compra por estoque mínimo.
     */
    public function suggestions()
    {
        // Produtos com estoque atual <= estoque mínimo
        $products = Product::where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $suggestedQty = max(1, ($product->minimum_stock * 2) - $product->stock_quantity);
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock_quantity' => $product->stock_quantity,
                    'minimum_stock' => $product->minimum_stock,
                    'cost_price' => $product->cost_price,
                    'suggested_quantity' => $suggestedQty,
                ];
            });

        $suppliers = Supplier::all();

        return Inertia::render('Stock/PurchaseSuggestions', [
            'products' => $products,
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Auto-gera uma ordem de compra rascunho baseada em itens selecionados na sugestão.
     */
    public function generateFromSuggestions(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $poNumber = 'OC-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

        DB::transaction(function () use ($validated, $poNumber) {
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            $order = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'po_number' => $poNumber,
                'status' => 'Rascunho',
                'total_amount' => $totalAmount,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')->with('success', 'Ordem de compra rascunho gerada com sucesso!');
    }
}
