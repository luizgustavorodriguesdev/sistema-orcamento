<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PaymentMethodController extends Controller
{
    /**
     * Mostra uma lista de todas as formas de pagamento.
     */
    public function index(): Response
    {
        return Inertia::render('PaymentMethods/Index', [
            'paymentMethods' => PaymentMethod::latest()->paginate(10),
        ]);
    }

    /**
     * Mostra o formulário para criar uma nova forma de pagamento.
     */
    public function create(): Response
    {
        return Inertia::render('PaymentMethods/Create');
    }

    /**
     * Guarda uma nova forma de pagamento na base de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        PaymentMethod::create($request->all());

        return redirect()->route('payment-methods.index')->with('success', 'Forma de pagamento criada com sucesso.');
    }

    /**
     * Mostra o formulário para editar uma forma de pagamento existente.
     */
    public function edit(PaymentMethod $paymentMethod): Response
    {
        return Inertia::render('PaymentMethods/Edit', [
            'paymentMethod' => $paymentMethod,
        ]);
    }

    /**
     * Atualiza uma forma de pagamento existente na base de dados.
     */
    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        $paymentMethod->update($request->all());

        return redirect()->route('payment-methods.index')->with('success', 'Forma de pagamento atualizada com sucesso.');
    }

    /**
     * Remove uma forma de pagamento da base de dados.
     */
    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->delete();

        return redirect()->route('payment-methods.index')->with('success', 'Forma de pagamento apagada com sucesso.');
    }
}
