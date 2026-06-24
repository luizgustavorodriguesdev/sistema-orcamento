<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    /**
     * Exibe o gerenciador de mídias no painel administrativo.
     */
    public function index(Request $request): Response
    {
        $query = Media::latest();

        if ($request->filled('q')) {
            $query->where('filename', 'like', '%' . $request->q . '%')
                  ->orWhere('title', 'like', '%' . $request->q . '%');
        }

        return Inertia::render('Media/Index', [
            'mediaItems' => $query->paginate(18)->withQueryString(),
            'filters' => $request->only(['q']),
        ]);
    }

    /**
     * Retorna mídias em formato JSON para o modal de seleção do editor.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = Media::latest();

        if ($request->filled('q')) {
            $query->where('filename', 'like', '%' . $request->q . '%')
                  ->orWhere('title', 'like', '%' . $request->q . '%')
                  ->orWhere('alt_text', 'like', '%' . $request->q . '%');
        }

        return response()->json($query->paginate(24));
    }

    /**
     * Carrega um novo arquivo de mídia e cria o registro com metadados de SEO padrão.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|image|max:5120', // máximo de 5MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            
            // Armazena no disco publico
            $path = $file->store('media', 'public');
            
            // Limpa o nome do arquivo para usar como SEO default (ex: caneca-azul.jpg -> Caneca Azul)
            $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            $seoDefault = ucwords(str_replace(['-', '_'], ' ', $nameWithoutExt));

            Media::create([
                'filename' => $originalName,
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'title' => $seoDefault,
                'alt_text' => $seoDefault,
                'caption' => '',
                'description' => '',
            ]);
        }

        return back()->with('success', 'Mídia enviada com sucesso.');
    }

    /**
     * Atualiza os campos de SEO de um arquivo de mídia.
     */
    public function update(Request $request, Media $medium): RedirectResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $medium->update($request->only(['title', 'alt_text', 'caption', 'description']));

        return back()->with('success', 'SEO da mídia atualizado com sucesso.');
    }

    /**
     * Exclui o arquivo físico e o registro do banco de dados.
     */
    public function destroy(Media $medium): RedirectResponse
    {
        // Exclui o arquivo no storage
        if (Storage::disk('public')->exists($medium->path)) {
            Storage::disk('public')->delete($medium->path);
        }

        $medium->delete();

        return back()->with('success', 'Mídia removida com sucesso.');
    }
}
