<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    suppliers: Array,
    products: Array,
});

// Pre-gera um código curto de PO
const generatePONumber = () => {
    const date = new Date().toISOString().slice(0,10).replace(/-/g,"");
    const random = Math.floor(1000 + Math.random() * 9000);
    return `OC-${date}-${random}`;
};

const form = useForm({
    supplier_id: '',
    po_number: generatePONumber(),
    observations: '',
    items: [],
});

const currentProduct = ref('');
const currentQuantity = ref(1);
const currentPrice = ref(0.00);

const addProductToOrder = () => {
    if (!currentProduct.value) {
        alert('Selecione um produto.');
        return;
    }

    const selectedProduct = props.products.find(p => p.id === currentProduct.value);
    
    // Verifica se já foi adicionado
    const exists = form.items.some(item => item.product_id === currentProduct.value);
    if (exists) {
        alert('Este produto já está na ordem. Ajuste a quantidade na lista.');
        return;
    }

    form.items.push({
        product_id: selectedProduct.id,
        name: selectedProduct.name,
        quantity: currentQuantity.value,
        price: currentPrice.value || selectedProduct.cost_price || 0.00,
    });

    currentProduct.value = '';
    currentQuantity.value = 1;
    currentPrice.value = 0.00;
};

const removeProductFromOrder = (index) => {
    form.items.splice(index, 1);
};

const onProductChange = () => {
    if (currentProduct.value) {
        const selected = props.products.find(p => p.id === currentProduct.value);
        if (selected) {
            currentPrice.value = selected.cost_price || 0.00;
        }
    }
};

const totalAmount = computed(() => {
    return form.items.reduce((acc, item) => {
        return acc + (item.quantity * item.price);
    }, 0);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const submit = () => {
    if (form.items.length === 0) {
        alert('Adicione pelo menos um produto à ordem de compra.');
        return;
    }

    form.post(route('purchase-orders.store'), {
        onSuccess: () => {
            alert('Ordem de compra criada com sucesso!');
        }
    });
};
</script>

<template>
    <Head title="Nova Ordem de Compra" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Nova Ordem de Compra</h2>
                    <p class="text-xs text-slate-400 mt-1">Crie um novo pedido de reabastecimento.</p>
                </div>
                <Link :href="route('purchase-orders.index')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">
                    Voltar
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    
                    <!-- Form e Itens -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Informações Básicas -->
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm">
                            <h3 class="text-sm font-black text-white">Dados Gerais</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="supplier_id" class="block text-xxs font-black text-slate-400 uppercase mb-1">Fornecedor</label>
                                    <select 
                                        id="supplier_id" 
                                        v-model="form.supplier_id" 
                                        class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                                        required
                                    >
                                        <option value="" disabled>-- Selecione o Fornecedor --</option>
                                        <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                                    </select>
                                    <p v-if="form.errors.supplier_id" class="text-xs text-rose-500 mt-1">{{ form.errors.supplier_id }}</p>
                                </div>
                                <div>
                                    <label for="po_number" class="block text-xxs font-black text-slate-400 uppercase mb-1">Número PO</label>
                                    <input 
                                        id="po_number" 
                                        type="text" 
                                        v-model="form.po_number" 
                                        class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white font-mono w-full focus:outline-none focus:border-blue-500" 
                                        required
                                    />
                                    <p v-if="form.errors.po_number" class="text-xs text-rose-500 mt-1">{{ form.errors.po_number }}</p>
                                </div>
                            </div>

                            <div>
                                <label for="observations" class="block text-xxs font-black text-slate-400 uppercase mb-1">Observações</label>
                                <textarea 
                                    id="observations" 
                                    rows="2" 
                                    v-model="form.observations" 
                                    placeholder="Instruções de entrega, condições de pagamento ou notas..." 
                                    class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500"
                                ></textarea>
                                <p v-if="form.errors.observations" class="text-xs text-rose-500 mt-1">{{ form.errors.observations }}</p>
                            </div>
                        </div>

                        <!-- Adicionar Produto à Lista -->
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm">
                            <h3 class="text-sm font-black text-white">Adicionar Itens ao Pedido</h3>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                <div class="md:col-span-2">
                                    <label for="current_product" class="block text-xxs font-black text-slate-400 uppercase mb-1">Selecionar Produto</label>
                                    <select 
                                        id="current_product" 
                                        v-model="currentProduct" 
                                        @change="onProductChange"
                                        class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                                    >
                                        <option value="">-- Escolha um produto --</option>
                                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="current_quantity" class="block text-xxs font-black text-slate-400 uppercase mb-1">Quantidade</label>
                                    <input 
                                        id="current_quantity" 
                                        type="number" 
                                        min="1" 
                                        v-model.number="currentQuantity" 
                                        class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                                    />
                                </div>
                                <div>
                                    <label for="current_price" class="block text-xxs font-black text-slate-400 uppercase mb-1">Custo Unitário (R$)</label>
                                    <input 
                                        id="current_price" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        v-model.number="currentPrice" 
                                        class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                                    />
                                </div>
                            </div>
                            
                            <div class="flex justify-end pt-2">
                                <button 
                                    type="button" 
                                    @click="addProductToOrder"
                                    class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs px-4 py-2.5 rounded-xl transition-all flex items-center gap-1"
                                >
                                    Adicionar à Lista
                                </button>
                            </div>
                        </div>

                        <!-- Lista de Itens Adicionados -->
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                            <div class="p-5 border-b border-slate-800">
                                <h3 class="text-sm font-black text-white">Itens da Ordem de Compra</h3>
                            </div>

                            <table class="w-full text-xs text-left text-slate-300">
                                <thead class="text-xxs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-black">Produto</th>
                                        <th scope="col" class="px-6 py-3 font-black text-center">Quantidade</th>
                                        <th scope="col" class="px-6 py-3 font-black text-right">Custo Unitário</th>
                                        <th scope="col" class="px-6 py-3 font-black text-right">Total</th>
                                        <th scope="col" class="px-6 py-3 font-black text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in form.items" :key="index" class="border-b border-slate-800/50 hover:bg-slate-800/20 transition-colors">
                                        <td class="px-6 py-4 font-bold text-white">{{ item.name }}</td>
                                        <td class="px-6 py-4 text-center font-mono">
                                            <input 
                                                type="number" 
                                                min="1" 
                                                v-model.number="item.quantity" 
                                                class="bg-slate-950 border border-slate-800 rounded-lg px-2 py-0.5 text-center font-mono text-xs w-16 text-white" 
                                            />
                                        </td>
                                        <td class="px-6 py-4 text-right font-mono">
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                min="0" 
                                                v-model.number="item.price" 
                                                class="bg-slate-950 border border-slate-800 rounded-lg px-2 py-0.5 text-right font-mono text-xs w-24 text-white" 
                                            />
                                        </td>
                                        <td class="px-6 py-4 text-right font-mono text-white font-semibold">{{ formatCurrency(item.quantity * item.price) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <button @click="removeProductFromOrder(index)" type="button" class="text-rose-500 hover:text-rose-400 font-bold">
                                                Remover
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="form.items.length === 0">
                                        <td colspan="5" class="px-6 py-8 text-center text-slate-500 font-light">Nenhum produto adicionado à ordem de compra ainda.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Resumo Financeiro e Ações -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4 shadow-sm">
                        <h3 class="text-sm font-black text-white">Resumo Financeiro</h3>

                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-2">
                            <div class="flex justify-between text-xxs text-slate-400">
                                <span>Total de Itens:</span>
                                <span class="font-bold text-white font-mono">{{ form.items.length }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-slate-400 border-t border-slate-800 pt-2 font-bold">
                                <span>Custo Total:</span>
                                <span class="text-emerald-400 font-mono">{{ formatCurrency(totalAmount) }}</span>
                            </div>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3 rounded-xl transition-all shadow-lg shadow-blue-600/10 flex justify-center items-center gap-1.5"
                            :disabled="form.processing || form.items.length === 0"
                        >
                            Salvar como Rascunho
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
