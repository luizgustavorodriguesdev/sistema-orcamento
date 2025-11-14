<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Define as props que o componente recebe do controller
const props = defineProps({
    settings: Object,
    categories: Array,
});

// --- LÓGICA DO CARRINHO ---

// [CORREÇÃO] Garante que a chave do localStorage é 'cart'
// E que cada item tem uma quantidade mínima de 1.
const cart = ref(
    JSON.parse(localStorage.getItem('cart') || '[]').map(item => ({
        ...item,
        quantity: item.quantity || 1,
    }))
);

// [CORREÇÃO] Lógica de preços robusta
const getPriceForQuantity = (item) => {
    // Se não houver escalas de preços ou se estiver vazio, retorna o preço base.
    // Usamos 'item.price' que é o preço base (promocional ou normal) guardado.
    if (!item.price_tiers || item.price_tiers.length === 0) {
        return parseFloat(item.price);
    }
    
    // Garante que as escalas de preços estão ordenadas da MAIOR para a MENOR quantidade.
    const sortedTiers = [...item.price_tiers].sort((a, b) => parseInt(b.min_quantity, 10) - parseInt(a.min_quantity, 10));
    
    // Itera sobre as escalas de preços para encontrar a primeira que se aplica.
    for (const tier of sortedTiers) {
        // Garante que ambos os valores são números
        const itemQty = parseInt(item.quantity, 10);
        const tierMinQty = parseInt(tier.min_quantity, 10);
        
        // Se a quantidade do item for maior ou igual à quantidade mínima da faixa
        if (itemQty >= tierMinQty) {
            const tierPrice = parseFloat(tier.price);
            return tierPrice; // Aplica o preço da faixa
        }
    }

    // Se nenhuma faixa de preço for aplicável (ex: qtd < 10), retorna o preço base.
    const basePrice = parseFloat(item.price);
    return basePrice;
};


// Função para atualizar a quantidade de um item
const updateQuantity = (productId, newQuantity) => {
    const item = cart.value.find(p => p.id === productId);
    if (item) {
        // Garante que o valor é um número válido e não menor que 1
        const qty = parseInt(newQuantity, 10);
        item.quantity = isNaN(qty) ? 1 : Math.max(1, qty);
    }
};

// Observa mudanças no carrinho para salvar no localStorage
watch(cart, (newCart) => {
    // [CORREÇÃO] Garante que a chave do localStorage é 'cart'
    localStorage.setItem('cart', JSON.stringify(newCart));
}, { deep: true }); // 'deep: true' é essencial para detetar mudanças na 'quantity'


// Função para remover um item do carrinho
const removeItem = (productId) => {
    cart.value = cart.value.filter(item => item.id !== productId);
};

// Calcula o subtotal e o total geral do orçamento
const totalAmount = computed(() => {
    return cart.value.reduce((total, item) => {
        const itemPrice = getPriceForQuantity(item); // Calcula o preço unitário dinâmico
        return total + (itemPrice * item.quantity);
    }, 0);
});

// Prepara o formulário para ser enviado ao backend
const form = useForm({
    client_name: '',
    client_contact: '',
    items: [],
});

// Função para submeter o pedido de orçamento
const submitOrder = () => {
    form.items = cart.value.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
    }));

    form.post(route('storefront.quote.store'), {
        onSuccess: () => {
            cart.value = [];
            localStorage.removeItem('cart');
        },
    });
};

const formatCurrency = (value) => {
    if (isNaN(value)) {
        return 'R$ 0,00';
    }
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

</script>

<template>
    <Head title="Carrinho de Orçamento" />

    <div class="bg-gray-100 min-h-screen">
        <!-- CABEÇALHO (similar ao da vitrine) -->
        <header class="bg-white shadow-md">
             <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <Link :href="route('storefront.index')" class="text-2xl font-bold text-gray-800">
                    {{ settings.company_name || 'OrçaBrindes' }}
                </Link>
                <nav class="hidden md:flex space-x-6">
                    <Link v-for="category in categories" :key="category.id" href="#" class="text-gray-600 hover:text-blue-600">{{ category.name }}</Link>
                </nav>
            </div>
        </header>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Meu Orçamento</h1>

            <div v-if="cart.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Coluna dos Itens -->
                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-lg">
                    <div v-for="item in cart" :key="item.id" class="flex items-center justify-between py-4 border-b last:border-b-0">
                        <div class="flex items-center space-x-4">
                            <img :src="item.image" alt="Imagem do Produto" class="w-20 h-20 object-cover rounded-md">
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ item.name }}</h3>
                                <p class="text-gray-600">Preço Unitário: R$ {{ getPriceForQuantity(item).toFixed(2) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <input
                                type="number"
                                :value="item.quantity"
                                @input="updateQuantity(item.id, $event.target.value)"
                                class="w-20 text-center border-gray-300 rounded-md"
                                min="1"
                            >
                            <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Coluna de Finalização -->
                <div class="bg-white p-6 rounded-lg shadow-lg h-fit">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Finalizar Orçamento</h2>
                    <div class="space-y-4 mb-6">
                         <div>
                            <label for="client_name" class="block text-sm font-medium text-gray-700">Seu Nome Completo</label>
                            <input v-model="form.client_name" type="text" id="client_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <div v-if="form.errors.client_name" class="text-sm text-red-600 mt-1">{{ form.errors.client_name }}</div>
                        </div>
                        <div>
                            <label for="client_contact" class="block text-sm font-medium text-gray-700">Seu Email ou WhatsApp</label>
                            <input v-model="form.client_contact" type="text" id="client_contact" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                             <div v-if="form.errors.client_contact" class="text-sm text-red-600 mt-1">{{ form.errors.client_contact }}</div>
                        </div>
                    </div>
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center font-bold text-lg">
                            <span>Total Estimado:</span>
                            <span>{{ formatCurrency(totalAmount) }}</span>
                        </div>
                        <button @click="submitOrder" :disabled="form.processing" class="mt-6 w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition duration-300 disabled:opacity-50">
                           {{ form.processing ? 'Aguarde...' : 'Finalizar e Gerar Orçamento' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mensagem de Carrinho Vazio -->
            <div v-else class="text-center bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">O seu carrinho de orçamento está vazio.</h2>
                <p class="text-gray-600 mb-6">Navegue pela nossa vitrine para adicionar produtos.</p>
                <Link :href="route('storefront.index')" class="bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                    Voltar à Vitrine
                </Link>
            </div>
        </main>
    </div>
</template>