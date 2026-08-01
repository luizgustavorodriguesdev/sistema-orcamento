<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Category;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class StockController extends Controller
{
    /**
     * Exibe a listagem do controle de estoque, saldos por depósito, lançamentos manuais e visão financeira.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $warehouseId = $request->input('warehouse_id');

        // Query principal de produtos carregando os saldos em cada depósito
        $productsQuery = Product::with(['category', 'warehouses'])->where('track_stock', true);

        if (!empty($search)) {
            $productsQuery->where('name', 'like', "%{$search}%");
        }

        if (!empty($categoryId)) {
            $productsQuery->where('category_id', $categoryId);
        }

        if (!empty($warehouseId)) {
            $productsQuery->whereHas('warehouses', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        $products = $productsQuery->orderBy('name')->paginate(10)->withQueryString();

        // Query de histórico de movimentações
        $movements = StockMovement::with(['product', 'user', 'warehouse', 'purchaseOrder', 'inventorySession'])
            ->latest()
            ->paginate(15, ['*'], 'movements_page')
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $allProducts = Product::where('track_stock', true)->orderBy('name')->get();

        // 3. Cálculos Financeiros do Estoque
        $financialSummary = DB::table('product_warehouse')
            ->join('products', 'products.id', '=', 'product_warehouse.product_id')
            ->selectRaw('
                SUM(product_warehouse.quantity * products.cost_price) as total_cost,
                SUM(product_warehouse.quantity * products.price) as total_retail
            ')
            ->first();

        $totalCost = (float) ($financialSummary->total_cost ?? 0.00);
        $totalRetail = (float) ($financialSummary->total_retail ?? 0.00);
        $potentialProfit = $totalRetail - $totalCost;
        $profitMarginPercent = $totalCost > 0 ? ($potentialProfit / $totalCost) * 100 : 0;

        return Inertia::render('Stock/Index', [
            'products' => $products,
            'movements' => $movements,
            'categories' => $categories,
            'warehouses' => $warehouses,
            'allProducts' => $allProducts,
            'financial' => [
                'total_cost' => $totalCost,
                'total_retail' => $totalRetail,
                'potential_profit' => $potentialProfit,
                'margin_percent' => $profitMarginPercent
            ],
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'warehouse_id' => $warehouseId,
            ],
        ]);
    }

    /**
     * Realiza um lançamento manual estruturado de estoque.
     */
    public function manualMovement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:addition,subtraction',
            'description' => 'required|string|max:255',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $quantity = $validated['quantity'];

        if ($validated['type'] === 'subtraction') {
            $quantity = -$quantity;
        }

        StockService::adjustStock(
            $product,
            $quantity,
            'adjustment',
            $validated['description'],
            null,
            null,
            $validated['warehouse_id']
        );

        return redirect()->route('stock.index')->with('success', 'Lançamento manual registrado com sucesso!');
    }
}
