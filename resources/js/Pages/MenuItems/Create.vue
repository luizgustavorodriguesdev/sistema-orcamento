<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
    products: Array,
    parentCandidates: Array,
});

const form = useForm({
    label: '',
    type: 'custom', // Default is custom
    parent_id: '',
    url: '',
    category_id: '',
    product_id: '',
    order: 0,
    is_active: true,
});

const submit = () => {
    // Clear unused fields before posting
    if (form.type !== 'custom') {
        form.url = '';
    }
    if (form.type !== 'category') {
        form.category_id = '';
    }
    if (form.type !== 'product') {
        form.product_id = '';
    }

    form.post(route('menu-items.store'));
};
</script>

<template>
    <Head title="Adicionar Item de Menu" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Adicionar Novo Item de Menu</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Rótulo -->
                            <div>
                                <label for="label" class="block font-medium text-sm text-gray-700">Rótulo do Menu (Texto Exibido)</label>
                                <input id="label" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.label" required autofocus />
                                <p v-if="form.errors.label" class="text-sm text-red-600 mt-2">{{ form.errors.label }}</p>
                            </div>

                            <!-- Tipo de Item -->
                            <div>
                                <label for="type" class="block font-medium text-sm text-gray-700">Tipo de Destino</label>
                                <select id="type" v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="custom">URL / Link Personalizado</option>
                                    <option value="category">Filtrar por Categoria</option>
                                    <option value="product">Link Direto para Produto</option>
                                </select>
                                <p v-if="form.errors.type" class="text-sm text-red-600 mt-2">{{ form.errors.type }}</p>
                            </div>

                            <!-- Opções dependendo do Tipo -->
                            <div v-if="form.type === 'custom'">
                                <label for="url" class="block font-medium text-sm text-gray-700">Link URL (Ex: /contato ou https://...)</label>
                                <input id="url" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.url" required />
                                <p v-if="form.errors.url" class="text-sm text-red-600 mt-2">{{ form.errors.url }}</p>
                            </div>

                            <div v-if="form.type === 'category'">
                                <label for="category_id" class="block font-medium text-sm text-gray-700">Selecionar Categoria</label>
                                <select id="category_id" v-model="form.category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Selecione uma Categoria...</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <p v-if="form.errors.category_id" class="text-sm text-red-600 mt-2">{{ form.errors.category_id }}</p>
                            </div>

                            <div v-if="form.type === 'product'">
                                <label for="product_id" class="block font-medium text-sm text-gray-700">Selecionar Produto</label>
                                <select id="product_id" v-model="form.product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Selecione um Produto...</option>
                                    <option v-for="prod in products" :key="prod.id" :value="prod.id">{{ prod.name }}</option>
                                </select>
                                <p v-if="form.errors.product_id" class="text-sm text-red-600 mt-2">{{ form.errors.product_id }}</p>
                            </div>

                            <!-- Item Pai (Hierarquia de Submenu) -->
                            <div>
                                <label for="parent_id" class="block font-medium text-sm text-gray-700">Item de Menu Pai (Opcional - para Submenu/Dropdown)</label>
                                <select id="parent_id" v-model="form.parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Nenhum (Item do Menu Principal)</option>
                                    <option v-for="parent in parentCandidates" :key="parent.id" :value="parent.id">{{ parent.label }}</option>
                                </select>
                                <p v-if="form.errors.parent_id" class="text-sm text-red-600 mt-2">{{ form.errors.parent_id }}</p>
                            </div>

                            <!-- Ordem e Status -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="order" class="block font-medium text-sm text-gray-700">Ordem de Exibição</label>
                                    <input id="order" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model.number="form.order" required />
                                    <p v-if="form.errors.order" class="text-sm text-red-600 mt-2">{{ form.errors.order }}</p>
                                </div>

                                <div class="flex items-center pt-6">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-blue-600 shadow-sm" />
                                        <span class="ms-2 text-sm text-gray-600">Item Ativo</span>
                                    </label>
                                    <p v-if="form.errors.is_active" class="text-sm text-red-600 mt-2">{{ form.errors.is_active }}</p>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="flex items-center justify-end space-x-4 border-t pt-4">
                                <Link :href="route('menu-items.index')" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded border">
                                    Cancelar
                                </Link>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ form.processing ? 'A Guardar...' : 'Guardar Item de Menu' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
