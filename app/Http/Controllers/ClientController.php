<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ClientController extends Controller
{
    /**
     * Mostra uma lista de todos os clientes.
     */
    public function index(): Response
    {
        return Inertia::render('Clients/Index', [
            'clients' => Client::latest()->paginate(10),
        ]);
    }

    /**
     * Mostra o formulário para criar um novo cliente.
     */
    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    /**
     * Guarda um novo cliente na base de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_main' => 'required|string|max:255',
            'contact_secondary' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        Client::create($request->all());

        return back()->with('success', 'Cliente criado com sucesso.');
    }

    /**
     * Mostra o formulário para editar um cliente existente.
     */
    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * Atualiza um cliente existente na base de dados.
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_main' => 'required|string|max:255',
            'contact_secondary' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $client->update($request->all());

        return redirect()->route('clients.index')->with('success', 'Cliente atualizado com sucesso.');
    }

    /**
     * Remove um cliente da base de dados.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Cliente apagado com sucesso.');
    }
}
