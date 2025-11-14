<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;



class UserController extends Controller
{
    /**
     * Mostra uma lista de todos os utilizadores.
     */
    public function index():Response
    {
        return Inertia::render('Users/Index', [
            'users' => User::latest()->paginate(10),
        ]);
    }

    /**
     * Mostra o formulário para criar um novo utilizador.
     */
    public function create()
    {
        return Inertia::render('Users/Create');
    }

    /**
     * Guarda um novo utilizador na base de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(['admin', 'vendedor'])],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Utilizador criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Mostra o formulário para editar um utilizador existente.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Atualiza um utilizador existente na base de dados.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'vendedor'])],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // Atualiza a palavra-passe apenas se uma nova for fornecida.
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Utilizador atualizado com sucesso.');
    }

    /**
     * Remove um utilizador da base de dados.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Medida de segurança para impedir que um utilizador se apague a si mesmo.
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Não pode apagar a sua própria conta.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilizador apagado com sucesso.');
    }
}
