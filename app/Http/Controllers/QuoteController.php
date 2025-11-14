<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    public function index(): Response
    {
        $quotes = Quote::with(['user', 'client'])->latest()->paginate(10);
        $settings = Setting::all()->pluck('value', 'key');

        return Inertia::render('Quotes/Index', [
            'quotes' => $quotes,
            'settings' => $settings,
        ]);
    }

    public function create(): Response
    {
        $products = Product::with('priceTiers')->orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Quotes/Create', [
            'products' => $products,
            'clients' => $clients,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'payment_terms' => 'nullable|string',
            'delivery_info' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $totalAmount = 0;
        $itemsToAttach = [];

        foreach ($validated['items'] as $item) {
            $product = Product::with('priceTiers')->find($item['product_id']);
            $quantity = $item['quantity'];
            $unitPrice = $this->getPriceForQuantity($product, $quantity);

            $totalAmount += $unitPrice * $quantity;
            $itemsToAttach[$item['product_id']] = [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        }

        $quote = Quote::create([
            'client_id' => $validated['client_id'],
            'payment_terms' => $validated['payment_terms'],
            'delivery_info' => $validated['delivery_info'],
            'total_amount' => $totalAmount,
            'user_id' => Auth::id(),
            'unique_hash' => Str::random(16),
            'status' => 'Pendente',
        ]);

        $quote->products()->attach($itemsToAttach);

        return redirect()->route('quotes.index')->with('success', 'Orçamento criado com sucesso!');
    }

    public function edit(Quote $quote): Response
    {
        $quote->load('products', 'client');
        
        return Inertia::render('Quotes/Edit', [
            'quote' => $quote,
            'products' => Product::with('priceTiers')->orderBy('name')->get(),
            'clients' => Client::orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'payment_terms' => 'nullable|string',
            'delivery_info' => 'nullable|string',
            'status' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        
        $totalAmount = 0;
        $itemsToSync = [];

        foreach ($validated['items'] as $item) {
            $product = Product::with('priceTiers')->find($item['product_id']);
            $quantity = $item['quantity'];
            $unitPrice = $this->getPriceForQuantity($product, $quantity);

            $totalAmount += $unitPrice * $quantity;
            $itemsToSync[$item['product_id']] = [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        }

        $quote->update([
            'client_id' => $validated['client_id'],
            'payment_terms' => $validated['payment_terms'],
            'delivery_info' => $validated['delivery_info'],
            'status' => $validated['status'],
            'total_amount' => $totalAmount,
        ]);

        $quote->products()->sync($itemsToSync);

        return redirect()->route('quotes.index')->with('success', 'Orçamento atualizado com sucesso!');
    }

    public function showPublic(Quote $quote): Response
    {
        $quote->load('products', 'user', 'client');
        $settings = Setting::all()->pluck('value', 'key');

        return Inertia::render('Quotes/PublicShow', [
            'quote' => $quote,
            'settings' => $settings,
        ]);
    }

    private function getPriceForQuantity(Product $product, int $quantity): float
    {
        $tier = $product->priceTiers()
                        ->where('min_quantity', '<=', $quantity)
                        ->orderBy('min_quantity', 'desc')
                        ->first();
        return $tier ? $tier->price : $product->price;
    }
}
