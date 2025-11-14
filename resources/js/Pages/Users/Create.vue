<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Usamos o 'useForm' para gerir os dados do novo utilizador.
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '', // Campo de confirmação para a palavra-passe
    role: 'vendedor', // Valor padrão para o cargo
});

// Função de submissão do formulário.
const submit = () => {
    // Enviamos os dados para a rota 'users.store'.
    form.post(route('users.store'), {
        // Limpa os campos de palavra-passe após a submissão.
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Adicionar Utilizador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Adicionar Novo Utilizador</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <form @submit.prevent="submit">
                            <!-- Campo Nome -->
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Nome</label>
                                <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.name" required autofocus />
                                <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
                            </div>

                            <!-- Campo Email -->
                            <div class="mt-4">
                                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                                <input id="email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.email" required />
                                <p v-if="form.errors.email" class="text-sm text-red-600 mt-2">{{ form.errors.email }}</p>
                            </div>

                            <!-- Campo Cargo (Role) -->
                            <div class="mt-4">
                                <label for="role" class="block font-medium text-sm text-gray-700">Cargo</label>
                                <select id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.role" required>
                                    <option value="vendedor">Vendedor</option>
                                    <option value="admin">Administrador</option>
                                </select>
                                <p v-if="form.errors.role" class="text-sm text-red-600 mt-2">{{ form.errors.role }}</p>
                            </div>

                            <!-- Campo Palavra-passe -->
                            <div class="mt-4">
                                <label for="password" class="block font-medium text-sm text-gray-700">Palavra-passe</label>
                                <input id="password" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.password" required />
                                <p v-if="form.errors.password" class="text-sm text-red-600 mt-2">{{ form.errors.password }}</p>
                            </div>

                            <!-- Campo Confirmação de Palavra-passe -->
                            <div class="mt-4">
                                <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirmar Palavra-passe</label>
                                <input id="password_confirmation" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.password_confirmation" required />
                                <p v-if="form.errors.password_confirmation" class="text-sm text-red-600 mt-2">{{ form.errors.password_confirmation }}</p>
                            </div>

                            <!-- Botão de Submissão -->
                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ form.processing ? 'A Guardar...' : 'Guardar Utilizador' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
