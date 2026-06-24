<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recent_quotes: Array,
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(val || 0);
};
</script>

<template>
    <Head title="Dashboard Geral" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-black text-gray-800 dark:text-gray-200">Visão Geral</h2>
        </template>

        <div class="space-y-8">
            
            <!-- Cards de Estatísticas Principais -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Faturamento Aprovado -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center justify-between group hover:border-emerald-200 transition-all hover:shadow-md">
                    <div class="space-y-1">
                        <span class="text-xxs font-black uppercase text-slate-400 tracking-wider">Faturamento Aprovado</span>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ formatCurrency(stats.approved_amount) }}</h3>
                        <p class="text-xxs text-emerald-600 font-bold flex items-center gap-0.5 mt-1">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ stats.approved_quotes }} orçamentos fechados
                        </p>
                    </div>
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-500 rounded-2xl group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1m-4-6h8" /></svg>
                    </div>
                </div>

                <!-- Orçamentos Pendentes -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center justify-between group hover:border-amber-200 transition-all hover:shadow-md">
                    <div class="space-y-1">
                        <span class="text-xxs font-black uppercase text-slate-400 tracking-wider">Aguardando Análise</span>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ stats.pending_quotes }}</h3>
                        <p class="text-xxs text-amber-600 font-bold flex items-center gap-0.5 mt-1">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Orçamentos pendentes
                        </p>
                    </div>
                    <div class="p-4 bg-amber-50 dark:bg-amber-950/30 text-amber-500 rounded-2xl group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>

                <!-- Total de Orçamentos -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center justify-between group hover:border-blue-200 transition-all hover:shadow-md">
                    <div class="space-y-1">
                        <span class="text-xxs font-black uppercase text-slate-400 tracking-wider">Total de Orçamentos</span>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ stats.total_quotes }}</h3>
                        <p class="text-xxs text-blue-600 font-bold mt-1">Registros totais no sistema</p>
                    </div>
                    <div class="p-4 bg-blue-50 dark:bg-blue-950/30 text-blue-500 rounded-2xl group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                </div>

                <!-- Clientes Cadastrados -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center justify-between group hover:border-purple-200 transition-all hover:shadow-md">
                    <div class="space-y-1">
                        <span class="text-xxs font-black uppercase text-slate-400 tracking-wider">Clientes Ativos</span>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ stats.total_clients }}</h3>
                        <p class="text-xxs text-purple-600 font-bold mt-1">Base de dados unificada</p>
                    </div>
                    <div class="p-4 bg-purple-50 dark:bg-purple-950/30 text-purple-500 rounded-2xl group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                </div>
            </div>

            <!-- Dashboard Body: Grid Principal -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Coluna Tabela de Orçamentos (Tamanho 2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                        <!-- Header do Card -->
                        <header class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                            <div class="space-y-0.5">
                                <h3 class="text-sm font-black text-slate-800 dark:text-white">Últimos Orçamentos Recebidos</h3>
                                <p class="text-xxs text-slate-400">Listagem dos 5 orçamentos mais recentes enviados por clientes.</p>
                            </div>
                            <Link :href="route('quotes.index')" class="text-blue-500 hover:text-blue-700 font-extrabold text-xs transition-colors flex items-center gap-1">
                                <span>Ver Todos</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </Link>
                        </header>

                        <!-- Tabela -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                                <thead class="text-xxs uppercase bg-slate-50 dark:bg-gray-900 border-b border-slate-100 dark:border-slate-700 text-slate-400 font-black">
                                    <tr>
                                        <th class="px-6 py-4">Cliente</th>
                                        <th class="px-6 py-4">Data</th>
                                        <th class="px-6 py-4">Valor Total</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4 text-right">Ação</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="quote in recent_quotes" :key="quote.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">{{ quote.client_name || 'Cliente Indefinido' }}</td>
                                        <td class="px-6 py-4 text-slate-500">{{ quote.created_at }}</td>
                                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">{{ formatCurrency(quote.total_amount) }}</td>
                                        <td class="px-6 py-4">
                                            <span 
                                                class="px-2 py-1 rounded-full text-xxs font-extrabold shadow-sm"
                                                :class="{
                                                    'bg-green-50 text-green-700 border border-green-200': quote.status === 'Aprovado',
                                                    'bg-amber-50 text-amber-700 border border-amber-200': quote.status === 'Pendente',
                                                    'bg-slate-50 text-slate-700 border border-slate-200': quote.status === 'Cancelado'
                                                }"
                                            >
                                                {{ quote.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <Link :href="route('quotes.edit', quote.id)" class="text-blue-500 hover:text-blue-700 font-extrabold hover:underline">
                                                Visualizar
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="recent_quotes.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            Nenhum orçamento encontrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Coluna Resumo e Atalhos (Tamanho 1/3) -->
                <div class="space-y-6">
                    <!-- Outros Indicadores -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 space-y-4">
                        <h4 class="text-sm font-black text-slate-800 dark:text-white border-b pb-3">Informações do Catálogo</h4>
                        
                        <div class="flex justify-between items-center py-1">
                            <span class="text-xs text-slate-400 font-bold">Total de Produtos</span>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white rounded-lg font-black text-xs">{{ stats.total_products }}</span>
                                <Link :href="route('products.index')" class="text-blue-500 hover:text-blue-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></Link>
                            </div>
                        </div>

                        <div class="flex justify-between items-center py-1 border-t border-slate-50 dark:border-slate-700">
                            <span class="text-xs text-slate-400 font-bold">Total de Categorias</span>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white rounded-lg font-black text-xs">{{ stats.total_categories }}</span>
                                <Link :href="route('categories.index')" class="text-blue-500 hover:text-blue-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></Link>
                            </div>
                        </div>
                    </div>

                    <!-- Atalhos Rápidos -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 space-y-4">
                        <h4 class="text-sm font-black text-slate-800 dark:text-white border-b pb-3">Atalhos Comuns</h4>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <Link :href="route('products.create')" class="bg-slate-50 hover:bg-blue-50 dark:bg-slate-700/50 dark:hover:bg-slate-700 rounded-2xl p-4 text-center border border-slate-100 dark:border-slate-700 transition-all group">
                                <div class="p-2 bg-blue-50 dark:bg-slate-800 text-blue-500 rounded-xl w-fit mx-auto mb-2 group-hover:scale-110 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </div>
                                <span class="text-xxs font-black text-slate-700 dark:text-slate-200">Novo Produto</span>
                            </Link>

                            <Link :href="route('pages.create')" class="bg-slate-50 hover:bg-blue-50 dark:bg-slate-700/50 dark:hover:bg-slate-700 rounded-2xl p-4 text-center border border-slate-100 dark:border-slate-700 transition-all group">
                                <div class="p-2 bg-blue-50 dark:bg-slate-800 text-blue-500 rounded-xl w-fit mx-auto mb-2 group-hover:scale-110 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                </div>
                                <span class="text-xxs font-black text-slate-700 dark:text-slate-200">Criar Página</span>
                            </Link>

                            <Link :href="route('media.index')" class="bg-slate-50 hover:bg-blue-50 dark:bg-slate-700/50 dark:hover:bg-slate-700 rounded-2xl p-4 text-center border border-slate-100 dark:border-slate-700 transition-all group">
                                <div class="p-2 bg-blue-50 dark:bg-slate-800 text-blue-500 rounded-xl w-fit mx-auto mb-2 group-hover:scale-110 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <span class="text-xxs font-black text-slate-700 dark:text-slate-200">Mídias (SEO)</span>
                            </Link>

                            <Link :href="route('settings.index')" class="bg-slate-50 hover:bg-blue-50 dark:bg-slate-700/50 dark:hover:bg-slate-700 rounded-2xl p-4 text-center border border-slate-100 dark:border-slate-700 transition-all group">
                                <div class="p-2 bg-blue-50 dark:bg-slate-800 text-blue-500 rounded-xl w-fit mx-auto mb-2 group-hover:scale-110 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <span class="text-xxs font-black text-slate-700 dark:text-slate-200">Ajustes</span>
                            </Link>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.text-xxs {
    font-size: 0.65rem;
}
</style>
