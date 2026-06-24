<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    menuItems: Array,
});

const getDestination = (item) => {
    if (item.type === 'category') {
        return `Categoria: ${item.category ? item.category.name : 'N/A'}`;
    }
    if (item.type === 'product') {
        return `Produto: ${item.product ? item.product.name : 'N/A'}`;
    }
    return `URL: ${item.url || '/'}`;
};
</script>

<template>
    <Head title="Gerenciamento de Menus" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestão de Menus da Vitrine</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold">Lista de Itens do Menu</h3>
                            <Link :href="route('menu-items.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Adicionar Item de Menu
                            </Link>
                        </div>

                        <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                        </div>

                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Rótulo (Label)</th>
                                        <th scope="col" class="px-6 py-3">Tipo</th>
                                        <th scope="col" class="px-6 py-3">Destino</th>
                                        <th scope="col" class="px-6 py-3">Item Pai</th>
                                        <th scope="col" class="px-6 py-3">Ordem</th>
                                        <th scope="col" class="px-6 py-3">Ativo</th>
                                        <th scope="col" class="px-6 py-3">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in menuItems" :key="item.id" class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                            <span v-if="item.parent_id" class="text-gray-400 mr-1">—</span>
                                            {{ item.label }}
                                        </td>
                                        <td class="px-6 py-4 capitalize">{{ item.type }}</td>
                                        <td class="px-6 py-4">{{ getDestination(item) }}</td>
                                        <td class="px-6 py-4">
                                            <span v-if="item.parent" class="text-sm font-medium text-gray-600 bg-gray-100 px-2 py-0.5 rounded">{{ item.parent.label }}</span>
                                            <span v-else class="text-xs text-gray-400">Nenhum (Menu Principal)</span>
                                        </td>
                                        <td class="px-6 py-4">{{ item.order }}</td>
                                        <td class="px-6 py-4">
                                            <span v-if="item.is_active" class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-bold shadow-sm">Ativo</span>
                                            <span v-else class="px-2 py-1 rounded bg-red-100 text-red-800 text-xs font-bold shadow-sm">Inativo</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <Link :href="route('menu-items.edit', item.id)" class="font-medium text-blue-600 hover:underline mr-4">Editar</Link>
                                            <Link :href="route('menu-items.destroy', item.id)" method="delete" as="button" class="font-medium text-red-600 hover:underline" preserve-scroll>Apagar</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="menuItems.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            Nenhum item de menu cadastrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
