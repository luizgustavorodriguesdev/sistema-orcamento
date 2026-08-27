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
use App\Models\Client;
use App\Models\PaymentMethod;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Banner;
use App\Models\PromotionPopup;

class StorefrontController extends Controller
{
    /**
     * Busca dados gerais da vitrine (menus, configurações, categorias).
     */
    private function getStorefrontData(): array
    {
        $menuItems = MenuItem::with([
            'children' => function($q) {
                $q->where('is_active', true)->orderBy('order');
            },
            'children.category',
            'children.product',
            'category',
            'product'
        ])
        ->whereNull('parent_id')
        ->where('is_active', true)
        ->orderBy('order')
        ->get();

        $settings = Setting::all()->pluck('value', 'key');
        $categories = Category::orderBy('name')->get();

        $highlightedMenuItems = [];
        
        $highlightedCats = Category::with('children')->whereNull('parent_id')->where('show_in_highlighted_menu', true)->orderBy('name')->get();
        foreach ($highlightedCats as $cat) {
            $highlightedMenuItems[] = [
                'label' => $cat->name,
                'url' => route('storefront.category.show', ['category' => $cat->slug]),
                'type' => 'category',
                'mega_menu_banner' => $cat->mega_menu_banner_path,
                'subcategories' => $cat->children->map(fn($child) => [
                    'label' => $child->name,
                    'url' => route('storefront.category.show', ['category' => $child->slug]),
                ]),
            ];
        }

        $highlightedPages = Page::where('is_active', true)->where('show_in_highlighted_menu', true)->orderBy('title')->get();
        foreach ($highlightedPages as $page) {
            $highlightedMenuItems[] = [
                'label' => $page->title,
                'url' => route('storefront.page.show', ['page' => $page->slug]),
                'type' => 'page'
            ];
        }

        $highlightedProds = Product::where('show_in_highlighted_menu', true)->orderBy('name')->get();
        foreach ($highlightedProds as $prod) {
            $highlightedMenuItems[] = [
                'label' => $prod->name,
                'url' => route('storefront.product.show', ['product' => $prod->slug]),
                'type' => 'product'
            ];
        }

        return [
            'menuItems' => $menuItems,
            'settings' => $settings,
            'categories' => $categories,
            'highlightedMenuItems' => $highlightedMenuItems,
        ];
    }

    /**
     * Exibe a página principal da vitrine com os produtos.
     */
    public function index(Request $request): Response
    {
        $query = Product::with(['images', 'category', 'priceTiers'])->latest();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->paginate(12);

        $storefrontData = $this->getStorefrontData();

        // Carrega até 4 categorias destacadas
        $featuredCategories = Category::where('is_featured', true)->take(4)->get();
        if ($featuredCategories->count() < 4) {
            $missingCount = 4 - $featuredCategories->count();
            $additional = Category::where('is_featured', false)->take($missingCount)->get();
            $featuredCategories = $featuredCategories->merge($additional);
        }

        $banners = Banner::where('is_active', true)->orderBy('order')->get();

        $now = now();
        $activePopup = PromotionPopup::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->latest()
            ->first();

        return Inertia::render('Storefront/Index', array_merge($storefrontData, [
            'products' => $products,
            'featuredCategories' => $featuredCategories,
            'selectedCategoryId' => $request->category,
            'banners' => $banners,
            'activePopup' => $activePopup,
        ]));
    }

    /**
     * Exibe a página de detalhes de um único produto.
     */
    public function show(Product $product): Response
    {
        $product->load('images', 'category', 'priceTiers');
        $storefrontData = $this->getStorefrontData();

        return Inertia::render('Storefront/Show', array_merge($storefrontData, [
            'product' => $product,
        ]));
    }

    /**
     * Exibe a página exclusiva de uma categoria com seus produtos.
     */
    public function categoryShow(Request $request, Category $category): Response
    {
        // 1. Extrair opções de filtros com base em todos os produtos pertencentes a esta categoria
        $productIdsInCat = Product::where('category_id', $category->id)->pluck('id');

        $availableThemes = \App\Models\Theme::whereHas('products', function($q) use ($productIdsInCat) {
            $q->whereIn('products.id', $productIdsInCat);
        })->orderBy('name')->get(['id', 'name', 'slug']);

        $availablePersonalizations = \App\Models\PersonalizationType::whereHas('products', function($q) use ($productIdsInCat) {
            $q->whereIn('products.id', $productIdsInCat);
        })->orderBy('name')->get(['id', 'name', 'slug']);

        $availableColors = \App\Models\Color::whereHas('products', function($q) use ($productIdsInCat) {
            $q->whereIn('products.id', $productIdsInCat);
        })->orderBy('name')->get(['id', 'name', 'slug', 'hex_code']);

        $availableCharacteristics = \App\Models\Characteristic::whereHas('products', function($q) use ($productIdsInCat) {
            $q->whereIn('products.id', $productIdsInCat);
        })->orderBy('name')->get(['id', 'name', 'slug']);

        // Faixa de preço nesta categoria
        $minPriceInCat = (float) Product::where('category_id', $category->id)->min(DB::raw('COALESCE(promotional_price, price)')) ?: 0;
        $maxPriceInCat = (float) Product::where('category_id', $category->id)->max(DB::raw('COALESCE(promotional_price, price)')) ?: 0;

        $filterOptions = [
            'themes' => $availableThemes,
            'personalization_types' => $availablePersonalizations,
            'colors' => $availableColors,
            'characteristics' => $availableCharacteristics,
            'price_range' => [
                'min' => $minPriceInCat,
                'max' => $maxPriceInCat,
            ]
        ];

        // 2. Construir a query filtrada de produtos
        $query = Product::where('category_id', $category->id)
            ->with(['images', 'category', 'priceTiers']);

        // Filtrar por temas (usando slugs)
        if ($request->filled('themes')) {
            $selectedThemes = explode(',', $request->themes);
            $selectedThemes = array_filter(array_map('trim', $selectedThemes));
            if (!empty($selectedThemes)) {
                $query->whereHas('themes', function($q) use ($selectedThemes) {
                    $q->whereIn('slug', $selectedThemes);
                });
            }
        }

        // Filtrar por tipo de personalização (usando slugs)
        if ($request->filled('personalization_types')) {
            $selectedPers = explode(',', $request->personalization_types);
            $selectedPers = array_filter(array_map('trim', $selectedPers));
            if (!empty($selectedPers)) {
                $query->whereHas('personalizationTypes', function($q) use ($selectedPers) {
                    $q->whereIn('slug', $selectedPers);
                });
            }
        }

        // Filtrar por cores (usando slugs)
        if ($request->filled('colors')) {
            $selectedColors = explode(',', $request->colors);
            $selectedColors = array_filter(array_map('trim', $selectedColors));
            if (!empty($selectedColors)) {
                $query->whereHas('colors', function($q) use ($selectedColors) {
                    $q->whereIn('slug', $selectedColors);
                });
            }
        }

        // Filtrar por características (usando slugs)
        if ($request->filled('characteristics')) {
            $selectedChars = explode(',', $request->characteristics);
            $selectedChars = array_filter(array_map('trim', $selectedChars));
            if (!empty($selectedChars)) {
                $query->whereHas('characteristics', function($q) use ($selectedChars) {
                    $q->whereIn('slug', $selectedChars);
                });
            }
        }

        // Filtrar por preço mínimo e máximo considerando preço promocional se houver
        if ($request->filled('min_price')) {
            $minPrice = (float) $request->min_price;
            $query->where(function($q) use ($minPrice) {
                $q->where(function($sub) use ($minPrice) {
                    $sub->whereNotNull('promotional_price')->where('promotional_price', '>=', $minPrice);
                })->orWhere(function($sub) use ($minPrice) {
                    $sub->whereNull('promotional_price')->where('price', '>=', $minPrice);
                });
            });
        }
        if ($request->filled('max_price')) {
            $maxPrice = (float) $request->max_price;
            $query->where(function($q) use ($maxPrice) {
                $q->where(function($sub) use ($maxPrice) {
                    $sub->whereNotNull('promotional_price')->where('promotional_price', '<=', $maxPrice);
                })->orWhere(function($sub) use ($maxPrice) {
                    $sub->whereNull('promotional_price')->where('price', '<=', $maxPrice);
                });
            });
        }

        // Ordenação
        $sort = $request->query('sort', 'latest');
        $perPage = (int) $request->query('per_page', 12);
        
        if ($sort === 'price_asc') {
            $query->orderBy(DB::raw('COALESCE(promotional_price, price)'), 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy(DB::raw('COALESCE(promotional_price, price)'), 'desc');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } else {
            $query->latest();
        }

        $products = $query->paginate($perPage)->withQueryString();

        $storefrontData = $this->getStorefrontData();

        $selectedFilters = [
            'themes' => $request->filled('themes') ? explode(',', $request->themes) : [],
            'personalization_types' => $request->filled('personalization_types') ? explode(',', $request->personalization_types) : [],
            'colors' => $request->filled('colors') ? explode(',', $request->colors) : [],
            'characteristics' => $request->filled('characteristics') ? explode(',', $request->characteristics) : [],
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
        ];

        return Inertia::render('Storefront/Category', array_merge($storefrontData, [
            'category' => $category,
            'products' => $products,
            'currentSort' => $sort,
            'filterOptions' => $filterOptions,
            'selectedFilters' => $selectedFilters,
        ]));
    }

    /**
     * Exibe a página de busca pública de produtos.
     */
    public function search(Request $request): Response
    {
        $q = $request->query('q', '');

        // 1. Iniciar query de produtos
        $query = Product::query()->with(['images', 'category', 'priceTiers']);

        if (!empty($q)) {
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('meta_keywords', 'like', "%{$q}%")
                    ->orWhereHas('category', function($catQuery) use ($q) {
                        $catQuery->where('name', 'like', "%{$q}%");
                    });
            });
        }

        // Para carregar os filtros disponíveis para ESTA busca
        $productIds = (clone $query)->pluck('id');

        $availableThemes = \App\Models\Theme::whereHas('products', function($queryTheme) use ($productIds) {
            $queryTheme->whereIn('products.id', $productIds);
        })->orderBy('name')->get(['id', 'name', 'slug']);

        $availablePersonalizations = \App\Models\PersonalizationType::whereHas('products', function($queryPers) use ($productIds) {
            $queryPers->whereIn('products.id', $productIds);
        })->orderBy('name')->get(['id', 'name', 'slug']);

        $availableColors = \App\Models\Color::whereHas('products', function($queryColor) use ($productIds) {
            $queryColor->whereIn('products.id', $productIds);
        })->orderBy('name')->get(['id', 'name', 'slug', 'hex_code']);

        $availableCharacteristics = \App\Models\Characteristic::whereHas('products', function($queryChar) use ($productIds) {
            $queryChar->whereIn('products.id', $productIds);
        })->orderBy('name')->get(['id', 'name', 'slug']);

        // Faixa de preço nos produtos da busca
        $minPrice = (float) Product::whereIn('id', $productIds)->min(DB::raw('COALESCE(promotional_price, price)')) ?: 0;
        $maxPrice = (float) Product::whereIn('id', $productIds)->max(DB::raw('COALESCE(promotional_price, price)')) ?: 0;

        $filterOptions = [
            'themes' => $availableThemes,
            'personalization_types' => $availablePersonalizations,
            'colors' => $availableColors,
            'characteristics' => $availableCharacteristics,
            'price_range' => [
                'min' => $minPrice,
                'max' => $maxPrice,
            ]
        ];

        // 2. Aplicar os filtros selecionados se houver
        if ($request->filled('themes')) {
            $selectedThemes = explode(',', $request->themes);
            $selectedThemes = array_filter(array_map('trim', $selectedThemes));
            if (!empty($selectedThemes)) {
                $query->whereHas('themes', function($sub) use ($selectedThemes) {
                    $sub->whereIn('slug', $selectedThemes);
                });
            }
        }

        if ($request->filled('personalization_types')) {
            $selectedPers = explode(',', $request->personalization_types);
            $selectedPers = array_filter(array_map('trim', $selectedPers));
            if (!empty($selectedPers)) {
                $query->whereHas('personalizationTypes', function($sub) use ($selectedPers) {
                    $sub->whereIn('slug', $selectedPers);
                });
            }
        }

        if ($request->filled('colors')) {
            $selectedColors = explode(',', $request->colors);
            $selectedColors = array_filter(array_map('trim', $selectedColors));
            if (!empty($selectedColors)) {
                $query->whereHas('colors', function($sub) use ($selectedColors) {
                    $sub->whereIn('slug', $selectedColors);
                });
            }
        }

        if ($request->filled('characteristics')) {
            $selectedChars = explode(',', $request->characteristics);
            $selectedChars = array_filter(array_map('trim', $selectedChars));
            if (!empty($selectedChars)) {
                $query->whereHas('characteristics', function($sub) use ($selectedChars) {
                    $sub->whereIn('slug', $selectedChars);
                });
            }
        }

        if ($request->filled('min_price')) {
            $minVal = (float) $request->min_price;
            $query->where(function($sub) use ($minVal) {
                $sub->where(function($s) use ($minVal) {
                    $s->whereNotNull('promotional_price')->where('promotional_price', '>=', $minVal);
                })->orWhere(function($s) use ($minVal) {
                    $s->whereNull('promotional_price')->where('price', '>=', $minVal);
                });
            });
        }

        if ($request->filled('max_price')) {
            $maxVal = (float) $request->max_price;
            $query->where(function($sub) use ($maxVal) {
                $sub->where(function($s) use ($maxVal) {
                    $s->whereNotNull('promotional_price')->where('promotional_price', '<=', $maxVal);
                })->orWhere(function($s) use ($maxVal) {
                    $s->whereNull('promotional_price')->where('price', '<=', $maxVal);
                });
            });
        }

        // Ordenação
        $sort = $request->query('sort', 'latest');
        $perPage = (int) $request->query('per_page', 12);
        
        if ($sort === 'price_asc') {
            $query->orderBy(DB::raw('COALESCE(promotional_price, price)'), 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy(DB::raw('COALESCE(promotional_price, price)'), 'desc');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } else {
            $query->latest();
        }

        $products = $query->paginate($perPage)->withQueryString();
        $storefrontData = $this->getStorefrontData();

        $selectedFilters = [
            'themes' => $request->filled('themes') ? explode(',', $request->themes) : [],
            'personalization_types' => $request->filled('personalization_types') ? explode(',', $request->personalization_types) : [],
            'colors' => $request->filled('colors') ? explode(',', $request->colors) : [],
            'characteristics' => $request->filled('characteristics') ? explode(',', $request->characteristics) : [],
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
        ];

        return Inertia::render('Storefront/Search', array_merge($storefrontData, [
            'searchTerm' => $q,
            'products' => $products,
            'currentSort' => $sort,
            'currentPerPage' => $perPage,
            'filterOptions' => $filterOptions,
            'selectedFilters' => $selectedFilters,
        ]));
    }

    /**
     * Exibe uma página institucional/customizada pelo slug.
     */
    public function customPageShow(Page $page): Response
    {
        if (!$page->is_active) {
            abort(404);
        }

        $storefrontData = $this->getStorefrontData();

        return Inertia::render('Storefront/CustomPage', array_merge($storefrontData, [
            'page' => $page,
        ]));
    }

    /**
     * Exibe a página do carrinho de orçamento.
     */
    public function cart(): Response
    {
        $storefrontData = $this->getStorefrontData();

        return Inertia::render('Storefront/Cart', array_merge($storefrontData, [
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
        ]));
    }

    /**
     * Salva o orçamento criado pelo cliente.
     */
    public function storeQuote(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'required|string|max:255',
            'cep' => 'required|string|max:9',
            'address_street' => 'required|string|max:255',
            'address_neighborhood' => 'required|string|max:255',
            'address_city' => 'required|string|max:255',
            'address_state' => 'required|string|max:2',
            'customization_details' => 'nullable|string',
            'payment_method_id' => 'required|exists:payment_methods,id',
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

            // Encontra ou cria o cliente com base no e-mail
            $client = Client::where('contact_main', $request->client_email)->first();
            if (!$client) {
                $client = Client::create([
                    'name' => $request->client_name,
                    'contact_main' => $request->client_email,
                    'contact_secondary' => $request->client_phone,
                ]);
            } else {
                if (empty($client->contact_secondary)) {
                    $client->update(['contact_secondary' => $request->client_phone]);
                }
            }

            // Obtém os detalhes da forma de pagamento
            $paymentMethod = PaymentMethod::find($request->payment_method_id);
            $paymentTermsText = $paymentMethod ? $paymentMethod->name . ($paymentMethod->description ? " (" . $paymentMethod->description . ")" : "") : null;

            $newQuote = Quote::create([
                'unique_hash' => Str::random(16),
                'client_id' => $client->id,
                'user_id' => null, 
                'status' => 'Pendente',
                'total_amount' => $totalAmount,
                'customer_phone' => $request->client_phone,
                'cep' => $request->cep,
                'address_street' => $request->address_street,
                'address_neighborhood' => $request->address_neighborhood,
                'address_city' => $request->address_city,
                'address_state' => $request->address_state,
                'customization_details' => $request->customization_details,
                'payment_terms' => $paymentTermsText,
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

    /**
     * Gera o sitemap.xml dinamicamente.
     */
    public function sitemap()
    {
        $urls = [];
        $appUrl = url('/');

        // 1. Home
        $urls[] = [
            'loc' => $appUrl,
            'lastmod' => now()->tz('UTC')->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];

        // 2. Categorias
        $categories = Category::all();
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => route('storefront.category.show', ['category' => $category->slug]),
                'lastmod' => $category->updated_at ? $category->updated_at->tz('UTC')->toAtomString() : now()->tz('UTC')->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        }

        // 3. Produtos
        $products = Product::all();
        foreach ($products as $product) {
            $urls[] = [
                'loc' => route('storefront.product.show', ['product' => $product->slug]),
                'lastmod' => $product->updated_at ? $product->updated_at->tz('UTC')->toAtomString() : now()->tz('UTC')->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        }

        // 4. Páginas institucionais
        $pages = Page::where('is_active', true)->get();
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => route('storefront.page.show', ['page' => $page->slug]),
                'lastmod' => $page->updated_at ? $page->updated_at->tz('UTC')->toAtomString() : now()->tz('UTC')->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ];
        }

        // Monta o XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url['loc']) . '</loc>';
            $xml .= '<lastmod>' . $url['lastmod'] . '</lastmod>';
            $xml .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
            $xml .= '<priority>' . $url['priority'] . '</priority>';
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    /**
     * Gera o robots.txt dinamicamente.
     */
    public function robots()
    {
        $appUrl = Setting::where('key', 'app_domain')->value('value') ?: url('/');
        $appUrl = rtrim($appUrl, '/');

        $content = "User-agent: *\n";
        $content .= "Disallow: /dashboard\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Disallow: /profile\n";
        $content .= "Disallow: /orcamento/\n\n";
        $content .= "Sitemap: {$appUrl}/sitemap.xml\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain'
        ]);
    }
}