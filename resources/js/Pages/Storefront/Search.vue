<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FloatingWhatsapp from '@/Components/FloatingWhatsapp.vue';

const props = defineProps({
    searchTerm: String,
    products: Object,
    categories: Array,
    settings: Object,
    menuItems: Array,
    currentSort: String,
    currentPerPage: [String, Number],
    filterOptions: Object,
    selectedFilters: Object,
});

// --- BUSCA NO CABEÇALHO ---
const headerSearchQuery = ref(props.searchTerm || '');
const executeSearch = () => {
    if (headerSearchQuery.value.trim()) {
        router.get(route('storefront.search'), { q: headerSearchQuery.value });
    }
};

// --- LÓGICA DO CARRINHO ---
const cart = ref(JSON.parse(localStorage.getItem('cart') || '[]'));

const addToCart = (product, event) => {
    event.stopPropagation();
    event.preventDefault();

    const existingProduct = cart.value.find(item => item.id === product.id);

    if (!existingProduct) {
        cart.value.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.main_image && product.main_image.path ? `/storage/${product.main_image.path}` : 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?auto=format&fit=crop&w=600&q=80',
            price_tiers: product.price_tiers,
        });
        localStorage.setItem('cart', JSON.stringify(cart.value));
        showToastNotification(`"${product.name}" foi adicionado ao orçamento!`);
    } else {
        showToastNotification(`"${product.name}" já está no seu orçamento.`);
    }
};

// --- LÓGICA DA NOTIFICAÇÃO (TOAST) ---
const toast = ref({
    show: false,
    message: '',
});

const showToastNotification = (message) => {
    toast.value.message = message;
    toast.value.show = true;
    setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

// --- LÓGICA DE WISHLIST (LOCAL) ---
const wishlist = ref(JSON.parse(localStorage.getItem('wishlist') || '[]'));
const toggleWishlist = (productId, event) => {
    event.stopPropagation();
    event.preventDefault();
    if (wishlist.value.includes(productId)) {
        wishlist.value = wishlist.value.filter(id => id !== productId);
        showToastNotification('Removido dos favoritos.');
    } else {
        wishlist.value.push(productId);
        showToastNotification('Adicionado aos favoritos!');
    }
    localStorage.setItem('wishlist', JSON.stringify(wishlist.value));
};

// --- FILTROS E ORDENAÇÃO ---
const selectedSort = ref(props.currentSort || 'latest');
const selectedPerPage = ref(props.currentPerPage || 12);

const activeThemes = ref(props.selectedFilters?.themes || []);
const activePersonalizations = ref(props.selectedFilters?.personalization_types || []);
const activeColors = ref(props.selectedFilters?.colors || []);
const activeCharacteristics = ref(props.selectedFilters?.characteristics || []);
const minPrice = ref(props.selectedFilters?.min_price || '');
const maxPrice = ref(props.selectedFilters?.max_price || '');

const applyFilters = () => {
    router.get(route('storefront.search'), {
        q: props.searchTerm,
        sort: selectedSort.value,
        per_page: selectedPerPage.value,
        themes: activeThemes.value.join(','),
        personalization_types: activePersonalizations.value.join(','),
        colors: activeColors.value.join(','),
        characteristics: activeCharacteristics.value.join(','),
        min_price: minPrice.value,
        max_price: maxPrice.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const updateFilters = applyFilters;

const changePerPage = (num) => {
    selectedPerPage.value = num;
    updateFilters();
};

watch(selectedSort, () => {
    updateFilters();
});

watch(() => props.selectedFilters, (newVal) => {
    activeThemes.value = newVal?.themes || [];
    activePersonalizations.value = newVal?.personalization_types || [];
    activeColors.value = newVal?.colors || [];
    activeCharacteristics.value = newVal?.characteristics || [];
    minPrice.value = newVal?.min_price || '';
    maxPrice.value = newVal?.max_price || '';
}, { deep: true });

const hasActiveFilters = computed(() => {
    return activeThemes.value.length > 0 ||
           activePersonalizations.value.length > 0 ||
           activeColors.value.length > 0 ||
           activeCharacteristics.value.length > 0 ||
           minPrice.value !== '' ||
           maxPrice.value !== '';
});

const clearAllFilters = () => {
    activeThemes.value = [];
    activePersonalizations.value = [];
    activeColors.value = [];
    activeCharacteristics.value = [];
    minPrice.value = '';
    maxPrice.value = '';
    applyFilters();
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

// --- LINK DO WHATSAPP ---
const whatsappUrl = computed(() => {
    if (!props.settings.company_whatsapp) return null;
    const cleanNumber = props.settings.company_whatsapp.replace(/\D/g, '');
    return `https://wa.me/${cleanNumber}`;
});

const getProductWhatsappUrl = (productName) => {
    if (!props.settings.company_whatsapp) return '#';
    const cleanNumber = props.settings.company_whatsapp.replace(/\D/g, '');
    const message = encodeURIComponent(`Olá! Gostaria de mais informações sobre o produto: ${productName}`);
    return `https://wa.me/${cleanNumber}?text=${message}`;
};

// --- MENU MOBILE ---
const showingMobileMenu = ref(false);
const openDropdown = ref(null);
</script>

<template>
    <!-- SEO tags reativas -->
    <Head>
        <title>{{ `Busca: "${searchTerm}" - ${settings.company_name || 'GiftJoy'}` }}</title>
        <meta name="description" :content="settings.seo_meta_description || 'Resultados de busca para brindes personalizados na nossa loja.'" />
        <meta name="keywords" :content="settings.seo_meta_keywords || 'brindes, personalizados, busca'" />
    </Head>

    <!-- Notificação (Toast) -->
    <div v-if="toast.show" class="fixed top-5 right-5 bg-blue-600 text-white py-3 px-5 rounded-xl shadow-2xl z-50 transform translate-y-0 transition-transform duration-300 flex items-center gap-2 border border-blue-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span class="font-medium text-sm">{{ toast.message }}</span>
    </div>

    <div class="bg-slate-50 min-h-screen font-sans antialiased text-slate-800 flex flex-col justify-between">
        <div>
            <!-- BARRA SUPERIOR DE INFORMAÇÕES (Com Redes Sociais) -->
            <div class="bg-slate-900 text-slate-300 text-xs py-2 border-b border-slate-800">
                <div class="container mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-2 max-w-6xl">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-location-dot text-blue-400"></i>
                            <span>Entregamos para todo o Brasil</span>
                        </div>
                        <!-- Ícones das Redes Sociais na Top Bar -->
                        <div class="hidden sm:flex items-center gap-3 border-l border-slate-800 pl-4">
                            <a v-if="settings.social_facebook" :href="settings.social_facebook" target="_blank" class="hover:text-blue-500 transition-colors text-sm" title="Facebook">
                                <i class="fa-brands fa-facebook"></i>
                            </a>
                            <a v-if="settings.social_instagram" :href="settings.social_instagram" target="_blank" class="hover:text-rose-500 transition-colors text-sm" title="Instagram">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                            <a v-if="settings.social_youtube" :href="settings.social_youtube" target="_blank" class="hover:text-red-600 transition-colors text-sm" title="YouTube">
                                <i class="fa-brands fa-youtube"></i>
                            </a>
                            <a v-if="whatsappUrl" :href="whatsappUrl" target="_blank" class="hover:text-green-500 transition-colors text-sm" title="WhatsApp">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <a v-if="settings.company_email" :href="`mailto:${settings.company_email}`" class="hover:text-blue-400 flex items-center gap-1.5 transition-colors">
                            <i class="fa-solid fa-envelope text-blue-400"></i>
                            <span>{{ settings.company_email }}</span>
                        </a>
                        <a v-if="settings.company_phone" :href="`tel:${settings.company_phone}`" class="hover:text-blue-400 flex items-center gap-1.5 transition-colors">
                            <i class="fa-solid fa-phone text-blue-400"></i>
                            <span>{{ settings.company_phone }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- CABEÇALHO PRINCIPAL (Com Campo de Busca Centralizado) -->
            <header class="bg-white shadow-sm sticky top-0 z-40">
                <div class="container mx-auto px-4 py-4 flex justify-between items-center max-w-6xl gap-4">
                    <!-- Logotipo -->
                    <Link :href="route('storefront.index')" class="text-2xl font-black tracking-tight text-blue-600 flex items-center gap-1.5 hover:opacity-95 transition-opacity flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
                        <span>{{ settings.company_name || 'GiftJoy' }}</span>
                    </Link>

                    <!-- Barra de busca no cabeçalho (Desktop) -->
                    <div class="hidden md:block flex-1 max-w-md mx-6">
                        <form @submit.prevent="executeSearch" class="relative">
                            <input 
                                type="text" 
                                v-model="headerSearchQuery"
                                placeholder="Digite o que você procura..." 
                                class="w-full bg-slate-50 border border-slate-200 rounded-full pl-5 pr-11 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                            />
                            <button type="submit" class="absolute right-3.5 top-2.5 text-slate-400 hover:text-blue-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                        </form>
                    </div>

                    <!-- Menu de Navegação Dinâmico (Desktop) -->
                    <nav class="hidden md:flex items-center space-x-6">
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
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <Link :href="route('storefront.cart')" class="relative p-2 bg-slate-50 hover:bg-blue-50 rounded-full transition-colors group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-700 group-hover:text-blue-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <div v-if="showingMobileMenu" class="md:hidden border-t border-slate-100 bg-white px-4 py-3 space-y-3 shadow-inner">
                    <!-- Barra de busca no mobile -->
                    <form @submit.prevent="executeSearch" class="relative">
                        <input 
                            type="text" 
                            v-model="headerSearchQuery"
                            placeholder="Digite o que você procura..." 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-10 py-2 text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                        />
                        <button type="submit" class="absolute right-3 top-2.5 text-slate-400 hover:text-blue-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                    </form>

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
            <main class="container mx-auto px-4 py-10 max-w-6xl">
                <!-- BREADCRUMB -->
                <nav class="flex text-xs font-semibold text-slate-400 mb-8 items-center gap-2">
                    <Link :href="route('storefront.index')" class="hover:text-blue-600 transition-colors">Início</Link>
                    <span>/</span>
                    <span class="text-slate-500">Busca</span>
                    <span>/</span>
                    <span class="text-slate-800 font-bold">"{{ searchTerm }}"</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <!-- SIDEBAR DA ESQUERDA (Filtros e Categorias) -->
                    <aside class="space-y-6">
                        <!-- Card de Categorias -->
                        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm space-y-4">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider border-b border-slate-50 pb-2">Categorias</h3>
                            <ul class="space-y-2.5 text-sm">
                                <li v-for="cat in categories" :key="cat.id">
                                    <Link 
                                        :href="route('storefront.category.show', { category: cat.slug })" 
                                        class="flex items-center justify-between group text-slate-600 hover:text-blue-600 font-medium transition-colors"
                                    >
                                        <span>{{ cat.name }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-300 group-hover:text-blue-500 transform group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <!-- Card de Filtros Dinâmicos -->
                        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm space-y-6">
                            <!-- Filtro de Preço -->
                            <div class="space-y-3 pb-4 border-b border-slate-100">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Filtrar por Preço</h4>
                                <div class="flex items-center gap-2">
                                    <div class="relative flex-1">
                                        <span class="absolute left-2.5 top-2 text-xxs text-slate-400 font-bold">Mín</span>
                                        <input 
                                            type="number" 
                                            v-model="minPrice" 
                                            placeholder="0,00" 
                                            class="w-full pl-8 pr-2 py-1.5 bg-slate-50 border-slate-200 rounded-xl text-xs font-bold focus:ring-blue-500 focus:border-blue-500"
                                            @keyup.enter="applyFilters"
                                        />
                                    </div>
                                    <span class="text-slate-400 text-xs">-</span>
                                    <div class="relative flex-1">
                                        <span class="absolute left-2.5 top-2 text-xxs text-slate-400 font-bold">Máx</span>
                                        <input 
                                            type="number" 
                                            v-model="maxPrice" 
                                            placeholder="0,00" 
                                            class="w-full pl-8 pr-2 py-1.5 bg-slate-50 border-slate-200 rounded-xl text-xs font-bold focus:ring-blue-500 focus:border-blue-500"
                                            @keyup.enter="applyFilters"
                                        />
                                    </div>
                                </div>
                                <button 
                                    @click="applyFilters" 
                                    class="w-full bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-600 text-xxs font-black py-2 rounded-xl transition-all shadow-sm uppercase tracking-wider mt-2 block text-center"
                                >
                                    Filtrar
                                </button>
                            </div>

                            <!-- Temas -->
                            <div v-if="filterOptions.themes && filterOptions.themes.length > 0" class="space-y-3 pb-4 border-b border-slate-100">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Temas</h4>
                                <div class="space-y-2 text-xs text-slate-600 font-medium max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                    <label v-for="theme in filterOptions.themes" :key="theme.id" class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                                        <input 
                                            type="checkbox" 
                                            :value="theme.slug" 
                                            v-model="activeThemes" 
                                            @change="applyFilters"
                                            class="rounded border-slate-200 text-blue-600 focus:ring-blue-500"
                                        >
                                        <span>{{ theme.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Personalização -->
                            <div v-if="filterOptions.personalization_types && filterOptions.personalization_types.length > 0" class="space-y-3 pb-4 border-b border-slate-100">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Personalização</h4>
                                <div class="space-y-2 text-xs text-slate-600 font-medium max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                    <label v-for="pers in filterOptions.personalization_types" :key="pers.id" class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                                        <input 
                                            type="checkbox" 
                                            :value="pers.slug" 
                                            v-model="activePersonalizations" 
                                            @change="applyFilters"
                                            class="rounded border-slate-200 text-blue-600 focus:ring-blue-500"
                                        >
                                        <span>{{ pers.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Cores -->
                            <div v-if="filterOptions.colors && filterOptions.colors.length > 0" class="space-y-3 pb-4 border-b border-slate-100">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Cores</h4>
                                <div class="space-y-2 text-xs text-slate-600 font-medium max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                    <label v-for="color in filterOptions.colors" :key="color.id" class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                                        <input 
                                            type="checkbox" 
                                            :value="color.slug" 
                                            v-model="activeColors" 
                                            @change="applyFilters"
                                            class="rounded border-slate-200 text-blue-600 focus:ring-blue-500"
                                        >
                                        <span class="w-3.5 h-3.5 rounded-full border border-slate-200 block shadow-sm flex-shrink-0" :style="{ backgroundColor: color.hex_code || '#fff' }"></span>
                                        <span>{{ color.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Características -->
                            <div v-if="filterOptions.characteristics && filterOptions.characteristics.length > 0" class="space-y-3">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Características</h4>
                                <div class="space-y-2 text-xs text-slate-600 font-medium max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                    <label v-for="char in filterOptions.characteristics" :key="char.id" class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                                        <input 
                                            type="checkbox" 
                                            :value="char.slug" 
                                            v-model="activeCharacteristics" 
                                            @change="applyFilters"
                                            class="rounded border-slate-200 text-blue-600 focus:ring-blue-500"
                                        >
                                        <span>{{ char.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Botão Limpar Filtros -->
                            <button 
                                v-if="hasActiveFilters" 
                                @click="clearAllFilters" 
                                class="w-full bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 text-xxs font-black py-2.5 rounded-xl transition-all shadow-sm uppercase tracking-wider block text-center mt-4"
                            >
                                Limpar Todos os Filtros
                            </button>
                        </div>
                    </aside>

                    <!-- CONTEÚDO DA LISTAGEM DE RESULTADOS -->
                    <section class="lg:col-span-3 space-y-6">
                        <!-- Cabeçalho da Busca -->
                        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-2">
                            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Resultado da busca</h1>
                            <p class="text-sm text-slate-500 font-light leading-relaxed">
                                Exibindo resultados encontrados para: <strong class="text-slate-800 font-extrabold">"{{ searchTerm }}"</strong>
                            </p>
                        </div>

                        <!-- Barra de Controles/Ordenação -->
                        <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4 text-xs font-semibold text-slate-500">
                            <!-- Mostrar itens -->
                            <div class="flex items-center gap-3">
                                <span>Mostrar:</span>
                                <div class="flex gap-2">
                                    <button 
                                        v-for="num in [9, 12, 18, 24]" 
                                        :key="num"
                                        @click="changePerPage(num)"
                                        class="px-2.5 py-1 rounded-lg border transition-all"
                                        :class="{'bg-blue-600 text-white border-blue-600 shadow-sm': selectedPerPage === num, 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100': selectedPerPage !== num}"
                                    >
                                        {{ num }}
                                    </button>
                                </div>
                            </div>

                            <!-- Ordenação -->
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <span>Ordenar por:</span>
                                <select 
                                    v-model="selectedSort" 
                                    class="bg-slate-50 border-slate-200 text-slate-700 text-xs font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 p-2 cursor-pointer w-full sm:w-56"
                                >
                                    <option value="latest">Mais recente</option>
                                    <option value="name_asc">Nome (A - Z)</option>
                                    <option value="price_asc">Menor preço</option>
                                    <option value="price_desc">Maior preço</option>
                                </select>
                            </div>
                        </div>

                        <!-- Grid de Produtos -->
                        <div v-if="products.data && products.data.length > 0">
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                                <div v-for="product in products.data" :key="product.id" class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 relative flex flex-col justify-between group">
                                    <!-- Badges -->
                                    <div class="absolute top-3 left-3 z-10 flex flex-col gap-1.5">
                                        <span v-if="product.track_stock && product.stock_quantity <= 0" class="bg-rose-700 text-white text-xxs font-black px-2.5 py-1 rounded-lg shadow-sm uppercase tracking-wider">Esgotado</span>
                                        <span v-else-if="product.promotional_price" class="bg-rose-600 text-white text-xxs font-black px-2.5 py-1 rounded-lg shadow-sm uppercase tracking-wider">Promoção</span>
                                        <span v-else class="bg-slate-900 text-white text-xxs font-black px-2.5 py-1 rounded-lg shadow-sm uppercase tracking-wider">Premium</span>
                                    </div>

                                    <!-- Botão de Wishlist -->
                                    <button 
                                        @click="toggleWishlist(product.id, $event)" 
                                        class="absolute top-3 right-3 z-10 p-2 bg-white/80 hover:bg-white backdrop-blur text-slate-400 hover:text-rose-600 rounded-full transition-all shadow-sm"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 fill-current" :class="{'text-rose-600': wishlist.includes(product.id), 'text-transparent': !wishlist.includes(product.id)}" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                    </button>

                                    <!-- Imagem do Card -->
                                    <Link :href="route('storefront.product.show', { product: product.slug })" class="block aspect-square w-full bg-slate-50 border-b border-slate-50 relative overflow-hidden flex items-center justify-center p-4">
                                        <img :src="product.images && product.images.length > 0 ? `/storage/${product.images.find(img => img.is_main)?.path || product.images[0].path}` : 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?auto=format&fit=crop&w=600&q=80'" :alt="product.name" class="max-w-full max-h-full object-contain transform group-hover:scale-103 transition-transform duration-500">
                                    </Link>

                                    <!-- Corpo do Card -->
                                    <div class="p-5 space-y-3 flex-grow flex flex-col justify-between">
                                        <div class="space-y-1">
                                            <!-- Estrelinhas -->
                                            <div class="flex items-center text-amber-400 gap-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                                                <span class="text-xxs font-bold text-slate-400 ml-1">5.0</span>
                                            </div>
                                            
                                            <Link :href="route('storefront.product.show', { product: product.slug })" class="block">
                                                <h3 class="font-extrabold text-slate-800 text-sm leading-tight hover:text-blue-600 transition-colors line-clamp-2 h-10">{{ product.name }}</h3>
                                            </Link>
                                        </div>

                                        <!-- Preço e Botões -->
                                        <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                                            <div class="flex items-center justify-between">
                                                <div class="flex flex-col">
                                                    <span class="text-xxs text-slate-400 font-bold">A partir de</span>
                                                    <div class="flex items-baseline gap-1">
                                                        <span v-if="product.promotional_price" class="font-black text-slate-900 text-sm">R$ {{ parseFloat(product.promotional_price).toFixed(2) }}</span>
                                                        <span v-else class="font-black text-slate-900 text-sm">R$ {{ parseFloat(product.price).toFixed(2) }}</span>
                                                        <span v-if="product.promotional_price" class="text-xxs text-slate-400 line-through">R$ {{ parseFloat(product.price).toFixed(2) }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <button 
                                                @click="addToCart(product, $event)" 
                                                :disabled="product.track_stock && product.stock_quantity <= 0"
                                                class="w-full font-bold py-2 px-4 rounded-xl transition-all duration-200 text-xs flex items-center justify-center gap-1.5 shadow-sm"
                                                :class="product.track_stock && product.stock_quantity <= 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-350' : 'bg-blue-600 hover:bg-blue-700 text-white'"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                <span>{{ product.track_stock && product.stock_quantity <= 0 ? 'Sem estoque' : 'Adicionar ao orçamento' }}</span>
                                            </button>

                                            <a 
                                                v-if="settings.company_whatsapp"
                                                :href="getProductWhatsappUrl(product.name)"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                @click.stop
                                                class="w-full border border-[#25D366] text-[#25D366] hover:bg-[#25D366] hover:text-white font-bold py-1.5 px-4 rounded-xl transition-all duration-200 text-xs flex items-center justify-center gap-1.5"
                                            >
                                                <span>Dúvidas pelo whatsapp</span>
                                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Paginação -->
                            <div v-if="products.links && products.links.length > 3" class="mt-12 flex justify-center gap-1.5">
                                <Component
                                    v-for="(link, i) in products.links"
                                    :key="i"
                                    :is="link.url ? 'Link' : 'span'"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    class="px-4 py-2 text-xs font-bold rounded-xl transition-all"
                                    :class="{
                                        'bg-blue-600 text-white shadow-md': link.active,
                                        'bg-white text-slate-600 border border-slate-100 hover:bg-slate-50': !link.active && link.url,
                                        'text-slate-300 cursor-not-allowed': !link.url
                                    }"
                                />
                            </div>
                        </div>

                        <!-- Nenhum produto encontrado -->
                        <div v-else class="text-center bg-white border border-slate-100 rounded-2xl p-12 shadow-sm space-y-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <h2 class="text-lg font-bold text-slate-900">Nenhum resultado encontrado</h2>
                            <p class="text-xs text-slate-400 font-light">Tente buscar por termos mais genéricos ou verifique a grafia.</p>
                        </div>
                    </section>
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

        <!-- Botão WhatsApp Flutuante -->
        <FloatingWhatsapp :settings="settings" />
    </div>
</template>

<style>
.text-xxs {
    font-size: 0.65rem;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
