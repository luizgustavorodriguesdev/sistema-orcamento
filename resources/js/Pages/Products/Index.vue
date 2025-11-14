<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// O controller do Laravel passa a prop 'products' para este componente
const props = defineProps({
    products: Object,
});

// Função para formatar o preço para o padrão brasileiro
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};
</script>

<template>
    <Head title="Produtos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestão de Produtos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div class="flex justify-between mb-6">
                            <h3 class="text-2xl font-bold">Lista de Produtos</h3>
                            <!-- O componente Link do Inertia cria uma navegação rápida, sem recarregar a página -->
                            <Link :href="route('products.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Adicionar Produto
                            </Link>
                        </div>

                        <!-- CORREÇÃO: Adicionado o operador '?' para verificar se 'flash' existe -->
                        <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                        </div>

                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Nome</th>
                                        <th scope="col" class="px-6 py-3">Preço</th>
                                        <th scope="col" class="px-6 py-3">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Loop para exibir cada produto -->
                                    <tr v-for="product in products.data" :key="product.id" class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ product.name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ formatCurrency(product.price) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <Link :href="route('products.edit', product.id)" class="font-medium text-blue-600 hover:underline mr-4">Editar</Link>
                                            <!-- O método 'delete' faz a requisição correta para a rota de exclusão -->
                                            <Link :href="route('products.destroy', product.id)" method="delete" as="button" class="font-medium text-red-600 hover:underline" preserve-scroll>Deletar</Link>
                                        </td>
                                    </tr>
                                    <!-- Mensagem para quando não houver produtos -->
                                    <tr v-if="products.data.length === 0">
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                            Nenhum produto encontrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Links de Paginação (CORRIGIDO) -->
                        <div class="mt-4 flex justify-center">
                            <div class="flex">
                                <template v-for="(link, key) in products.links" :key="key">
                                    <!-- Renderiza um Link clicável se link.url existir -->
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-4 py-2 mx-1 text-sm rounded-md"
                                        :class="{ 'bg-blue-500 text-white': link.active, 'text-gray-700 bg-white hover:bg-gray-100': !link.active }"
                                    />
                                    <!-- Renderiza um span não clicável se link.url for nulo -->
                                    <span
                                        v-else
                                        v-html="link.label"
                                        class="px-4 py-2 mx-1 text-sm rounded-md text-gray-400 cursor-not-allowed"
                                    />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
