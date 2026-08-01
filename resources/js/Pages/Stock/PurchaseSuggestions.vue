<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Array,
    suppliers: Array,
});

const selectedSupplier = ref('');
const selectedItems = ref({}); // product_id => boolean

// Preenche inicialmente todos os items como selecionados
props.products.forEach(p => {
    selectedItems.value[p.id] = true;
});

const form = useForm({
    supplier_id: '',
    items: [],
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

// Quantidades de reabastecimento ajustáveis
const replenishmentQuantities = ref({});
props.products.forEach(p => {
    replenishmentQuantities.value[p.id] = p.suggested_quantity;
});

// Preços de compra ajustáveis (inicia com cost_price ou 0)
const purchasePrices = ref({});
props.products.forEach(p => {
    purchasePrices.value[p.id] = p.cost_price || 0.00;
});

// Conta quantos itens estão selecionados
const selectedCount = computed(() => {
    return Object.values(selectedItems.value).filter(Boolean).length;
});

// Calcula o valor total estimado da compra
const totalEstimatedValue = computed(() => {
    return props.products.reduce((acc, p) => {
        if (selectedItems.value[p.id]) {
            const qty = replenishmentQuantities.value[p.id] || 0;
            const price = purchasePrices.value[p.id] || 0;
            return acc + (qty * price);
        }
        return acc;
    }, 0);
});

const submitOrder = () => {
    if (!selectedSupplier.value) {
        alert('Por favor, selecione um Fornecedor primeiro.');
        return;
    }

    const itemsToSend = props.products
        .filter(p => selectedItems.value[p.id])
        .map(p => ({
            product_id: p.id,
            quantity: replenishmentQuantities.value[p.id],
            price: purchasePrices.value[p.id],
        }));

    if (itemsToSend.length === 0) {
        alert('Selecione pelo menos um item para comprar.');
        return;
    }

    form.supplier_id = selectedSupplier.value;
    form.items = itemsToSend;

    form.post(route('purchase-orders.suggestions.generate'), {
        onSuccess: () => {
            alert('Ordem de compra rascunho criada com sucesso!');
        }
    });
};
</script>

<template>
    <Head title="Sugestões de Reabastecimento de Estoque" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Sugestões de Compra</h2>
                    <p class="text-xs text-slate-400 mt-1">Produtos abaixo do estoque mínimo de segurança.</p>
                </div>
                <Link :href="route('stock.index')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">
                    Voltar ao Estoque
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div v-if="products.length === 0" class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-emerald-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <h3 class="text-sm font-bold text-white mb-1">Tudo em Ordem!</h3>
                    <p class="text-xs text-slate-500">Todos os produtos monitorados estão com estoque acima do limite mínimo.</p>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    
                    <!-- Lista de Produtos para Reposição -->
                    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                        <div class="p-5 border-b border-slate-800">
                            <h3 class="text-sm font-black text-white">Produtos Necessitando Reposição</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left text-slate-300">
                                <thead class="text-xxs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 font-black text-center w-12">Comprar</th>
                                        <th scope="col" class="px-4 py-3 font-black">Produto</th>
                                        <th scope="col" class="px-4 py-3 font-black text-center">Estoque Atual</th>
                                        <th scope="col" class="px-4 py-3 font-black text-center">Estoque Mínimo</th>
                                        <th scope="col" class="px-4 py-3 font-black text-center w-24">Qtd Compra</th>
                                        <th scope="col" class="px-4 py-3 font-black text-center w-28">Preço Unit. (R$)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="p in products" :key="p.id" class="border-b border-slate-800/50 hover:bg-slate-800/20 transition-colors">
                                        <td class="px-4 py-4 text-center">
                                            <input type="checkbox" v-model="selectedItems[p.id]" class="rounded border-slate-800 text-blue-600 bg-slate-950 focus:ring-blue-500" />
                                        </td>
                                        <td class="px-4 py-4 font-bold text-white">{{ p.name }}</td>
                                        <td class="px-4 py-4 text-center font-mono text-slate-400">{{ p.stock_quantity }}</td>
                                        <td class="px-4 py-4 text-center font-mono text-slate-400">{{ p.minimum_stock }}</td>
                                        <td class="px-4 py-4">
                                            <input 
                                                type="number" 
                                                min="1" 
                                                v-model.number="replenishmentQuantities[p.id]" 
                                                class="bg-slate-950 border border-slate-800 rounded-lg px-2 py-1 text-center font-mono text-xs w-full text-white" 
                                                :disabled="!selectedItems[p.id]"
                                            />
                                        </td>
                                        <td class="px-4 py-4">
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                min="0" 
                                                v-model.number="purchasePrices[p.id]" 
                                                class="bg-slate-950 border border-slate-800 rounded-lg px-2 py-1 text-center font-mono text-xs w-full text-white" 
                                                :disabled="!selectedItems[p.id]"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Geração de Ordem de Compra -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4">
                        <h3 class="text-sm font-black text-white">Gerar Ordem de Compra</h3>
                        
                        <!-- Fornecedor -->
                        <div>
                            <label for="supplier_id" class="block text-xxs font-black text-slate-400 uppercase mb-1">Fornecedor Parceiro</label>
                            <select 
                                id="supplier_id" 
                                v-model="selectedSupplier" 
                                class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                                required
                            >
                                <option value="" disabled>-- Selecione o Fornecedor --</option>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>

                        <!-- Resumo financeiro estimado -->
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-2">
                            <div class="flex justify-between text-xxs text-slate-400">
                                <span>Produtos Selecionados:</span>
                                <span class="font-bold text-white font-mono">{{ selectedCount }} de {{ products.length }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-slate-400 border-t border-slate-800 pt-2 font-bold">
                                <span>Custo Total Estimado:</span>
                                <span class="text-emerald-400 font-mono">{{ formatCurrency(totalEstimatedValue) }}</span>
                            </div>
                        </div>

                        <button 
                            @click="submitOrder"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3 rounded-xl transition-all shadow-lg shadow-blue-600/10 flex justify-center items-center gap-1.5"
                            :disabled="form.processing || selectedCount === 0"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Criar Ordem Rascunho
                        </button>
                        <p class="text-[10px] text-center text-slate-500 leading-normal">Esta ação gerará uma Ordem de Compra no status "Rascunho" que poderá ser editada ou aprovada posteriormente.</p>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
