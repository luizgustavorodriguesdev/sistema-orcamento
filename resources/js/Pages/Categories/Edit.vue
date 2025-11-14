<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Recebemos a categoria a ser editada como uma prop.
const props = defineProps({
    category: Object,
});

// Preenchemos o formulário com os dados existentes da categoria.
const form = useForm({
    name: props.category.name,
    description: props.category.description,
});

// Função para submeter as alterações.
const submit = () => {
    // Usamos o método 'put' para a rota de atualização.
    form.put(route('categories.update', props.category.id));
};
</script>

<template>
    <Head title="Editar Categoria" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Categoria</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <form @submit.prevent="submit">
                            <!-- Campo Nome -->
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Nome da Categoria</label>
                                <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.name" required autofocus />
                                <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
                            </div>

                            <!-- Campo Descrição -->
                            <div class="mt-4">
                                <label for="description" class="block font-medium text-sm text-gray-700">Descrição (Opcional)</label>
                                <textarea id="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.description"></textarea>
                                <p v-if="form.errors.description" class="text-sm text-red-600 mt-2">{{ form.errors.description }}</p>
                            </div>

                            <!-- Botão de Submissão -->
                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ form.processing ? 'A Atualizar...' : 'Atualizar Categoria' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
