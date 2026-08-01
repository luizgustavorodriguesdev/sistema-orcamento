<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    orders: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'Rascunho': 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        'Aprovado': 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'Recebido_Parcial': 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'Recebido': 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'Cancelado': 'bg-rose-500/10 text-rose-400 border-rose-500/20',
    };
    return classes[status] || 'bg-slate-500/10 text-slate-400';
};
</script>

<template>
    <Head title="Ordens de Compra" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Ordens de Compra</h2>
                    <p class="text-xs text-slate-400 mt-1">Gerencie a entrada de mercadorias e negociações com fornecedores.</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('stock.index')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">
                        Voltar ao Estoque
                    </Link>
                    <Link :href="route('purchase-orders.create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-3 py-2 rounded-xl transition-all shadow-lg shadow-blue-600/10 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Nova Ordem
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-5 border-b border-slate-800">
                        <h3 class="text-sm font-black text-white">Lista de Ordens de Compra</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-slate-300">
                            <thead class="text-xxs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 font-black">Número PO</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Fornecedor</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Status</th>
                                    <th scope="col" class="px-6 py-3.5 font-black text-right">Valor Total</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Criado Por</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Recebido Em</th>
                                    <th scope="col" class="px-6 py-3.5 font-black text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in orders.data" :key="order.id" class="border-b border-slate-800/50 hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4 font-bold text-white font-mono">{{ order.po_number }}</td>
                                    <td class="px-6 py-4 text-slate-400 font-semibold">{{ order.supplier?.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded border text-[10px] font-black uppercase tracking-wider" :class="getStatusBadgeClass(order.status)">
                                            {{ order.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-white font-mono">{{ formatCurrency(order.total_amount) }}</td>
                                    <td class="px-6 py-4 text-slate-400">{{ order.creator?.name || 'Sistema' }}</td>
                                    <td class="px-6 py-4 text-slate-400 font-mono">{{ formatDate(order.received_at) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-3">
                                            <!-- Check-In (Aprovado ou Recebido Parcial) -->
                                            <Link 
                                                v-if="['Aprovado', 'Recebido_Parcial'].includes(order.status)"
                                                :href="route('purchase-orders.checkin.form', order.id)" 
                                                class="text-xxs font-black text-emerald-400 hover:text-emerald-300 uppercase tracking-wider transition-colors"
                                            >
                                                Check-In
                                            </Link>
                                            
                                            <!-- Editar -->
                                            <Link 
                                                v-if="['Rascunho', 'Aprovado'].includes(order.status)"
                                                :href="route('purchase-orders.edit', order.id)" 
                                                class="text-xxs font-black text-blue-400 hover:text-blue-300 uppercase tracking-wider transition-colors"
                                            >
                                                Editar
                                            </Link>
                                            
                                            <!-- Excluir (Apenas Rascunho) -->
                                            <Link 
                                                v-if="order.status === 'Rascunho'"
                                                :href="route('purchase-orders.destroy', order.id)" 
                                                method="delete" 
                                                as="button" 
                                                class="text-xxs font-black text-rose-500 hover:text-rose-400 uppercase tracking-wider transition-colors"
                                                preserve-scroll
                                            >
                                                Excluir
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="orders.data.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-500">Nenhuma ordem de compra encontrada.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINAÇÃO -->
                    <div class="p-5 border-t border-slate-800 flex justify-center">
                        <div class="flex">
                            <template v-for="(link, key) in orders.links" :key="key">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-3 py-1.5 mx-1 text-xs rounded-xl font-bold transition-all"
                                    :class="{ 'bg-blue-600 text-white shadow-lg shadow-blue-600/10': link.active, 'text-slate-400 bg-slate-950 border border-slate-800 hover:text-white': !link.active }"
                                />
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
