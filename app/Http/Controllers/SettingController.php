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
            'app_domain' => 'nullable|string|max:255',
        ]);

        // Itera sobre cada dado validado e guarda-o na base de dados.
        foreach ($validatedData as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key], // Condição para encontrar o registo
                ['value' => $value]  // Valores para atualizar ou criar
            );
        }

        return back()->with('success', 'Configurações guardadas com sucesso.');
    }
}
