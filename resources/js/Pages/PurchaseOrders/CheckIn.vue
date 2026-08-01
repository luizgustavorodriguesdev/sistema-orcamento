<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    order: Object,
    warehouses: Array,
});

const form = useForm({
    warehouse_id: props.warehouses[0]?.id || '',
    received: {}, // product_id => quantity received today
});

// Inicializa recebidos como 0 ou a quantidade restante
props.order.items.forEach(item => {
    const remaining = item.quantity - item.received_quantity;
    form.received[item.product_id] = remaining > 0 ? remaining : 0;
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const submit = () => {
    // Valida se pelo menos uma quantidade é maior que 0
    const hasItemsReceived = Object.values(form.received).some(qty => qty > 0);
    if (!hasItemsReceived) {
        alert('Por favor, informe a quantidade recebida de pelo menos um item.');
        return;
    }

    form.post(route('purchase-orders.checkin', props.order.id), {
        onSuccess: () => {
            alert('Recebimento de estoque registrado com sucesso!');
        }
    });
};
</script>

<template>
    <Head title="Check-In de Recebimento" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Check-In de Recebimento</h2>
                    <p class="text-xs text-slate-400 mt-1">Dê entrada física dos itens da O.C. #{{ order.po_number }} no estoque.</p>
                </div>
                <Link :href="route('purchase-orders.index')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">
                    Voltar
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    
                    <!-- Lista de Itens para Receber -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Depósito Destino -->
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm">
                            <h3 class="text-sm font-black text-white">Alocação de Carga</h3>
                            <div class="max-w-md">
                                <label for="warehouse_id" class="block text-xxs font-black text-slate-400 uppercase mb-1">Depósito para Armazenamento</label>
                                <select 
                                    id="warehouse_id" 
                                    v-model="form.warehouse_id" 
                                    class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                                    required
                                >
                                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                                </select>
                                <p class="text-[10px] text-slate-500 mt-1">Todos os itens conferidos abaixo serão adicionados ao saldo deste depósito.</p>
                            </div>
                        </div>

                        <!-- Tabela de Conferência -->
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                            <div class="p-5 border-b border-slate-800">
                                <h3 class="text-sm font-black text-white">Tabela de Conferência</h3>
                            </div>

                            <table class="w-full text-xs text-left text-slate-300">
                                <thead class="text-xxs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-black">Produto</th>
                                        <th scope="col" class="px-6 py-3 font-black text-center">Comprado</th>
                                        <th scope="col" class="px-6 py-3 font-black text-center">Já Recebido</th>
                                        <th scope="col" class="px-6 py-3 font-black text-center">Pendente</th>
                                        <th scope="col" class="px-6 py-3 font-black text-center w-32">Recebido Hoje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in order.items" :key="item.id" class="border-b border-slate-800/50 hover:bg-slate-800/20 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-white">{{ item.product?.name || 'Excluído' }}</div>
                                            <div class="text-xxs text-slate-500 mt-0.5">Preço Negociado: {{ formatCurrency(item.price) }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-mono font-semibold">{{ item.quantity }}</td>
                                        <td class="px-6 py-4 text-center font-mono text-slate-400">{{ item.received_quantity }}</td>
                                        <td class="px-6 py-4 text-center font-mono text-amber-400 font-bold">
                                            {{ Math.max(0, item.quantity - item.received_quantity) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <input 
                                                type="number" 
                                                min="0" 
                                                :max="item.quantity - item.received_quantity"
                                                v-model.number="form.received[item.product_id]" 
                                                class="bg-slate-950 border border-slate-800 rounded-lg px-2 py-1 text-center font-mono text-xs w-24 text-white" 
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sidebar de Fechamento -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4 shadow-sm">
                        <h3 class="text-sm font-black text-white">Resumo de Recebimento</h3>

                        <div class="space-y-2 bg-slate-950 border border-slate-800 rounded-xl p-4 text-xxs text-slate-400 leading-relaxed">
                            <p><strong class="text-white">O.C.:</strong> {{ order.po_number }}</p>
                            <p><strong class="text-white">Fornecedor:</strong> {{ order.supplier?.name }}</p>
                            <p><strong class="text-white">Valor PO:</strong> {{ formatCurrency(order.total_amount) }}</p>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-3 rounded-xl transition-all shadow-lg shadow-emerald-600/10 flex justify-center items-center gap-1.5"
                            :disabled="form.processing"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Confirmar Check-In
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
