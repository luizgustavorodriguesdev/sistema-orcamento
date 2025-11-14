<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
// [CORREÇÃO]: Adicione esta linha se estiver em falta.
use App\Models\Client;

class StorefrontController extends Controller
{
    /**
     * Exibe a página principal da vitrine com os produtos.
     */
    public function index(): Response
    {
        // Carregamos as relações 'images', 'category' e 'priceTiers' de forma explícita.
        $products = Product::with(['images', 'category', 'priceTiers'])
            ->latest()
            ->paginate(12);

        $categories = Category::orderBy('name')->get();
        $settings = Setting::all()->pluck('value', 'key');

        return Inertia::render('Storefront/Index', [
            'products' => $products,
            'categories' => $categories,
            'settings' => $settings,
        ]);
    }

    /**
     * Exibe a página de detalhes de um único produto.
     */
    public function show(Product $product): Response
    {
        // Carrega todas as relações necessárias para a página de detalhes.
        $product->load('images', 'category', 'priceTiers');
        $categories = Category::orderBy('name')->get();
        $settings = Setting::all()->pluck('value', 'key');

        return Inertia::render('Storefront/Show', [
            'product' => $product,
            'categories' => $categories,
            'settings' => $settings,
        ]);
    }

    /**
     * Exibe a página do carrinho de orçamento.
     */
    public function cart(): Response
    {
        return Inertia::render('Storefront/Cart', [
            'settings' => Setting::all()->pluck('value', 'key'),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Salva o orçamento criado pelo cliente.
     * [CORREÇÃO]: Garante que (Request $request) está na definição da função.
     */
    public function storeQuote(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_contact' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $totalAmount = 0;
        $processedItems = [];

        $quote = DB::transaction(function () use ($request, &$totalAmount, &$processedItems) {
            foreach ($request->items as $item) {
                $product = Product::with('priceTiers')->find($item['product_id']);
                $quantity = $item['quantity'];
                
                $unitPrice = $this->getPriceForQuantity($product, $quantity);
                $totalAmount += $unitPrice * $quantity;
                
                $processedItems[$item['product_id']] = [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ];
            }

            // Encontra ou cria o cliente
            $client = Client::firstOrCreate(
                ['contact_main' => $request->client_contact],
                ['name' => $request->client_name]
            );

            $newQuote = Quote::create([
                'unique_hash' => Str::random(16),
                'client_id' => $client->id, // Usa o ID do cliente
                'user_id' => null, 
                'status' => 'Pendente',
                'total_amount' => $totalAmount,
            ]);

            $newQuote->products()->attach($processedItems);

            return $newQuote;
        });

        return redirect()->route('quotes.public.show', ['quote' => $quote->unique_hash]);
    }

    /**
     * Lógica de cálculo de preços por quantidade.
     * [CORREÇÃO]: Garante que (Product $product, int $quantity) estão na definição.
     */
    private function getPriceForQuantity(Product $product, int $quantity): float
    {
        if ($product->priceTiers->isEmpty()) {
            return (float) ($product->promotional_price ?? $product->price);
        }

        $applicableTier = $product->priceTiers
            ->sortByDesc('min_quantity')
            ->first(function ($tier) use ($quantity) {
                return $quantity >= $tier->min_quantity;
            });

        if ($applicableTier) {
            return (float) $applicableTier->price;
        }

        return (float) ($product->promotional_price ?? $product->price);
    }
}