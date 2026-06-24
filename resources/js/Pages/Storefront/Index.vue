<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    products: Object,
    categories: Array,
    settings: Object,
    menuItems: Array,
    featuredCategories: Array,
    selectedCategoryId: [String, Number],
});

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

// --- IMAGENS DE FALLBACK DAS CATEGORIAS ---
const categoryFallbacks = [
    'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?auto=format&fit=crop&w=600&q=80', // Canecas
    'https://images.unsplash.com/photo-1577937927133-66ef06acdf18?auto=format&fit=crop&w=600&q=80', // Copos Térmicos
    'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80', // Copos & Cálices
    'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80', // Kits Corporativos
];

const getCategoryImage = (category, index) => {
    if (category.image_path) {
        return `/storage/${category.image_path}`;
    }
    return categoryFallbacks[index % categoryFallbacks.length];
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

const activeCategoryName = computed(() => {
    if (!props.selectedCategoryId) return '';
    const cat = props.categories.find(c => c.id == props.selectedCategoryId);
    return cat ? cat.name : '';
});

// --- MENU MOBILE ---
const showingMobileMenu = ref(false);
const openDropdown = ref(null);
</script>

<template>
    <Head title="Vitrine de Produtos - Brindes Personalizados" />

    <!-- Notificação (Toast) -->
    <div v-if="toast.show" class="fixed top-5 right-5 bg-blue-600 text-white py-3 px-5 rounded-xl shadow-2xl z-50 transform translate-y-0 transition-transform duration-300 flex items-center gap-2 border border-blue-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span class="font-medium text-sm">{{ toast.message }}</span>
    </div>

    <div class="bg-slate-50 min-h-screen font-sans antialiased text-slate-800">
        
        <!-- BARRA SUPERIOR DE INFORMAÇÕES -->
        <div class="bg-slate-900 text-slate-300 text-xs py-2 border-b border-slate-800">
            <div class="container mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-2">
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
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
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
                    <Link :href="route('storefront.cart')" class="relative p-2 bg-slate-50 hover:bg-blue-50 rounded-full transition-colors group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-700 group-hover:text-blue-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span v-if="cart.length > 0" class="absolute -top-1 -right-1 bg-blue-600 text-white text-xxs font-black rounded-full h-5 w-5 flex items-center justify-center border-2 border-white shadow-md animate-bounce-subtle">{{ cart.length }}</span>
                    </Link>

                    <!-- Mobile Menu Button -->
                    <button @click="showingMobileMenu = !showingMobileMenu" class="md:hidden p-2 text-slate-700 hover:bg-slate-100 rounded-lg">
                        <svg v-if="!showingMobileMenu" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
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

        <!-- SEÇÃO HERO (BANNER SPLIT PREMIUM) -->
        <section class="bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700 text-white overflow-hidden py-16 lg:py-24">
            <div class="container mx-auto px-4 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Textos -->
                <div class="space-y-6 lg:max-w-xl animate-fade-in-up">
                    <span class="inline-block bg-blue-500 bg-opacity-30 text-blue-200 border border-blue-400 border-opacity-40 rounded-full px-4 py-1 text-xs font-bold tracking-wider uppercase">Canecas & Copos que Celebram Você</span>
                    <h1 class="text-4xl sm:text-5xl font-black leading-tight tracking-tight">
                        Brindes Personalizados que Elevam sua Marca
                    </h1>
                    <p class="text-blue-100 text-base sm:text-lg leading-relaxed font-light">
                        Criamos brindes corporativos premium com a identidade visual da sua marca. Acabamentos finos, durabilidade extrema e o melhor design para encantar seus clientes e equipe.
                    </p>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 pt-4">
                        <a href="#produtos" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-8 py-3.5 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2 hover:-translate-y-0.5">
                            <span>Personalizar Agora</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </a>
                        <a href="#categorias" class="bg-white bg-opacity-10 hover:bg-opacity-20 text-white border border-white border-opacity-35 font-bold px-8 py-3.5 rounded-xl transition-colors duration-200 flex items-center justify-center gap-2">
                            <span>Explorar Catálogo</span>
                        </a>
                    </div>
                </div>

                <!-- Imagem do Hero -->
                <div class="relative flex justify-center lg:justify-end animate-fade-in">
                    <div class="absolute -top-10 -left-10 w-44 h-44 bg-blue-400 rounded-full mix-blend-multiply filter blur-2xl opacity-20 animate-blob"></div>
                    <div class="absolute -bottom-10 -right-10 w-44 h-44 bg-indigo-400 rounded-full mix-blend-multiply filter blur-2xl opacity-20 animate-blob animation-delay-2000"></div>
                    <div class="relative bg-white bg-opacity-5 p-4 rounded-3xl border border-white border-opacity-15 shadow-2xl">
                        <img src="/images/hero_showcase.png" alt="Showcase de Mugs Personalizados" class="w-full max-w-lg object-cover rounded-2xl shadow-inner transform hover:scale-102 transition-transform duration-500">
                    </div>
                </div>
            </div>
        </section>

        <!-- EXPLORAR CATEGORIAS (GRADE ASSIMÉTRICA) -->
        <section id="categorias" class="container mx-auto px-4 py-16">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Explorar Categorias</h2>
                    <p class="text-sm text-slate-500 mt-1">Encontre o modelo perfeito para cada ocasião especial</p>
                </div>
                <a href="#produtos" @click="form.category_id = ''" class="text-sm font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 group">
                    <span>Ver todos os produtos</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>

            <!-- Grade de Categorias em Cards Redondos (baseada no mockup de referência) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                <!-- Card Todos os Produtos -->
                <Link :href="route('storefront.index')" class="flex flex-col items-center group text-center space-y-3">
                    <div class="w-full aspect-[4/5] bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex flex-col items-center justify-center p-6 shadow-sm group-hover:shadow-md transition-all duration-300 transform group-hover:-translate-y-1 relative overflow-hidden">
                        <div class="absolute -top-6 -right-6 w-20 h-20 bg-white/10 rounded-full"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    </div>
                    <span class="text-xs font-black tracking-wider text-slate-800 uppercase group-hover:text-blue-600 transition-colors">Todos os Produtos</span>
                </Link>

                <!-- Cards das Categorias -->
                <Link v-for="(cat, index) in categories" :key="cat.id" :href="route('storefront.category.show', { category: cat.slug })" class="flex flex-col items-center group text-center space-y-3">
                    <div class="w-full aspect-[4/5] bg-slate-100 rounded-2xl overflow-hidden shadow-sm group-hover:shadow-md transition-all duration-300 transform group-hover:-translate-y-1 relative">
                        <img :src="getCategoryImage(cat, index)" alt="Imagem Categoria" class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <span class="text-xs font-black tracking-wider text-slate-800 uppercase group-hover:text-blue-600 transition-colors">{{ cat.name }}</span>
                </Link>
            </div>
        </section>

        <!-- VITRINE DE PRODUTOS (MAIS VENDIDOS) -->
        <main id="produtos" class="container mx-auto px-4 py-16 border-t border-slate-200/60">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Mais Vendidos</h2>
                    <p class="text-sm text-slate-500 mt-1">Os favoritos da nossa comunidade personalizados para você</p>
                </div>

                <!-- Categoria selecionada / Filtro ativo -->
                <div v-if="selectedCategoryId" class="flex items-center gap-2 bg-blue-50 text-blue-800 px-3 py-1.5 rounded-full border border-blue-100 text-xs font-semibold">
                    <span>Filtro: <strong class="text-blue-900">{{ activeCategoryName }}</strong></span>
                    <Link :href="route('storefront.index')" class="hover:bg-blue-100 rounded-full p-0.5 text-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </Link>
                </div>
            </div>

            <!-- Grelha de Cards de Produto -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <template v-for="product in products.data" :key="product.id">
                    <Link
                        v-if="product && product.slug"
                        :href="route('storefront.product.show', { product: product.slug })"
                        class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col justify-between group hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 relative"
                    >
                        <!-- Badge de Destaque / Promoção -->
                        <span v-if="product.promotional_price" class="absolute top-4 left-4 bg-rose-600 text-white font-bold text-xxs px-2.5 py-1 rounded-full uppercase tracking-wider z-10 shadow">Oferta</span>
                        <span v-else class="absolute top-4 left-4 bg-slate-900 text-slate-100 font-bold text-xxs px-2.5 py-1 rounded-full uppercase tracking-wider z-10 shadow">Destaque</span>

                        <!-- Botão Favoritar (Coração Local) -->
                        <button @click="toggleWishlist(product.id, $event)" class="absolute top-4 right-4 p-2 bg-white/95 backdrop-blur hover:bg-slate-50 text-slate-400 hover:text-rose-500 rounded-full z-10 transition-colors shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" :fill="wishlist.includes(product.id) ? 'currentColor' : 'none'" :class="{'text-rose-500': wishlist.includes(product.id)}" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>

                        <!-- Foto do Produto com Hover Zoom -->
                        <div class="h-60 w-full overflow-hidden bg-slate-100 relative">
                            <img :src="product.main_image && product.main_image.path ? `/storage/${product.main_image.path}` : 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?auto=format&fit=crop&w=600&q=80'" alt="Imagem do Produto" class="w-full h-full object-cover transform group-hover:scale-104 transition-transform duration-500">
                        </div>

                        <!-- Detalhes do Produto -->
                        <div class="p-5 flex-grow flex flex-col justify-between">
                            <div class="space-y-1.5">
                                <span class="text-xxs text-slate-400 font-bold uppercase tracking-wider">{{ product.category ? product.category.name : 'Brinde' }}</span>
                                <h3 class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors text-sm line-clamp-2 leading-snug">{{ product.name }}</h3>
                                
                                <!-- Estrelas de Avaliação Mocks (UX Premium) -->
                                <div class="flex items-center gap-1 py-0.5">
                                    <div class="flex text-amber-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                                    </div>
                                    <span class="text-slate-400 text-xxs">(5.0)</span>
                                </div>
                            </div>

                            <!-- Preços e Botão Adicionar -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col gap-3 justify-end">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-slate-400 text-xxs font-semibold">A partir de:</span>
                                    <span v-if="product.promotional_price" class="text-base font-black text-rose-600">
                                        R$ {{ parseFloat(product.promotional_price).toFixed(2) }}
                                        <span class="text-xs text-slate-400 font-normal line-through ml-1.5">R$ {{ parseFloat(product.price).toFixed(2) }}</span>
                                    </span>
                                    <span v-else class="text-base font-black text-slate-900">
                                        R$ {{ parseFloat(product.price).toFixed(2) }}
                                    </span>
                                </div>
                                <button @click="addToCart(product, $event)" class="w-full bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white font-bold py-2.5 px-4 rounded-xl transition-all duration-200 text-xs flex items-center justify-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>Adicionar ao orçamento</span>
                                </button>
                            </div>
                        </div>
                    </Link>
                </template>
            </div>

            <!-- Paginação -->
            <div v-if="products.links.length > 3" class="mt-12 flex justify-center space-x-1">
                <Link
                    v-for="(link, index) in products.links"
                    :key="index"
                    :href="link.url"
                    v-html="link.label"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition-colors"
                    :class="{
                        'bg-blue-600 text-white shadow': link.active,
                        'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50': !link.active && link.url,
                        'bg-slate-100 text-slate-300 cursor-not-allowed border border-slate-100': !link.url
                    }"
                />
            </div>
        </main>

        <!-- SEÇÃO DE BENEFÍCIOS -->
        <section class="bg-blue-50/50 py-16 border-y border-slate-200/50">
            <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card Benefício 1 -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex gap-4 items-start">
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Design Ilimitado</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Customize e simule suas ideias com infinitas opções de cores, fontes e posicionamento do seu logo.</p>
                    </div>
                </div>

                <!-- Card Benefício 2 -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex gap-4 items-start">
                    <div class="p-3 bg-rose-100 text-rose-600 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Produção Expressa</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Processamento rápido do orçamento e produção acelerada para entregar seus brindes no menor tempo.</p>
                    </div>
                </div>

                <!-- Card Benefício 3 -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex gap-4 items-start">
                    <div class="p-3 bg-amber-100 text-amber-600 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Qualidade Premium</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Materiais cuidadosamente selecionados e processos de estamparia e gravação a laser de alta definição.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- DEPOIMENTOS DE CLIENTES -->
        <section class="container mx-auto px-4 py-16">
            <div class="text-center max-w-xl mx-auto mb-12">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Depoimentos</h2>
                <p class="text-sm text-slate-500 mt-1">O que nossos clientes dizem sobre a experiência conosco</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testemunho 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 space-y-4">
                    <div class="flex text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                    </div>
                    <p class="text-xs text-slate-500 italic leading-relaxed">
                        "Ficou fantástico! Impressionou a equipe e os nossos parceiros comerciais. A gravação do nosso logotipo ficou perfeitamente nítida nos copos térmicos. Super recomendo!"
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&h=100&q=80" alt="Ana Silva" class="w-10 h-10 object-cover rounded-full">
                        <div>
                            <h4 class="font-bold text-slate-900 text-xs">Ana Silva</h4>
                            <p class="text-xxs text-slate-400 font-medium">Diretora de Marketing</p>
                        </div>
                    </div>
                </div>

                <!-- Testemunho 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 space-y-4">
                    <div class="flex text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                    </div>
                    <p class="text-xs text-slate-500 italic leading-relaxed">
                        "Precisei de um lote corporativo urgente para um evento institucional e a entrega foi realizada antes do prazo acordado. O suporte ao cliente foi muito ágil e profissional durante todo o processo."
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&h=100&q=80" alt="Ricardo Oliveira" class="w-10 h-10 object-cover rounded-full">
                        <div>
                            <h4 class="font-bold text-slate-900 text-xs">Ricardo Oliveira</h4>
                            <p class="text-xxs text-slate-400 font-medium">Coordenador de Eventos</p>
                        </div>
                    </div>
                </div>

                <!-- Testemunho 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 space-y-4">
                    <div class="flex text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192z"/></svg>
                    </div>
                    <p class="text-xs text-slate-500 italic leading-relaxed">
                        "Encomendei as canecas personalizadas para os convidados do meu casamento e o resultado foi maravilhoso. Todos elogiaram a qualidade e o design moderno da estampa!"
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100&h=100&q=80" alt="Juliana Costa" class="w-10 h-10 object-cover rounded-full">
                        <div>
                            <h4 class="font-bold text-slate-900 text-xs">Juliana Costa</h4>
                            <p class="text-xxs text-slate-400 font-medium font-medium">Noiva</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- BANNER DE CALL-TO-ACTION (CTA) -->
        <section class="container mx-auto px-4 py-8">
            <div class="bg-slate-900 text-white rounded-3xl p-8 sm:p-12 text-center space-y-6 relative overflow-hidden shadow-2xl">
                <!-- Efeitos decorativos de fundo -->
                <div class="absolute top-0 right-0 w-80 h-80 bg-blue-600/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-80 h-80 bg-indigo-600/10 rounded-full blur-3xl"></div>
                
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight max-w-2xl mx-auto">Pronto para dar vida à sua ideia?</h2>
                <p class="text-sm text-slate-400 max-w-xl mx-auto leading-relaxed">
                    Fale agora com o nosso consultor especializado para tirar dúvidas sobre grandes tiragens, prazos especiais ou projetos sob medida para sua empresa.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
                    <a v-if="settings.company_whatsapp" :href="`https://wa.me/${settings.company_whatsapp}?text=Olá! Gostaria de fazer um orçamento de brindes personalizados.`" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-8 py-3.5 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.003 5.37 5.378 0 12.007 0c3.211.002 6.228 1.252 8.497 3.522 2.268 2.27 3.515 5.289 3.515 8.494 0 6.63-5.374 12-12.007 12-1.996 0-3.957-.497-5.717-1.442L0 24zm6.59-4.846c1.652.981 3.27 1.488 4.965 1.488 5.418 0 9.827-4.387 9.83-9.782.004-2.614-1.01-5.074-2.855-6.924C16.68 2.083 14.225 1.062 11.62 1.06 6.2 1.06 1.79 5.447 1.787 10.844c0 1.724.46 3.411 1.332 4.92l-.997 3.636 3.73-.974.202.12z"/></svg>
                        <span>Falar com Consultor via WhatsApp</span>
                    </a>
                    <a href="#" class="bg-white bg-opacity-10 hover:bg-opacity-15 text-white border border-white border-opacity-20 font-bold px-8 py-3.5 rounded-xl transition-all flex items-center justify-center">
                        <span>Ver Portfólio Corporativo</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- RODAPÉ ESCURO PREMIUM -->
        <footer class="bg-slate-900 text-slate-400 mt-20 border-t border-slate-800">
            <div class="container mx-auto px-4 py-16 grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Coluna Info -->
                <div class="space-y-4">
                    <h3 class="text-white font-black text-lg tracking-tight flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
                        <span>{{ settings.company_name || 'GiftJoy' }}</span>
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed font-light">
                        Personalizando momentos e presentes com qualidade extrema, foco na experiência do cliente e entregas em tempo recorde por todo o território nacional.
                    </p>
                    <!-- Redes Sociais com ícones estilizados -->
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
                <div class="container mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-4">
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
/* Pequenos estilos adicionais para efeitos modernos */
.text-xxs {
    font-size: 0.65rem;
}
.text-xxs {
    font-size: 0.65rem;
}
@keyframes blob {
    0%, 100% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
}
.animate-blob {
    animation: blob 10s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>