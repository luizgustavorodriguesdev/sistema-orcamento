<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Recebemos o cliente a ser editado como uma prop.
const props = defineProps({
    client: Object,
});

// Preenchemos o formulário com os dados do cliente recebido.
const form = useForm({
    name: props.client.name,
    contact_main: props.client.contact_main,
    contact_secondary: props.client.contact_secondary,
    address: props.client.address,
});

// Função para submeter as alterações.
const submit = () => {
    // Usamos o método 'put' para a rota 'clients.update'.
    form.put(route('clients.update', props.client.id));
};
</script>

<template>
    <Head title="Editar Cliente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Cliente: {{ client.name }}</h2>
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
                                    {{ form.processing ? 'A Atualizar...' : 'Atualizar Cliente' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
