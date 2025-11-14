<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Mostra uma lista de todos os produtos.
     */
    public function index(): Response
    {
        // Carregamos o nome da categoria junto com cada produto para a listagem
        $products = Product::with('category')->latest()->paginate(10);

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Mostra o formulário para criar um novo produto.
     */
    public function create(): Response
    {
        return Inertia::render('Products/Create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Salva um novo produto na base de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'promotional_price' => 'nullable|numeric|min:0|lt:price',
            'category_id' => 'nullable|exists:categories,id',
            'main_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',            
            'price_tiers' => 'nullable|array',
            'price_tiers.*.min_quantity' => 'required|integer|min:1',
            'price_tiers.*.price' => 'required|numeric|min:0',
        ]);


        // Usamos uma transação para garantir que, se algo falhar, nada seja salvo.
      DB::transaction(function () use ($request, $validated) {
          // Gera o slug a partir do nome
          $validated['slug'] = Str::slug($validated['name']);
          $product = Product::create($validated);

            // Salva as escalas de preços, se houver
            if (!empty($validated['price_tiers'])) {
                foreach ($validated['price_tiers'] as $tier) {
                    $product->priceTiers()->create($tier);
                }
            }

            // Salva a imagem principal, se houver
            if ($request->hasFile('main_image')) {
                $path = $request->file('main_image')->store('products', 'public');
                $product->images()->create(['path' => $path, 'is_main' => true]);
            }

            // Salva as imagens da galeria, se houver
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $path = $file->store('products', 'public');
                    $product->images()->create(['path' => $path, 'is_main' => false]);
                }
            }
        });

        return redirect()->route('products.index')
                         ->with('success', 'Produto criado com sucesso.');
    }

    /**
     * Mostra o formulário para editar um produto existente.
     */
    public function edit(Product $product): Response
    {
        $product->load('images', 'category','priceTiers');

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Atualiza um produto existente na base de dados.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'promotional_price' => 'nullable|numeric|min:0|lt:price',
            'category_id' => 'nullable|exists:categories,id',
            'main_image' => 'nullable|image',
            'gallery_images.*' => 'nullable|image',
            // Validação para as escalas de preços
            'price_tiers' => 'nullable|array',
            'price_tiers.*.min_quantity' => 'required|integer|min:1',
            'price_tiers.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $product, $validated) {
            // Gera o slug a partir do nome
            $validated['slug'] = Str::slug($validated['name']);
            $product->update($validated);

            // Apaga as escalas de preços antigas e cria as novas
            $product->priceTiers()->delete();
            if (!empty($validated['price_tiers'])) {
                foreach ($validated['price_tiers'] as $tier) {
                    $product->priceTiers()->create($tier);
                }
            }

            // Lida com a nova imagem principal
            if ($request->hasFile('main_image')) {
                // Apaga a imagem principal antiga, se existir
                $oldMainImage = $product->images()->where('is_main', true)->first();
                if ($oldMainImage) {
                    Storage::disk('public')->delete($oldMainImage->path);
                    $oldMainImage->delete();
                }
                // Guarda a nova imagem principal
                $path = $request->file('main_image')->store('products', 'public');
                $product->images()->create(['path' => $path, 'is_main' => true]);
            }

            // Lida com as novas imagens da galeria
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $path = $file->store('products', 'public');
                    $product->images()->create(['path' => $path, 'is_main' => false]);
                }
            }
        });

        return redirect()->route('products.index')->with('success', 'Produto atualizado com sucesso.');
    }

    /**
     * Remove uma imagem de produto específica.
     */
    public function destroyImage(ProductImage $productImage): RedirectResponse
    {
        // Apaga o ficheiro do disco
        Storage::disk('public')->delete($productImage->path);
        // Apaga o registo da base de dados
        $productImage->delete();

        return back()->with('success', 'Imagem apagada com sucesso.');
    }

     /**
     * Remove um produto da base de dados.
     */
    public function destroy(Product $product): RedirectResponse
    {
        // Apaga as imagens do disco antes de apagar o produto
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return redirect()->route('products.index')
                         ->with('success', 'Produto apagado com sucesso.');
    }
}
