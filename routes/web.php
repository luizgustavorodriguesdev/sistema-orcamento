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

// [A ROTA CORRETA É ESTA]
// Ela usa o URL /produto/ e aponta para o StorefrontController
Route::get('/produto/{product:slug}', [StorefrontController::class, 'show'])->name('storefront.product.show');


/*
|--------------------------------------------------------------------------
| Rotas do Painel de Controlo (Admin)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
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
    
    // Rota para apagar imagens de produtos
    Route::delete('/product-images/{productImage}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');

    // Rotas de Configurações
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
});

require __DIR__.'/auth.php';