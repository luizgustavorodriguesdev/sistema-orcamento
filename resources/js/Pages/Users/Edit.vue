<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Recebemos o utilizador a ser editado como uma prop.
const props = defineProps({
    user: Object,
});

// Preenchemos o formulário com os dados do utilizador recebido.
// Deixamos os campos de palavra-passe em branco por segurança.
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    password: '',
    password_confirmation: '',
});

// Função para submeter as alterações.
const submit = () => {
    // Usamos o método 'put' para a rota 'users.update'.
    form.put(route('users.update', props.user.id), {
        // Preserva o estado do scroll após a submissão.
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Editar Utilizador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Utilizador: {{ user.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <form @submit.prevent="submit">
                            <div>
                                <label for="name">Nome</label>
                                <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.name" required />
                                <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
                            </div>
                            <div class="mt-4">
                                <label for="email">Email</label>
                                <input id="email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.email" required />
                                <p v-if="form.errors.email" class="text-sm text-red-600 mt-2">{{ form.errors.email }}</p>
                            </div>
                            <div class="mt-4">
                                <label for="role">Cargo</label>
                                <select id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.role" required>
                                    <option value="vendedor">Vendedor</option>
                                    <option value="admin">Administrador</option>
                                </select>
                                <p v-if="form.errors.role" class="text-sm text-red-600 mt-2">{{ form.errors.role }}</p>
                            </div>

                            <div class="mt-6 pt-6 border-t">
                                <p class="text-sm text-gray-600">Preencha os campos abaixo apenas se desejar alterar a palavra-passe.</p>
                            </div>
                            <div class="mt-4">
                                <label for="password">Nova Palavra-passe</label>
                                <input id="password" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.password" />
                                <p v-if="form.errors.password" class="text-sm text-red-600 mt-2">{{ form.errors.password }}</p>
                            </div>
                            <div class="mt-4">
                                <label for="password_confirmation">Confirmar Nova Palavra-passe</label>
                                <input id="password_confirmation" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.password_confirmation" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ form.processing ? 'A Atualizar...' : 'Atualizar Utilizador' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
