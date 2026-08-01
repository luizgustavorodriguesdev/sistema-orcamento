<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: Object,
    movements: Object,
    categories: Array,
    warehouses: Array,
    allProducts: Array,
    financial: Object,
    filters: Object,
});

// --- CONTROLE DE ABAS ---
const activeTab = ref('balances'); // balances, manual_entry, history

// --- FILTRAGEM ---
const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category_id || '');
const selectedWarehouse = ref(props.filters.warehouse_id || '');

const applyFilters = () => {
    router.get(route('stock.index'), {
        search: searchQuery.value,
        category_id: selectedCategory.value,
        warehouse_id: selectedWarehouse.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedWarehouse.value = '';
    router.get(route('stock.index'), {}, {
        preserveState: true,
        replace: true,
    });
};

// --- FORMULÁRIO DE LANÇAMENTO MANUAL ---
const manualForm = useForm({
    product_id: '',
    warehouse_id: props.warehouses[0]?.id || '',
    type: 'addition', // addition or subtraction
    quantity: 1,
    description: '',
});

const submitManualMovement = () => {
    manualForm.post(route('stock.manual-movement'), {
        onSuccess: () => {
            manualForm.reset('product_id', 'quantity', 'description');
            alert('Lançamento manual registrado com sucesso!');
        }
    });
};

// --- AUXILIARES ---
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

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const getMovementTypeLabel = (type) => {
    const labels = {
        'addition': 'Entrada Manual',
        'subtraction': 'Saída Manual',
        'adjustment': 'Ajuste Manual',
        'quote_approved': 'Venda (Orçamento)',
        'quote_cancelled': 'Estorno Venda',
        'inbound_po': 'Recebimento de O.C.',
        'inventory_adjustment': 'Ajuste de Inventário',
    };
    return labels[type] || type;
};

const getMovementTypeBadgeClass = (type) => {
    const classes = {
        'addition': 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'subtraction': 'bg-rose-500/10 text-rose-400 border-rose-500/20',
        'adjustment': 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'quote_approved': 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
        'quote_cancelled': 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        'inbound_po': 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'inventory_adjustment': 'bg-purple-500/10 text-purple-400 border-purple-500/20',
    };
    return classes[type] || 'bg-slate-500/10 text-slate-400 border-slate-500/20';
};
</script>

<template>
    <Head title="Controle de Estoque Profissional" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Controle de Estoque</h2>
                    <p class="text-xs text-slate-400 mt-1">Gestão avançada de depósitos, ordens de compra, inventários e custos.</p>
                </div>
                
                <!-- Links de Acesso Rápido -->
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('purchase-orders.suggestions')" class="bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 border border-amber-500/20 font-bold text-xs px-3 py-2 rounded-xl transition-all flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Sugestões de Compra
                    </Link>
                    <Link :href="route('purchase-orders.index')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-3 py-2 rounded-xl transition-all shadow-lg shadow-blue-600/10 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01" /></svg>
                        Ordens de Compra
                    </Link>
                    <Link :href="route('inventory.index')" class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs px-3 py-2 rounded-xl transition-all shadow-lg shadow-purple-600/10 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        Fazer Inventário
                    </Link>
                    <Link :href="route('warehouses.index')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">
                        Depósitos
                    </Link>
                    <Link :href="route('suppliers.index')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">
                        Fornecedores
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- 1. PAINEL FINANCEIRO DE ESTOQUE (PRO) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Custo Total -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-xxs font-black text-slate-400 uppercase">Patrimônio de Estoque (Custo)</span>
                            <span class="p-2 rounded-xl bg-blue-500/10 text-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1M10 11h2m4 2h.01" /></svg>
                            </span>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-2xl font-black text-white leading-none">{{ formatCurrency(financial.total_cost) }}</h3>
                            <p class="text-xxs text-slate-400 mt-1">Soma do saldo de itens × preço de custo</p>
                        </div>
                    </div>

                    <!-- Faturamento Potencial -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-xxs font-black text-slate-400 uppercase">Faturamento Potencial (Venda)</span>
                            <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                            </span>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-2xl font-black text-white leading-none">{{ formatCurrency(financial.total_retail) }}</h3>
                            <p class="text-xxs text-slate-400 mt-1">Soma do saldo de itens × preço de venda</p>
                        </div>
                    </div>

                    <!-- Lucro Bruto Estimado -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-xxs font-black text-slate-400 uppercase">Lucro Bruto Projetado</span>
                            <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            </span>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-2xl font-black text-white leading-none">{{ formatCurrency(financial.potential_profit) }}</h3>
                            <p class="text-xxs text-slate-400 mt-1">Margem absoluta de lucro estimada</p>
                        </div>
                    </div>

                    <!-- Margem de Lucro Média -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-xxs font-black text-slate-400 uppercase">Markup Médio</span>
                            <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /></svg>
                            </span>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-2xl font-black text-white leading-none">{{ financial.margin_percent.toFixed(1) }}%</h3>
                            <p class="text-xxs text-slate-400 mt-1">Retorno percentual médio sobre o custo</p>
                        </div>
                    </div>
                </div>

                <!-- 2. ABAS DE NAVEGAÇÃO -->
                <div class="border-b border-slate-800 flex gap-4">
                    <button 
                        @click="activeTab = 'balances'"
                        class="pb-3 text-xs font-bold transition-all border-b-2"
                        :class="activeTab === 'balances' ? 'border-blue-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200'"
                    >
                        Saldos por Depósito
                    </button>
                    <button 
                        @click="activeTab = 'manual_entry'"
                        class="pb-3 text-xs font-bold transition-all border-b-2"
                        :class="activeTab === 'manual_entry' ? 'border-blue-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200'"
                    >
                        Lançamentos Manuais
                    </button>
                    <button 
                        @click="activeTab = 'history'"
                        class="pb-3 text-xs font-bold transition-all border-b-2"
                        :class="activeTab === 'history' ? 'border-blue-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200'"
                    >
                        Histórico Geral de Movimentações
                    </button>
                </div>

                <!-- 3. CONTEÚDO DAS ABAS -->
                
                <!-- ABA: SALDOS POR DEPÓSITO -->
                <div v-if="activeTab === 'balances'" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                    <div class="p-5 border-b border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h3 class="text-sm font-black text-white">Visualização de Saldos em Depósitos</h3>
                            <p class="text-xxs text-slate-400 mt-0.5">Saldo desagregado dos produtos controlados em estoque.</p>
                        </div>
                        
                        <!-- FILTROS -->
                        <div class="flex flex-wrap gap-2 w-full md:w-auto">
                            <input 
                                type="text" 
                                placeholder="Buscar produto..." 
                                class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 w-full md:w-48"
                                v-model="searchQuery"
                                @keyup.enter="applyFilters"
                            />
                            <select 
                                class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-blue-500"
                                v-model="selectedCategory"
                                @change="applyFilters"
                            >
                                <option value="">Todas as Categorias</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                            </select>
                            <select 
                                class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-blue-500"
                                v-model="selectedWarehouse"
                                @change="applyFilters"
                            >
                                <option value="">Todos os Depósitos</option>
                                <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                            </select>
                            <button @click="clearFilters" class="text-xxs text-slate-400 hover:text-white px-2 py-1 font-semibold transition-colors">Limpar</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-slate-300">
                            <thead class="text-xxs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 font-black">Produto</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Categoria</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Estoque Mínimo</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Estoque Atual</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Depósitos & Saldos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in products.data" :key="product.id" class="border-b border-slate-800/50 hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-white">{{ product.name }}</div>
                                        <div class="text-xxs text-slate-500 mt-0.5">Custo: {{ formatCurrency(product.cost_price) }} | Venda: {{ formatCurrency(product.price) }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-400">{{ product.category?.name || '-' }}</td>
                                    <td class="px-6 py-4 text-slate-400 font-mono">{{ product.minimum_stock }}</td>
                                    <td class="px-6 py-4 font-mono">
                                        <span 
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xxs font-bold"
                                            :class="product.stock_quantity <= product.minimum_stock ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400'"
                                        >
                                            {{ product.stock_quantity }}
                                            <span v-if="product.stock_quantity <= product.minimum_stock" class="ml-1 text-[9px] font-black uppercase">Abaixo</span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span 
                                                v-for="w in product.warehouses" 
                                                :key="w.id"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-950 border border-slate-800 text-xxs font-medium"
                                            >
                                                <span class="text-slate-400 font-bold">{{ w.code }}:</span>
                                                <span class="text-white font-mono">{{ w.pivot.quantity }}</span>
                                            </span>
                                            <span v-if="product.warehouses.length === 0" class="text-xxs text-slate-500">Sem saldos registrados</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="products.data.length === 0">
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-500">Nenhum produto cadastrado com controle de estoque.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINAÇÃO -->
                    <div class="p-5 border-t border-slate-800 flex justify-center">
                        <div class="flex">
                            <template v-for="(link, key) in products.links" :key="key">
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

                <!-- ABA: LANÇAMENTOS MANUAIS -->
                <div v-if="activeTab === 'manual_entry'" class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="mb-6">
                        <h3 class="text-sm font-black text-white">Lançamento Manual de Entrada e Saída</h3>
                        <p class="text-xxs text-slate-400 mt-0.5">Realize entradas e saídas avulsas em depósitos para ajustes, doações ou descartes.</p>
                    </div>

                    <form @submit.prevent="submitManualMovement" class="space-y-4 max-w-xl">
                        <!-- Produto -->
                        <div>
                            <label for="product_id" class="block text-xxs font-black text-slate-400 uppercase mb-1">Produto</label>
                            <select 
                                id="product_id" 
                                v-model="manualForm.product_id" 
                                class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                                required
                            >
                                <option value="" disabled>-- Selecione o Produto --</option>
                                <option v-for="p in allProducts" :key="p.id" :value="p.id">{{ p.name }} (Estoque atual: {{ p.stock_quantity }})</option>
                            </select>
                            <p v-if="manualForm.errors.product_id" class="text-xs text-rose-500 mt-1">{{ manualForm.errors.product_id }}</p>
                        </div>

                        <!-- Depósito + Tipo de Operação -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="warehouse_id" class="block text-xxs font-black text-slate-400 uppercase mb-1">Depósito Destino/Origem</label>
                                <select 
                                    id="warehouse_id" 
                                    v-model="manualForm.warehouse_id" 
                                    class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                                    required
                                >
                                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                                </select>
                                <p v-if="manualForm.errors.warehouse_id" class="text-xs text-rose-500 mt-1">{{ manualForm.errors.warehouse_id }}</p>
                            </div>
                            <div>
                                <label for="type" class="block text-xxs font-black text-slate-400 uppercase mb-1">Tipo de Movimentação</label>
                                <select 
                                    id="type" 
                                    v-model="manualForm.type" 
                                    class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                                    required
                                >
                                    <option value="addition">Entrada (+) no Estoque</option>
                                    <option value="subtraction">Saída (-) do Estoque</option>
                                </select>
                                <p v-if="manualForm.errors.type" class="text-xs text-rose-500 mt-1">{{ manualForm.errors.type }}</p>
                            </div>
                        </div>

                        <!-- Quantidade -->
                        <div>
                            <label for="quantity" class="block text-xxs font-black text-slate-400 uppercase mb-1">Quantidade</label>
                            <input 
                                id="quantity" 
                                type="number" 
                                min="1" 
                                v-model.number="manualForm.quantity" 
                                class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                                required
                            />
                            <p v-if="manualForm.errors.quantity" class="text-xs text-rose-500 mt-1">{{ manualForm.errors.quantity }}</p>
                        </div>

                        <!-- Justificativa -->
                        <div>
                            <label for="description" class="block text-xxs font-black text-slate-400 uppercase mb-1">Motivo / Justificativa</label>
                            <textarea 
                                id="description" 
                                rows="3" 
                                v-model="manualForm.description" 
                                placeholder="Escreva o motivo da movimentação (ex: Carga inicial, Descarte de peça quebrada, Amostra grátis)" 
                                class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                                required
                            ></textarea>
                            <p v-if="manualForm.errors.description" class="text-xs text-rose-500 mt-1">{{ manualForm.errors.description }}</p>
                        </div>

                        <!-- Botão de Envio -->
                        <div class="pt-2">
                            <button 
                                type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-5 py-3 rounded-xl transition-all shadow-lg shadow-blue-600/10"
                                :disabled="manualForm.processing"
                            >
                                Registrar Lançamento
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ABA: HISTÓRICO GERAL -->
                <div v-if="activeTab === 'history'" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                    <div class="p-5 border-b border-slate-800">
                        <h3 class="text-sm font-black text-white">Log de Auditoria de Estoque</h3>
                        <p class="text-xxs text-slate-400 mt-0.5">Histórico completo de entradas, saídas, vendas e inventários reconciliados.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-slate-300">
                            <thead class="text-xxs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 font-black">Data/Hora</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Produto</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Depósito</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Tipo</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Qtd</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Justificativa / Documento</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Operador</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in movements.data" :key="m.id" class="border-b border-slate-800/50 hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4 text-slate-400 font-mono whitespace-nowrap">{{ formatDate(m.created_at) }}</td>
                                    <td class="px-6 py-4 font-bold text-white">{{ m.product?.name || 'Excluído' }}</td>
                                    <td class="px-6 py-4 font-mono text-slate-400">{{ m.warehouse?.code || '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded border text-[10px] font-black uppercase tracking-wider" :class="getMovementTypeBadgeClass(m.type)">
                                            {{ getMovementTypeLabel(m.type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold font-mono" :class="m.quantity > 0 ? 'text-emerald-400' : 'text-rose-400'">
                                        {{ m.quantity > 0 ? '+' : '' }}{{ m.quantity }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-400">
                                        <div>{{ m.description || '-' }}</div>
                                        <div class="text-xxs text-slate-500 mt-0.5">
                                            <span v-if="m.quote_id" class="font-semibold text-indigo-400">Orçamento #{{ m.quote_id }}</span>
                                            <span v-if="m.purchase_order" class="font-semibold text-blue-400">O.C. #{{ m.purchase_order.po_number }}</span>
                                            <span v-if="m.inventory_session" class="font-semibold text-purple-400">Inventário #{{ m.inventory_session.id }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-400 whitespace-nowrap">{{ m.user?.name || 'Sistema' }}</td>
                                </tr>
                                <tr v-if="movements.data.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-500">Nenhuma movimentação registrada.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINAÇÃO -->
                    <div class="p-5 border-t border-slate-800 flex justify-center">
                        <div class="flex">
                            <template v-for="(link, key) in movements.links" :key="key">
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
