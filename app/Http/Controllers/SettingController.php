<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    /**
     * Mostra a página de configurações.
     */
    public function index(): Response
    {
        // Busca todas as configurações e transforma-as num formato fácil de usar no Vue (ex: 'company_name' => 'Minha Loja').
        $settings = Setting::all()->pluck('value', 'key');

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Guarda as configurações na base de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        // Valida todos os campos que esperamos receber do formulário.
        $validatedData = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_cnpj' => 'nullable|string|max:255',
            'company_contact' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string|max:255',
            'company_city' => 'nullable|string|max:255',
            'company_state' => 'nullable|string|max:255',
            'company_zip' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:255',
            'company_whatsapp' => 'nullable|string|max:255',
            'company_observations' => 'nullable|string',
            'social_facebook' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_linkedin' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'seo_meta_title' => 'nullable|string|max:255',
            'seo_meta_description' => 'nullable|string|max:1000',
            'seo_meta_keywords' => 'nullable|string|max:255',
            'app_domain' => 'nullable|string|max:255',

            // Novos campos de Vantagens (1 a 4)
            'advantage_1_icon' => 'nullable|string|max:255',
            'advantage_1_title' => 'nullable|string|max:255',
            'advantage_1_subtitle' => 'nullable|string|max:255',
            'advantage_2_icon' => 'nullable|string|max:255',
            'advantage_2_title' => 'nullable|string|max:255',
            'advantage_2_subtitle' => 'nullable|string|max:255',
            'advantage_3_icon' => 'nullable|string|max:255',
            'advantage_3_title' => 'nullable|string|max:255',
            'advantage_3_subtitle' => 'nullable|string|max:255',
            'advantage_4_icon' => 'nullable|string|max:255',
            'advantage_4_title' => 'nullable|string|max:255',
            'advantage_4_subtitle' => 'nullable|string|max:255',

            // Novos campos de Banners Promocionais
            'promo_banner_1_link' => 'nullable|string|max:255',
            'promo_banner_2_link' => 'nullable|string|max:255',
            'promo_banner_1_image' => 'nullable|image|max:3072',
            'promo_banner_2_image' => 'nullable|image|max:3072',
        ]);

        // Processamento de Imagens para o Banner Promocional 1
        if ($request->hasFile('promo_banner_1_image')) {
            $oldPath = Setting::where('key', 'promo_banner_1_image_path')->value('value');
            if ($oldPath && \Storage::disk('public')->exists($oldPath)) {
                \Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('promo_banner_1_image')->store('promo_banners', 'public');
            Setting::updateOrCreate(['key' => 'promo_banner_1_image_path'], ['value' => $path]);
        }

        // Processamento de Imagens para o Banner Promocional 2
        if ($request->hasFile('promo_banner_2_image')) {
            $oldPath = Setting::where('key', 'promo_banner_2_image_path')->value('value');
            if ($oldPath && \Storage::disk('public')->exists($oldPath)) {
                \Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('promo_banner_2_image')->store('promo_banners', 'public');
            Setting::updateOrCreate(['key' => 'promo_banner_2_image_path'], ['value' => $path]);
        }

        // Remove os campos de ficheiro do array para não serem salvos como texto
        unset($validatedData['promo_banner_1_image']);
        unset($validatedData['promo_banner_2_image']);

        // Itera sobre cada dado validado e guarda-o na base de dados.
        foreach ($validatedData as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key], // Condição para encontrar o registo
                ['value' => $value ?? '']  // Valores para atualizar ou criar (aceita nulo como string vazia)
            );
        }

        return back()->with('success', 'Configurações guardadas com sucesso.');
    }
}
