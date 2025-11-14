<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    quotes: Object,
    settings: Object, // Recebemos as configurações aqui
});

const copiedQuoteId = ref(null); // Variável para controlar o feedback "Copiado!"

// Função para copiar o link do orçamento
const copyLink = (quote) => {
    // Usa o domínio das configurações ou o domínio atual como fallback
    const domain = props.settings.app_domain || window.location.origin;
    const url = `${domain}/orcamento/${quote.unique_hash}`;

    // Usa a API do navegador para copiar o texto para a área de transferência
    navigator.clipboard.writeText(url).then(() => {
        copiedQuoteId.value = quote.id; // Ativa o feedback visual
        // Remove o feedback após 2 segundos
        setTimeout(() => {
            copiedQuoteId.value = null;
        }, 2000);
    }).catch(err => {
        console.error('Erro ao copiar o link: ', err);
        alert('Não foi possível copiar o link.');
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('pt-BR', options);
};

const statusClass = (status) => {
    switch (status) {
        case 'Aprovado': return 'bg-green-100 text-green-800';
        case 'Cancelado': return 'bg-red-100 text-red-800';
        default: return 'bg-yellow-100 text-yellow-800';
    }
};
</script>

<template>
    <Head title="Orçamentos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestão de Orçamentos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold">Lista de Orçamentos</h3>
                            <Link :href="route('quotes.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Criar Orçamento
                            </Link>
                        </div>

                        <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                        </div>

                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">ID</th>
                                        <th scope="col" class="px-6 py-3">Cliente</th>
                                        <th scope="col" class="px-6 py-3">Vendedor</th>
                                        <th scope="col" class="px-6 py-3">Valor</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="quote in quotes.data" :key="quote.id" class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-bold text-gray-700">#{{ String(quote.id).padStart(4, '0') }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ quote.client ? quote.client.name : 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ quote.user ? quote.user.name : 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ formatCurrency(quote.total_amount) }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(quote.status)">
                                                {{ quote.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 flex items-center space-x-4">
                                            <Link :href="route('quotes.edit', quote.id)" class="font-medium text-blue-600 hover:underline">Editar</Link>
                                            <button @click="copyLink(quote)" class="font-medium text-green-600 hover:underline">
                                                <span v-if="copiedQuoteId === quote.id">Copiado!</span>
                                                <span v-else>Copiar Link</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="quotes.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            Nenhum orçamento encontrado.
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
