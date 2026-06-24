<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Define as props que o componente recebe do controller
const props = defineProps({
    settings: Object,
    categories: Array,
    paymentMethods: Array,
    menuItems: Array,
});

// --- LÓGICA DO CARRINHO ---
const cart = ref(
    JSON.parse(localStorage.getItem('cart') || '[]').map(item => ({
        ...item,
        quantity: item.quantity || 1,
    }))
);

// Lógica de preços por escala
const getPriceForQuantity = (item) => {
    if (!item.price_tiers || item.price_tiers.length === 0) {
        return parseFloat(item.price);
    }
    
    // Ordena as escalas de preços da maior para a menor quantidade
    const sortedTiers = [...item.price_tiers].sort((a, b) => parseInt(b.min_quantity, 10) - parseInt(a.min_quantity, 10));
    
    for (const tier of sortedTiers) {
        const itemQty = parseInt(item.quantity, 10);
        const tierMinQty = parseInt(tier.min_quantity, 10);
        
        if (itemQty >= tierMinQty) {
            return parseFloat(tier.price);
        }
    }

    return parseFloat(item.price);
};

// Atualiza a quantidade de um item
const updateQuantity = (productId, newQuantity) => {
    const item = cart.value.find(p => p.id === productId);
    if (item) {
        const qty = parseInt(newQuantity, 10);
        item.quantity = isNaN(qty) ? 1 : Math.max(1, qty);
    }
};

// Observa mudanças no carrinho para salvar no localStorage
watch(cart, (newCart) => {
    localStorage.setItem('cart', JSON.stringify(newCart));
}, { deep: true });

// Remove um item do carrinho
const removeItem = (productId) => {
    cart.value = cart.value.filter(item => item.id !== productId);
};

// Calcula o subtotal do orçamento
const totalAmount = computed(() => {
    return cart.value.reduce((total, item) => {
        const itemPrice = getPriceForQuantity(item);
        return total + (itemPrice * item.quantity);
    }, 0);
});

// Prepara o formulário para envio
const form = useForm({
    client_name: '',
    client_email: '',
    client_phone: '',
    cep: '',
    address_street: '',
    address_neighborhood: '',
    address_city: '',
    address_state: '',
    customization_details: '',
    payment_method_id: '',
    items: [],
});

// Auto-complete de Endereço pelo CEP
const fetchCep = async () => {
    const cleanCep = form.cep.replace(/\D/g, '');
    if (cleanCep.length === 8) {
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cleanCep}/json/`);
            const data = await response.json();
            if (!data.erro) {
                form.address_street = data.logradouro;
                form.address_neighborhood = data.bairro;
                form.address_city = data.localidade;
                form.address_state = data.uf;
            } else {
                alert("CEP não encontrado!");
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
        }
    }
};

const handleCepInput = () => {
    let value = form.cep.replace(/\D/g, '');
    if (value.length > 5) {
        value = value.substring(0, 5) + '-' + value.substring(5, 8);
    }
    form.cep = value;
    
    if (value.replace('-', '').length === 8) {
        fetchCep();
    }
};

// Finalizar pedido de orçamento
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

// --- ROTAS DO MENU ---
const getMenuUrl = (item) => {
    if (item.type === 'category') {
        return item.category ? route('storefront.category.show', { category: item.category.slug }) : '#';
    }
    if (item.type === 'product') {
        return item.product ? route('storefront.product.show', { product: item.product.slug }) : '#';
    }
    return item.url || '#';
};

// --- MENU MOBILE ---
const showingMobileMenu = ref(false);
const openDropdown = ref(null);
</script>

<template>
    <Head title="Carrinho de Orçamento - Finalizar Pedido" />

    <div class="bg-slate-50 min-h-screen font-sans antialiased text-slate-800 flex flex-col justify-between">
        <div>
            <!-- BARRA SUPERIOR DE INFORMAÇÕES -->
            <div class="bg-slate-900 text-slate-300 text-xs py-2 border-b border-slate-800">
                <div class="container mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-2 max-w-6xl">
                    <div class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span>Entregamos para todo o Brasil</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <a v-if="settings.company_email" :href="`mailto:${settings.company_email}`" class="hover:text-blue-400 flex items-center gap-1 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            <span>{{ settings.company_email }}</span>
                        </a>
                        <a v-if="settings.company_phone" :href="`tel:${settings.company_phone}`" class="hover:text-blue-400 flex items-center gap-1 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            <span>{{ settings.company_phone }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- CABEÇALHO PRINCIPAL -->
            <header class="bg-white shadow-sm sticky top-0 z-40">
                <div class="container mx-auto px-4 py-4 flex justify-between items-center max-w-6xl">
                    <!-- Logotipo -->
                    <Link :href="route('storefront.index')" class="text-2xl font-black tracking-tight text-blue-600 flex items-center gap-1.5 hover:opacity-95 transition-opacity">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
                        <span>{{ settings.company_name || 'GiftJoy' }}</span>
                    </Link>

                    <!-- Menu de Navegação Dinâmico (Desktop) -->
                    <nav class="hidden md:flex items-center space-x-7">
                        <div v-for="menu in menuItems" :key="menu.id" class="relative group">
                            <!-- Item sem Submenu -->
                            <Link v-if="!menu.children || menu.children.length === 0" :href="getMenuUrl(menu)" class="text-slate-600 hover:text-blue-600 font-semibold text-sm transition-colors py-2 block">
                                {{ menu.label }}
                            </Link>

                            <!-- Item com Submenu (Dropdown) -->
                            <div v-else class="flex items-center gap-0.5 cursor-pointer py-2">
                                <span class="text-slate-600 group-hover:text-blue-600 font-semibold text-sm transition-colors">{{ menu.label }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                
                                <!-- Dropdown Menu -->
                                <div class="absolute top-full left-0 mt-1 w-52 bg-white border border-slate-100 rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 py-2">
                                    <Link v-for="child in menu.children" :key="child.id" :href="getMenuUrl(child)" class="block px-4 py-2 text-sm text-slate-600 hover:bg-blue-50 hover:text-blue-600 font-medium transition-colors">
                                        {{ child.label }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </nav>

                    <!-- Ações do Header (Carrinho e Mobile Toggle) -->
                    <div class="flex items-center gap-4">
                        <Link :href="route('storefront.cart')" class="relative p-2 bg-blue-50 text-blue-600 rounded-full transition-colors group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span v-if="cart.length > 0" class="absolute -top-1 -right-1 bg-blue-600 text-white text-xxs font-black rounded-full h-5 w-5 flex items-center justify-center border-2 border-white shadow-md">{{ cart.length }}</span>
                        </Link>

                        <!-- Mobile Menu Button -->
                        <button @click="showingMobileMenu = !showingMobileMenu" class="md:hidden p-2 text-slate-700 hover:bg-slate-100 rounded-lg">
                            <svg v-if="!showingMobileMenu" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Menu Mobile (Drawer) -->
                <div v-if="showingMobileMenu" class="md:hidden border-t border-slate-100 bg-white px-4 py-3 space-y-2 shadow-inner">
                    <div v-for="menu in menuItems" :key="menu.id" class="border-b last:border-b-0 pb-2">
                        <Link v-if="!menu.children || menu.children.length === 0" :href="getMenuUrl(menu)" @click="showingMobileMenu = false" class="block py-2 text-slate-700 hover:text-blue-600 font-semibold text-sm">
                            {{ menu.label }}
                        </Link>
                        <div v-else>
                            <button @click="openDropdown = openDropdown === menu.id ? null : menu.id" class="w-full flex justify-between items-center py-2 text-slate-700 font-semibold text-sm">
                                <span>{{ menu.label }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform transition-transform" :class="{'rotate-180': openDropdown === menu.id}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div v-if="openDropdown === menu.id" class="pl-4 space-y-2 py-1 bg-slate-50 rounded-lg">
                                <Link v-for="child in menu.children" :key="child.id" :href="getMenuUrl(child)" @click="showingMobileMenu = false" class="block py-1.5 text-xs text-slate-600 hover:text-blue-600 font-medium">
                                    {{ child.label }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- CONTEÚDO PRINCIPAL -->
            <main class="container mx-auto px-4 py-12 max-w-6xl">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-8">Finalizar Orçamento</h1>

                <div v-if="cart.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    <!-- Coluna dos Itens (Esquerda - Ocupa 2 cols) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden p-6 sm:p-8">
                            <h2 class="text-lg font-bold text-slate-800 mb-6">Produtos Selecionados</h2>
                            
                            <div class="divide-y divide-slate-100">
                                <div v-for="item in cart" :key="item.id" class="flex flex-col sm:flex-row items-start sm:items-center justify-between py-5 first:pt-0 last:pb-0 gap-4">
                                    <div class="flex items-center gap-4">
                                        <img :src="item.image" alt="Imagem do Produto" class="w-20 h-20 object-cover rounded-2xl border border-slate-100 shadow-inner bg-slate-50">
                                        <div>
                                            <h3 class="font-bold text-slate-900 text-sm">{{ item.name }}</h3>
                                            <p class="text-xs text-slate-400 mt-1">Preço unitário: <span class="font-bold text-blue-600">{{ formatCurrency(getPriceForQuantity(item)) }}</span></p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-6">
                                        <!-- Controle de Quantidade -->
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-slate-400 font-medium">Qtd:</span>
                                            <input
                                                type="number"
                                                :value="item.quantity"
                                                @input="updateQuantity(item.id, $event.target.value)"
                                                class="w-20 bg-slate-50 border-slate-200 text-slate-800 text-center text-sm font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 p-2"
                                                min="1"
                                            >
                                        </div>
                                        
                                        <!-- Botão Remover -->
                                        <button @click="removeItem(item.id)" class="p-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna do Formulário (Direita - Ocupa 1 col) -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sm:p-8 space-y-6">
                        <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Dados para o Orçamento</h2>
                        
                        <div class="space-y-4">
                            <!-- Nome -->
                            <div>
                                <label for="client_name" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome Completo</label>
                                <input v-model="form.client_name" type="text" id="client_name" class="mt-1.5 block w-full bg-slate-50 border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3" placeholder="Seu nome..." required>
                                <div v-if="form.errors.client_name" class="text-xs text-rose-600 mt-1 font-semibold">{{ form.errors.client_name }}</div>
                            </div>

                            <!-- Email & Telefone -->
                            <div class="space-y-4">
                                <div>
                                    <label for="client_email" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">E-mail corporativo</label>
                                    <input v-model="form.client_email" type="email" id="client_email" class="mt-1.5 block w-full bg-slate-50 border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3" placeholder="seu@email.com" required>
                                    <div v-if="form.errors.client_email" class="text-xs text-rose-600 mt-1 font-semibold">{{ form.errors.client_email }}</div>
                                </div>
                                <div>
                                    <label for="client_phone" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Telefone / WhatsApp</label>
                                    <input v-model="form.client_phone" type="text" id="client_phone" class="mt-1.5 block w-full bg-slate-50 border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3" placeholder="(00) 00000-0000" required>
                                    <div v-if="form.errors.client_phone" class="text-xs text-rose-600 mt-1 font-semibold">{{ form.errors.client_phone }}</div>
                                </div>
                            </div>

                            <!-- CEP & Estado -->
                            <div class="grid grid-cols-3 gap-3">
                                <div class="col-span-2">
                                    <label for="cep" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">CEP</label>
                                    <input v-model="form.cep" type="text" id="cep" @input="handleCepInput" @blur="fetchCep" class="mt-1.5 block w-full bg-slate-50 border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3" placeholder="00000-000" required>
                                    <div v-if="form.errors.cep" class="text-xs text-rose-600 mt-1 font-semibold">{{ form.errors.cep }}</div>
                                </div>
                                <div>
                                    <label for="address_state" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">UF</label>
                                    <input v-model="form.address_state" type="text" id="address_state" maxlength="2" class="mt-1.5 block w-full bg-slate-50 border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3 uppercase text-center" required>
                                    <div v-if="form.errors.address_state" class="text-xs text-rose-600 mt-1 font-semibold">{{ form.errors.address_state }}</div>
                                </div>
                            </div>

                            <!-- Rua -->
                            <div>
                                <label for="address_street" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Logradouro / Rua</label>
                                <input v-model="form.address_street" type="text" id="address_street" class="mt-1.5 block w-full bg-slate-50 border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3" placeholder="Rua, Av..." required>
                                <div v-if="form.errors.address_street" class="text-xs text-rose-600 mt-1 font-semibold">{{ form.errors.address_street }}</div>
                            </div>

                            <!-- Bairro & Cidade -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="address_neighborhood" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Bairro</label>
                                    <input v-model="form.address_neighborhood" type="text" id="address_neighborhood" class="mt-1.5 block w-full bg-slate-50 border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3" required>
                                    <div v-if="form.errors.address_neighborhood" class="text-xs text-rose-600 mt-1 font-semibold">{{ form.errors.address_neighborhood }}</div>
                                </div>
                                <div>
                                    <label for="address_city" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Cidade</label>
                                    <input v-model="form.address_city" type="text" id="address_city" class="mt-1.5 block w-full bg-slate-50 border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3" required>
                                    <div v-if="form.errors.address_city" class="text-xs text-rose-600 mt-1 font-semibold">{{ form.errors.address_city }}</div>
                                </div>
                            </div>

                            <!-- Forma de Pagamento -->
                            <div>
                                <label for="payment_method_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Forma de Pagamento</label>
                                <select v-model="form.payment_method_id" id="payment_method_id" class="mt-1.5 block w-full bg-slate-50 border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3 cursor-pointer" required>
                                    <option value="" disabled>Selecione...</option>
                                    <option v-for="method in paymentMethods" :key="method.id" :value="method.id">
                                        {{ method.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.payment_method_id" class="text-xs text-rose-600 mt-1 font-semibold">{{ form.errors.payment_method_id }}</div>
                            </div>

                            <!-- Detalhes de Personalização -->
                            <div>
                                <label for="customization_details" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Ideias de Personalização</label>
                                <textarea v-model="form.customization_details" id="customization_details" rows="3" class="mt-1.5 block w-full bg-slate-50 border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3" placeholder="Ex: Logotipo em 1 cor na caneca..."></textarea>
                                <div v-if="form.errors.customization_details" class="text-xs text-rose-600 mt-1 font-semibold">{{ form.errors.customization_details }}</div>
                            </div>
                        </div>

                        <!-- Resumo financeiro e botão final -->
                        <div class="border-t border-slate-100 pt-6 space-y-4">
                            <div class="flex justify-between items-center text-slate-900 font-extrabold text-lg">
                                <span>Total Estimado:</span>
                                <span class="text-blue-600">{{ formatCurrency(totalAmount) }}</span>
                            </div>
                            
                            <button @click="submitOrder" :disabled="form.processing" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2 hover:-translate-y-0.5 disabled:opacity-50">
                                <span>{{ form.processing ? 'Processando...' : 'Finalizar Orçamento' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mensagem de Carrinho Vazio -->
                <div class="text-center bg-white border border-slate-100 rounded-3xl p-12 max-w-lg mx-auto shadow-sm space-y-5">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center text-blue-500 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-xl font-bold text-slate-900">Seu carrinho está vazio</h2>
                        <p class="text-sm text-slate-400 font-light">Adicione canecas, copos ou brindes corporativos para solicitar sua cotação personalizada.</p>
                    </div>
                    <Link :href="route('storefront.index')" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-6 py-3 rounded-xl shadow-md transition-all">
                        Explorar Catálogo
                    </Link>
                </div>
            </main>
        </div>

        <!-- RODAPÉ ESCURO PREMIUM -->
        <footer class="bg-slate-900 text-slate-400 mt-20 border-t border-slate-800">
            <div class="container mx-auto px-4 py-16 grid grid-cols-1 md:grid-cols-4 gap-12 max-w-6xl">
                <!-- Coluna Info -->
                <div class="space-y-4">
                    <h3 class="text-white font-black text-lg tracking-tight flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
                        <span>{{ settings.company_name || 'GiftJoy' }}</span>
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed font-light">
                        Personalizando momentos e presentes com qualidade extrema, foco na experiência do cliente e entregas em tempo recorde por todo o território nacional.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <a v-if="settings.social_facebook" :href="settings.social_facebook" target="_blank" class="p-2 bg-slate-800 hover:bg-blue-600 text-slate-400 hover:text-white rounded-lg transition-colors shadow">
                            Facebook
                        </a>
                        <a v-if="settings.social_instagram" :href="settings.social_instagram" target="_blank" class="p-2 bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white rounded-lg transition-colors shadow">
                            Instagram
                        </a>
                        <a v-if="settings.social_linkedin" :href="settings.social_linkedin" target="_blank" class="p-2 bg-slate-800 hover:bg-blue-800 text-slate-400 hover:text-white rounded-lg transition-colors shadow">
                            LinkedIn
                        </a>
                    </div>
                </div>

                <!-- Coluna Links Úteis -->
                <div class="space-y-4">
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider">Empresa</h4>
                    <ul class="space-y-2.5 text-xs font-light">
                        <li><a href="#" class="hover:text-blue-400 transition-colors">Quem Somos</a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors">Nossos Produtos</a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors">Portfólio Corporativo</a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors">Termos de Uso</a></li>
                    </ul>
                </div>

                <!-- Coluna Suporte -->
                <div class="space-y-4">
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider">Suporte</h4>
                    <ul class="space-y-2.5 text-xs font-light">
                        <li><a href="#" class="hover:text-blue-400 transition-colors">Fale Conosco</a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors">Perguntas Frequentes (FAQ)</a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors">Prazo de Produção</a></li>
                        <li><a href="#" class="hover:text-blue-400 transition-colors">Logística de Entrega</a></li>
                    </ul>
                </div>

                <!-- Coluna Newsletter -->
                <div class="space-y-4">
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider">Fique por Dentro</h4>
                    <p class="text-xs text-slate-500 leading-relaxed font-light">Assine nossa newsletter e receba lançamentos de novos brindes e ofertas exclusivas direto no seu e-mail.</p>
                    <form @submit.prevent="" class="flex gap-2 max-w-sm">
                        <input type="email" placeholder="Seu e-mail..." class="w-full bg-slate-800 border-slate-700 text-slate-200 text-xs rounded-xl focus:ring-blue-500 focus:border-blue-500 p-2.5 placeholder-slate-500" required />
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 rounded-xl transition-all shadow">Assinar</button>
                    </form>
                </div>
            </div>

            <!-- Rodapé inferior com informações de registro -->
            <div class="border-t border-slate-800/80 py-8 text-center text-slate-500 text-xxs">
                <div class="container mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-4 max-w-6xl">
                    <p>&copy; {{ new Date().getFullYear() }} {{ settings.company_name || 'GiftJoy' }}. CNPJ: {{ settings.company_cnpj || '00.000.000/0000-00' }}. {{ settings.company_address || 'Endereço não informado' }}</p>
                    <div class="flex items-center gap-2">
                        <span>Versão do Sistema:</span>
                        <span v-if="$page.props.appVersion" class="inline-flex items-center px-2 py-0.5 rounded bg-slate-800 text-slate-400 font-mono transition-colors duration-200 hover:text-white cursor-pointer">
                            v{{ $page.props.appVersion }}
                        </span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
.text-xxs {
    font-size: 0.65rem;
}
</style>