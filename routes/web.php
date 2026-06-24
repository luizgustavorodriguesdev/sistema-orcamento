<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StorefrontController; // Certifique-se que esta linha existe
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\PersonalizationTypeController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CharacteristicController;
use App\Models\Quote;
use App\Models\Product;
use App\Models\Client;
use App\Models\Category;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rotas Públicas (Vitrine)
|--------------------------------------------------------------------------
*/
Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/carrinho', [StorefrontController::class, 'cart'])->name('storefront.cart');
Route::post('/carrinho', [StorefrontController::class, 'storeQuote'])->name('storefront.quote.store');
Route::get('/category/{category:slug}', [StorefrontController::class, 'categoryShow'])->name('storefront.category.show');

// [A ROTA CORRETA É ESTA]
// Ela usa o URL /produto/ e aponta para o StorefrontController
Route::get('/produto/{product:slug}', [StorefrontController::class, 'show'])->name('storefront.product.show');


/*
|--------------------------------------------------------------------------
| Rotas do Painel de Controlo (Admin)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'stats' => [
            'total_quotes' => Quote::count(),
            'pending_quotes' => Quote::where('status', 'Pendente')->count(),
            'approved_quotes' => Quote::where('status', 'Aprovado')->count(),
            'approved_amount' => (float) Quote::where('status', 'Aprovado')->sum('total_amount'),
            'total_products' => Product::count(),
            'total_clients' => Client::count(),
            'total_categories' => Category::count(),
        ],
        'recent_quotes' => Quote::with('client')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($quote) {
                return [
                    'id' => $quote->id,
                    'client_name' => $quote->client_name,
                    'status' => $quote->status,
                    'total_amount' => (float) $quote->total_amount,
                    'created_at' => $quote->created_at->format('d/m/Y H:i'),
                    'unique_hash' => $quote->unique_hash,
                ];
            }),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/orcamento/{quote:unique_hash}', [QuoteController::class, 'showPublic'])->name('quotes.public.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Nossas rotas de recursos
    Route::resource('products', ProductController::class);
    Route::resource('quotes', QuoteController::class);
    Route::resource('users', UserController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('payment-methods', PaymentMethodController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('menu-items', MenuItemController::class);
    Route::resource('pages', PageController::class);
    Route::resource('media', MediaController::class);
    Route::get('/api/media', [MediaController::class, 'apiIndex'])->name('api.media.index');
    
    // Rota para apagar imagens de produtos
    Route::delete('/product-images/{productImage}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');

    // Rotas de Filtros/Atributos
    Route::resource('themes', ThemeController::class)->except(['create', 'show', 'edit']);
    Route::resource('personalization-types', PersonalizationTypeController::class)->except(['create', 'show', 'edit']);
    Route::resource('colors', ColorController::class)->except(['create', 'show', 'edit']);
    Route::resource('characteristics', CharacteristicController::class)->except(['create', 'show', 'edit']);

    // Rotas de Configurações
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
});

require __DIR__.'/auth.php';

// Rota de fallback para páginas institucionais dinâmicas na raiz (ex: /quem-somos, /contato)
Route::get('/{page:slug}', [StorefrontController::class, 'customPageShow'])->name('storefront.page.show');