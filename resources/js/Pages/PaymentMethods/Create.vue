<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Usamos o 'useForm' para gerir os dados do formulário.
const form = useForm({
    name: '',

    description: '',
    is_active: true, // O valor padrão será 'Ativo'
});

// Função de submissão do formulário.
const submit = () => {
    form.post(route('payment-methods.store'));
};
</script>

<template>
    <Head title="Adicionar Forma de Pagamento" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Adicionar Nova Forma de Pagamento</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm-rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <form @submit.prevent="submit">
                            <!-- Campo Nome -->
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Nome (Ex: PIX à vista)</label>
                                <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.name" required autofocus />
                                <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
                            </div>

                            <!-- Campo Descrição -->
                            <div class="mt-4">
                                <label for="description" class="block font-medium text-sm text-gray-700">Descrição (Ex: Chave PIX: email@exemplo.com)</label>
                                <textarea id="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.description" required></textarea>
                                <p v-if="form.errors.description" class="text-sm text-red-600 mt-2">{{ form.errors.description }}</p>
                            </div>

                            <!-- Campo Status -->
                            <div class="mt-4">
                                <label for="is_active" class="block font-medium text-sm text-gray-700">Status</label>
                                <select id="is_active" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.is_active">
                                    <option :value="true">Ativo</option>
                                    <option :value="false">Inativo</option>
                                </select>
                                <p v-if="form.errors.is_active" class="text-sm text-red-600 mt-2">{{ form.errors.is_active }}</p>
                            </div>

                            <!-- Botão de Submissão -->
                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ form.processing ? 'A Guardar...' : 'Guardar Forma de Pagamento' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
