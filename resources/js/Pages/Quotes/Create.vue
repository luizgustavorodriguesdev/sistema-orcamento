<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    products: Array,
    clients: Array,
    paymentMethods: Array,
});

const form = useForm({
    client_id: null,
    payment_terms: '',
    delivery_info: '',
    items: [],
});

const quoteItems = ref([]);

// --- LÓGICA DE PREÇOS DINÂMICOS ---

// 1. Função JavaScript que replica a lógica do nosso Controller.
const getPriceForQuantity = (product, quantity) => {
    // Procura a escala de preço correspondente, ordenando da maior para a menor quantidade.
    const tier = product.price_tiers
        .filter(t => t.min_quantity <= quantity)
        .sort((a, b) => b.min_quantity - a.min_quantity)[0];

    // Se encontrar uma escala, retorna o preço dela. Caso contrário, retorna o preço base do produto.
    return tier ? tier.price : product.price;
};

// 2. 'watch' observa o array 'quoteItems' para qualquer alteração (como a quantidade).
watch(quoteItems, (newItems) => {
    newItems.forEach(item => {
        // Encontra o produto completo na nossa lista de props.
        const product = props.products.find(p => p.id === item.product_id);
        if (product) {
            // Recalcula o preço unitário do item com base na sua nova quantidade.
            item.price = getPriceForQuantity(product, item.quantity);
        }
    });
}, { deep: true }); // 'deep: true' garante que o watch observe as propriedades dentro dos objetos do array.


// --- LÓGICA DOS ITENS DO ORÇAMENTO ---
const addItem = (product) => {
    const existingItem = quoteItems.value.find(item => item.product_id === product.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        quoteItems.value.push({
            product_id: product.id,
            name: product.name,
            // O preço inicial é calculado com base na quantidade 1.
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

const submit = () => {
    form.items = quoteItems.value.map(item => ({ product_id: item.product_id, quantity: item.quantity }));
    form.post(route('quotes.store'));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};


// --- LÓGICA DO MODAL E PESQUISA DE CLIENTES ---
const showAddClientModal = ref(false);
const newClientForm = useForm({ name: '', contact_main: '' });
const submitNewClient = () => {
    newClientForm.post(route('clients.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddClientModal.value = false;
            newClientForm.reset();
        },
    });
};

const clientSearchQuery = ref('');
const filteredClients = computed(() => {
    if (!clientSearchQuery.value) return props.clients;
    return props.clients.filter(client =>
        client.name.toLowerCase().includes(clientSearchQuery.value.toLowerCase())
    );
});


// --- LÓGICA DA SELEÇÃO DE PAGAMENTO ---
const selectedPaymentMethodId = ref(null);
watch(selectedPaymentMethodId, (newId) => {
    if (newId) {
        const selectedMethod = props.paymentMethods.find(method => method.id === newId);
        form.payment_terms = selectedMethod ? selectedMethod.description : '';
    } else {
        form.payment_terms = '';
    }
});
</script>

<template>
    <Head title="Criar Orçamento" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Criar Novo Orçamento</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                </div>

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <div class="md:col-span-2 space-y-6">
                            <div class="bg-white p-6 rounded-lg shadow-sm">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-bold">Dados do Cliente</h3>
                                    <button @click="showAddClientModal = true" type="button" class="text-sm bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded">
                                        + Novo Cliente
                                    </button>
                                </div>
                                <div class="mb-4">
                                    <label for="client_search">Pesquisar Cliente</label>
                                    <input type="text" id="client_search" v-model="clientSearchQuery" placeholder="Digite o nome do cliente..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                                </div>
                                <div>
                                    <label for="client_id">Selecionar Cliente</label>
                                    <select id="client_id" v-model="form.client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                        <option :value="null" disabled>-- Escolha um cliente --</option>
                                        <option v-for="client in filteredClients" :key="client.id" :value="client.id">
                                            {{ client.name }} - ({{ client.contact_main }})
                                        </option>
                                    </select>
                                    <p v-if="form.errors.client_id" class="text-sm text-red-600 mt-1">{{ form.errors.client_id }}</p>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-lg shadow-sm">
                                <h3 class="text-lg font-bold mb-4">Itens do Orçamento</h3>
                                <div v-if="quoteItems.length === 0" class="text-center text-gray-500 py-4">Nenhum item adicionado.</div>
                                <div v-else>
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
                                </div>
                                <p v-if="form.errors.items" class="text-sm text-red-600 mt-2">{{ form.errors.items }}</p>
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
                                {{ form.processing ? 'A Guardar...' : 'Guardar Orçamento' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>

    <Modal :show="showAddClientModal" @close="showAddClientModal = false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">Adicionar Novo Cliente</h2>
            <form @submit.prevent="submitNewClient" class="mt-6 space-y-4">
                <div>
                    <label for="new_client_name">Nome Completo</label>
                    <input id="new_client_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="newClientForm.name" required />
                    <p v-if="newClientForm.errors.name" class="text-sm text-red-600 mt-2">{{ newClientForm.errors.name }}</p>
                </div>
                <div>
                    <label for="new_client_contact">Contacto Principal</label>
                    <input id="new_client_contact" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="newClientForm.contact_main" required />
                    <p v-if="newClientForm.errors.contact_main" class="text-sm text-red-600 mt-2">{{ newClientForm.errors.contact_main }}</p>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="button" @click="showAddClientModal = false" class="mr-4 text-gray-600">Cancelar</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :disabled="newClientForm.processing">
                        {{ newClientForm.processing ? 'A Guardar...' : 'Guardar Cliente' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
