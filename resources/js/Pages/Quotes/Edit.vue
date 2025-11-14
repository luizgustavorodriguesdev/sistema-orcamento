<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    quote: Object,
    products: Array,
    clients: Array,
    paymentMethods: Array,
});

const form = useForm({
    client_id: props.quote.client_id,
    payment_terms: props.quote.payment_terms,
    delivery_info: props.quote.delivery_info,
    status: props.quote.status,
    items: [],
});

const quoteItems = ref(props.quote.products.map(p => ({
    product_id: p.id,
    name: p.name,
    price: p.pivot.unit_price,
    quantity: p.pivot.quantity,
})));

// --- LÓGICA DE PREÇOS DINÂMICOS ---
const getPriceForQuantity = (product, quantity) => {
    const tier = product.price_tiers
        .filter(t => t.min_quantity <= quantity)
        .sort((a, b) => b.min_quantity - a.min_quantity)[0];
    return tier ? tier.price : product.price;
};

watch(quoteItems, (newItems) => {
    newItems.forEach(item => {
        const product = props.products.find(p => p.id === item.product_id);
        if (product) {
            item.price = getPriceForQuantity(product, item.quantity);
        }
    });
}, { deep: true });

// --- LÓGICA DOS ITENS DO ORÇAMENTO ---
const addItem = (product) => {
    const existingItem = quoteItems.value.find(item => item.product_id === product.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        quoteItems.value.push({
            product_id: product.id,
            name: product.name,
            price: getPriceForQuantity(product, 1),
            quantity: 1,
        });
    }
};

const removeItem = (productId) => {
    quoteItems.value = quoteItems.value.filter(item => item.product_id !== productId);
};

const totalAmount = computed(() => {
    return quoteItems.value.reduce((total, item) => total + (item.price * item.quantity), 0);
});

// --- LÓGICA DA SELEÇÃO DE PAGAMENTO ---
const matchingMethod = props.paymentMethods.find(
    method => method.description === props.quote.payment_terms
);
const selectedPaymentMethodId = ref(matchingMethod ? matchingMethod.id : null);

watch(selectedPaymentMethodId, (newId) => {
    if (newId) {
        const selectedMethod = props.paymentMethods.find(method => method.id === newId);
        form.payment_terms = selectedMethod ? selectedMethod.description : '';
    }
});

// --- LÓGICA DE SUBMISSÃO E FORMATAÇÃO ---
const submit = () => {
    form.items = quoteItems.value.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
    }));
    form.put(route('quotes.update', props.quote.id));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};
</script>

<template>
    <Head title="Editar Orçamento" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Orçamento #{{ String(quote.id).padStart(4, '0') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <div class="md:col-span-2 space-y-6">
                            <div class="bg-white p-6 rounded-lg shadow-sm">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="status" class="block font-medium text-sm text-gray-700">Status do Orçamento</label>
                                        <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <option value="Pendente">Pendente</option>
                                            <option value="Aprovado">Aprovado</option>
                                            <option value="Cancelado">Cancelado</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="client_id" class="block font-medium text-sm text-gray-700">Cliente</label>
                                        <select id="client_id" v-model="form.client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                            <option v-for="client in clients" :key="client.id" :value="client.id">
                                                {{ client.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-lg shadow-sm">
                                <h3 class="text-lg font-bold mb-4">Itens do Orçamento</h3>
                                <div v-for="item in quoteItems" :key="item.product_id" class="flex items-center justify-between border-b py-2">
                                    <div>
                                        <p class="font-semibold">{{ item.name }}</p>
                                        <p class="text-sm text-gray-600">{{ formatCurrency(item.price) }} / un.</p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <input type="number" v-model.number="item.quantity" min="1" class="w-20 text-center rounded-md border-gray-300 shadow-sm">
                                        <button @click="removeItem(item.product_id)" type="button" class="text-red-500 hover:text-red-700">Remover</button>
                                    </div>
                                </div>
                                <div class="text-right font-bold text-xl mt-4">Total: {{ formatCurrency(totalAmount) }}</div>
                            </div>
                        </div>

                        <div class="md:col-span-1">
                            <div class="bg-white p-6 rounded-lg shadow-sm">
                                <h3 class="text-lg font-bold mb-4">Adicionar Produtos</h3>
                                <div class="space-y-2 max-h-96 overflow-y-auto">
                                    <div v-for="product in products" :key="product.id" class="flex justify-between items-center p-2 rounded-md hover:bg-gray-100">
                                        <div>
                                            <p class="font-semibold">{{ product.name }}</p>
                                            <p class="text-sm text-gray-600">{{ formatCurrency(product.price) }}</p>
                                        </div>
                                        <button @click="addItem(product)" type="button" class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-600">Adicionar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 bg-white p-6 rounded-lg shadow-sm">
                         <h3 class="text-lg font-bold mb-4">Detalhes Adicionais</h3>
                         <div class="mb-4">
                            <label for="payment_method_select">Selecionar Forma de Pagamento (Opcional)</label>
                            <select id="payment_method_select" v-model="selectedPaymentMethodId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option :value="null">-- Preencher manualmente --</option>
                                <option v-for="method in paymentMethods" :key="method.id" :value="method.id">{{ method.name }}</option>
                            </select>
                         </div>
                         <div>
                            <label for="payment_terms">Condições de Pagamento</label>
                            <textarea v-model="form.payment_terms" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                         </div>
                         <div class="mt-4">
                            <label for="delivery_info">Prazo de Entrega</label>
                            <textarea v-model="form.delivery_info" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                         </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded" :disabled="form.processing">
                                {{ form.processing ? 'A Atualizar...' : 'Atualizar Orçamento' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
