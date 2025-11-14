<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Usamos o 'useForm' para gerir os dados do novo cliente.
const form = useForm({
    name: '',
    contact_main: '',
    contact_secondary: '',
    address: '',
});

// Função de submissão do formulário.
const submit = () => {
    // Enviamos os dados para a rota 'clients.store'.
    form.post(route('clients.store'));
};
</script>

<template>
    <Head title="Adicionar Cliente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Adicionar Novo Cliente</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <form @submit.prevent="submit">
                            <!-- Campo Nome -->
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Nome Completo</label>
                                <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.name" required autofocus />
                                <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
                            </div>

                            <!-- Campo Contacto Principal -->
                            <div class="mt-4">
                                <label for="contact_main" class="block font-medium text-sm text-gray-700">Contacto Principal (Email ou Telefone)</label>
                                <input id="contact_main" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.contact_main" required />
                                <p v-if="form.errors.contact_main" class="text-sm text-red-600 mt-2">{{ form.errors.contact_main }}</p>
                            </div>

                            <!-- Campo Contacto Secundário -->
                            <div class="mt-4">
                                <label for="contact_secondary" class="block font-medium text-sm text-gray-700">Contacto Secundário (Opcional)</label>
                                <input id="contact_secondary" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.contact_secondary" />
                                <p v-if="form.errors.contact_secondary" class="text-sm text-red-600 mt-2">{{ form.errors.contact_secondary }}</p>
                            </div>

                            <!-- Campo Endereço -->
                            <div class="mt-4">
                                <label for="address" class="block font-medium text-sm text-gray-700">Endereço (Opcional)</label>
                                <textarea id="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.address"></textarea>
                                <p v-if="form.errors.address" class="text-sm text-red-600 mt-2">{{ form.errors.address }}</p>
                            </div>

                            <!-- Botão de Submissão -->
                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ form.processing ? 'A Guardar...' : 'Guardar Cliente' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
