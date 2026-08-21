<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FloatingWhatsapp from '@/Components/FloatingWhatsapp.vue';

const props = defineProps({
    products: Object,
    categories: Array,
    settings: Object,
    menuItems: Array,
    featuredCategories: Array,
    selectedCategoryId: [String, Number],
    banners: Array,
    activePopup: Object,
    highlightedMenuItems: Array,
});

// --- LÓGICA DO CARROSSEL DE BANNERS ---
const activeSlide = ref(0);
let carouselInterval = null;

const nextSlide = () => {
    if (props.banners && props.banners.length > 0) {
        activeSlide.value = (activeSlide.value + 1) % props.banners.length;
    }
};

const prevSlide = () => {
    if (props.banners && props.banners.length > 0) {
        activeSlide.value = (activeSlide.value - 1 + props.banners.length) % props.banners.length;
    }
};

const setSlide = (idx) => {
    activeSlide.value = idx;
    resetAutoplay();
};

const resetAutoplay = () => {
    if (carouselInterval) clearInterval(carouselInterval);
    carouselInterval = setInterval(nextSlide, 5000);
};

// --- LÓGICA DO POPUP PROMOCIONAL ---
const showPromoPopup = ref(false);
const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 });
let countdownInterval = null;

const startCountdown = (endTimeStr) => {
    if (!endTimeStr) return;
    const targetTime = new Date(endTimeStr).getTime();

    const updateTimer = () => {
        const now = new Date().getTime();
        const diff = targetTime - now;

        if (diff <= 0) {
            countdown.value = { days: 0, hours: 0, minutes: 0, seconds: 0 };
            if (countdownInterval) clearInterval(countdownInterval);
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        countdown.value = { days, hours, minutes, seconds };
    };

    updateTimer();
    countdownInterval = setInterval(updateTimer, 1000);
};

const closePromoPopup = () => {
    showPromoPopup.value = false;
    if (props.activePopup) {
        sessionStorage.setItem('promo_popup_closed_' + props.activePopup.id, 'true');
    }
};

onMounted(() => {
    if (props.banners && props.banners.length > 1) {
        resetAutoplay();
    }

    if (props.activePopup) {
        const closed = sessionStorage.getItem('promo_popup_closed_' + props.activePopup.id);
        if (!closed) {
            setTimeout(() => {
                showPromoPopup.value = true;
            }, 800);

            if (props.activePopup.has_countdown && props.activePopup.countdown_end) {
                startCountdown(props.activePopup.countdown_end);
            }
        }
    }
});

onUnmounted(() => {
    if (carouselInterval) clearInterval(carouselInterval);
    if (countdownInterval) clearInterval(countdownInterval);
});

// --- LINK DO WHATSAPP DE ACORDO COM O PRODUTO ---
const getProductWhatsappUrl = (productName) => {
    if (!props.settings.company_whatsapp) return '#';
    const cleanNumber = props.settings.company_whatsapp.replace(/\D/g, '');
    const message = encodeURIComponent(`Olá! Gostaria de mais informações sobre o produto: ${productName}`);
    return `https://wa.me/${cleanNumber}?text=${message}`;
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

// --- BUSCA NO CABEÇALHO ---
const headerSearchQuery = ref('');
const executeSearch = () => {
    if (headerSearchQuery.value.trim()) {
        router.get(route('storefront.search'), { q: headerSearchQuery.value });
    }
};

// --- LINK DO WHATSAPP ---
const whatsappUrl = computed(() => {
    if (!props.settings.company_whatsapp) return null;
    const cleanNumber = props.settings.company_whatsapp.replace(/\D/g, '');
    return `https://wa.me/${cleanNumber}`;
});

// --- MENU MOBILE ---
const showingMobileMenu = ref(false);
const openDropdown = ref(null);
</script>

<template>
    <Head>
        <title>{{ settings.seo_meta_title || "Vitrine de Produtos - Brindes Personalizados" }}</title>
        <meta name="description" :content="settings.seo_meta_description || 'Confira nossos produtos personalizados.'" />
        <meta name="keywords" :content="settings.seo_meta_keywords || 'brindes, personalizados, copos, canecas'" />

        <!-- URL Canônica -->
        <link rel="canonical" :href="$page.props.current_url" />

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="$page.props.current_url" />
        <meta property="og:title" :content="settings.seo_meta_title || 'Vitrine de Produtos - Brindes Personalizados'" />
        <meta property="og:description" :content="settings.seo_meta_description || 'Confira nossos produtos personalizados.'" />
        <meta property="og:image" content="/images/hero_showcase.png" />

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:url" :content="$page.props.current_url" />
        <meta property="twitter:title" :content="settings.seo_meta_title || 'Vitrine de Produtos - Brindes Personalizados'" />
        <meta property="twitter:description" :content="settings.seo_meta_description || 'Confira nossos produtos personalizados.'" />
        <meta property="twitter:image" content="/images/hero_showcase.png" />
    </Head>

    <!-- Notificação (Toast) -->
    <div v-if="toast.show" class="fixed top-5 right-5 bg-blue-600 text-white py-3 px-5 rounded-xl shadow-2xl z-50 transform translate-y-0 transition-transform duration-300 flex items-center gap-2 border border-blue-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span class="font-medium text-sm">{{ toast.message }}</span>
    </div>

    <div class="bg-slate-50 min-h-screen font-sans antialiased text-slate-800">
        
        <!-- BARRA SUPERIOR DE INFORMAÇÕES -->
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

        <!-- CABEÇALHO PRINCIPAL -->
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

            <!-- MENU EM DESTAQUE (SUB-HEADER) -->
            <div v-if="highlightedMenuItems && highlightedMenuItems.length > 0" class="border-t border-slate-100 py-2.5 overflow-x-auto scrollbar-none bg-slate-50/50">
                <div class="container mx-auto px-4 max-w-6xl flex justify-start md:justify-center items-center gap-6 whitespace-nowrap">
                    <Link 
                        v-for="(item, idx) in highlightedMenuItems" 
                        :key="idx" 
                        :href="item.url" 
                        class="text-xs font-bold text-slate-700 hover:text-blue-600 transition-all tracking-wide uppercase px-2.5 py-1 rounded-lg"
                        :class="item.type === 'product' ? 'bg-blue-600 text-white shadow-sm hover:text-white hover:bg-blue-700' : 'hover:bg-slate-200/50'"
                    >
                        {{ item.label }}
                    </Link>
                </div>
            </div>
        </header>

        <!-- SEÇÃO HERO CARROSSEL DINÂMICO -->
        <section class="relative bg-slate-900 text-white overflow-hidden shadow-inner select-none h-[260px] sm:h-[350px] md:h-[400px]">
            <!-- Se temos banners cadastrados -->
            <div v-if="banners && banners.length > 0" class="relative w-full h-full">
                <div 
                    v-for="(banner, idx) in banners" 
                    :key="banner.id" 
                    class="absolute inset-0 w-full h-full transition-opacity duration-700 ease-in-out flex items-center"
                    :class="activeSlide === idx ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'"
                >
                    <!-- Imagem de Fundo -->
                    <div 
                        class="absolute inset-0 bg-cover bg-center transition-transform duration-[10000ms]"
                        :class="activeSlide === idx ? 'scale-105' : 'scale-100'"
                        :style="{ backgroundImage: `url('/storage/${banner.image_path}')` }"
                    ></div>
                    
                    <!-- Overlay Gradiente Escuro -->
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-900/50 to-transparent"></div>
                    
                    <!-- Conteúdo do Slide -->
                    <div class="container mx-auto px-4 sm:px-6 md:px-8 max-w-6xl relative z-10 space-y-4">
                        <div class="max-w-xl space-y-2 sm:space-y-4 animate-fade-in-up">
                            <h2 v-if="banner.title" class="text-2xl sm:text-4xl md:text-5xl font-black leading-tight tracking-tight drop-shadow-md">
                                {{ banner.title }}
                            </h2>
                            <p v-if="banner.subtitle" class="text-slate-200 text-xs sm:text-base md:text-lg font-light leading-relaxed drop-shadow">
                                {{ banner.subtitle }}
                            </p>
                            <div v-if="banner.link" class="pt-2">
                                <Link :href="banner.link" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-bold px-5 py-2.5 sm:px-7 sm:py-3.5 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                                    <span>Saiba Mais</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Setas de Navegação -->
                <button @click="prevSlide" class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-2.5 z-20 transition-all hover:scale-105" aria-label="Slide anterior">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button @click="nextSlide" class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-2.5 z-20 transition-all hover:scale-105" aria-label="Próximo slide">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                </button>

                <!-- Indicadores de Slides (Dots) -->
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2 z-20">
                    <button 
                        v-for="(banner, idx) in banners" 
                        :key="idx" 
                        @click="setSlide(idx)"
                        class="w-2.5 h-2.5 rounded-full transition-all duration-300"
                        :class="activeSlide === idx ? 'bg-white scale-125 px-2' : 'bg-white/40 hover:bg-white/70'"
                        :aria-label="`Ir para slide ${idx + 1}`"
                    ></button>
                </div>
            </div>

            <!-- Fallback Slide (Caso não haja banners cadastrados) -->
            <div v-else class="relative w-full h-full flex items-center">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-900/40 to-transparent"></div>
                <div class="container mx-auto px-4 max-w-6xl relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center h-full py-8">
                    <!-- Textos -->
                    <div class="space-y-4 max-w-xl">
                        <span class="inline-block bg-blue-500 bg-opacity-30 text-blue-200 border border-blue-400 border-opacity-40 rounded-full px-3 py-1 text-[10px] sm:text-xs font-bold uppercase tracking-wider">Canecas & Copos que Celebram Você</span>
                        <h2 class="text-2xl sm:text-4xl md:text-5xl font-black leading-tight tracking-tight">
                            Brindes Personalizados que Elevam sua Marca
                        </h2>
                        <p class="text-blue-100 text-xs sm:text-base font-light leading-relaxed">
                            Criamos brindes corporativos premium com a identidade visual da sua marca. Acabamentos finos, durabilidade extrema e o melhor design.
                        </p>
                        <div class="flex items-center gap-3 pt-2">
                            <a href="#produtos" class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm px-6 py-2.5 rounded-xl shadow transition-transform hover:-translate-y-0.5">
                                Personalizar Agora
                            </a>
                        </div>
                    </div>
                    <!-- Imagem Showcase -->
                    <div class="hidden lg:flex justify-end relative h-full items-center">
                        <img src="/images/hero_showcase.png" alt="Showcase de Mugs Personalizados" class="max-h-[300px] object-contain rounded-2xl shadow-2xl transform hover:scale-102 transition-transform duration-500">
                    </div>
                </div>
            </div>
        </section>

        <!-- SEÇÃO DE VANTAGENS (DO OIAPOQUE AO CHUÍ, ETC.) -->
        <div class="bg-white border-b border-slate-100 py-6">
            <div class="container mx-auto px-4 max-w-6xl">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-y-6 gap-x-4 items-center justify-items-center">
                    <!-- Vantagem 1 -->
                    <div class="flex items-center gap-3 w-full max-w-[240px] md:justify-center">
                        <div class="text-slate-800 text-2xl flex-shrink-0">
                            <i :class="settings.advantage_1_icon || 'fa-solid fa-truck'"></i>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-[#FF3366] text-xs sm:text-sm font-black whitespace-nowrap overflow-hidden text-ellipsis">{{ settings.advantage_1_title || 'Do Oiapoque ao Chuí' }}</span>
                            <span class="text-slate-500 text-xxs sm:text-xs font-light whitespace-nowrap overflow-hidden text-ellipsis">{{ settings.advantage_1_subtitle || 'Entregas em Todo Brasil' }}</span>
                        </div>
                    </div>
                    
                    <!-- Vantagem 2 -->
                    <div class="flex items-center gap-3 w-full max-w-[240px] md:justify-center md:border-l border-slate-100">
                        <div class="text-slate-800 text-2xl flex-shrink-0">
                            <i :class="settings.advantage_2_icon || 'fa-solid fa-credit-card'"></i>
                        </div>
                        <div class="flex flex-col min-w-0 md:pl-4">
                            <span class="text-[#FF3366] text-xs sm:text-sm font-black whitespace-nowrap overflow-hidden text-ellipsis">{{ settings.advantage_2_title || 'Parcelamento' }}</span>
                            <span class="text-slate-500 text-xxs sm:text-xs font-light whitespace-nowrap overflow-hidden text-ellipsis">{{ settings.advantage_2_subtitle || 'Em até 3x sem juros' }}</span>
                        </div>
                    </div>

                    <!-- Vantagem 3 -->
                    <div class="flex items-center gap-3 w-full max-w-[240px] md:justify-center md:border-l border-slate-100">
                        <div class="text-slate-800 text-2xl flex-shrink-0">
                            <i :class="settings.advantage_3_icon || 'fa-solid fa-gem'"></i>
                        </div>
                        <div class="flex flex-col min-w-0 md:pl-4">
                            <span class="text-[#FF3366] text-xs sm:text-sm font-black whitespace-nowrap overflow-hidden text-ellipsis">{{ settings.advantage_3_title || 'Ganhe Desconto' }}</span>
                            <span class="text-slate-500 text-xxs sm:text-xs font-light whitespace-nowrap overflow-hidden text-ellipsis">{{ settings.advantage_3_subtitle || 'Pagando com PIX' }}</span>
                        </div>
                    </div>

                    <!-- Vantagem 4 -->
                    <div class="flex items-center gap-3 w-full max-w-[240px] md:justify-center md:border-l border-slate-100">
                        <div class="text-slate-800 text-2xl flex-shrink-0">
                            <i :class="settings.advantage_4_icon || 'fa-solid fa-shield-halved'"></i>
                        </div>
                        <div class="flex flex-col min-w-0 md:pl-4">
                            <span class="text-[#FF3366] text-xs sm:text-sm font-black whitespace-nowrap overflow-hidden text-ellipsis">{{ settings.advantage_4_title || 'Segurança' }}</span>
                            <span class="text-slate-500 text-xxs sm:text-xs font-light whitespace-nowrap overflow-hidden text-ellipsis">{{ settings.advantage_4_subtitle || 'Loja com SSL de proteção' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEÇÃO DE CHAMADAS PROMOCIONAIS (2 BANNERS SEGUIDOS) -->
        <div v-if="settings.promo_banner_1_image_path || settings.promo_banner_2_image_path" class="container mx-auto px-4 max-w-6xl pt-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Banner 1 -->
                <div v-if="settings.promo_banner_1_image_path">
                    <Link 
                        v-if="settings.promo_banner_1_link"
                        :href="settings.promo_banner_1_link"
                        class="block rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 transform hover:scale-[1.01]"
                    >
                        <img :src="`/storage/${settings.promo_banner_1_image_path}`" alt="Banner Promocional Esquerda" class="w-full h-auto object-cover max-h-56" />
                    </Link>
                    <div 
                        v-else
                        class="block rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 transform hover:scale-[1.01]"
                    >
                        <img :src="`/storage/${settings.promo_banner_1_image_path}`" alt="Banner Promocional Esquerda" class="w-full h-auto object-cover max-h-56" />
                    </div>
                </div>
                <!-- Banner 2 -->
                <div v-if="settings.promo_banner_2_image_path">
                    <Link 
                        v-if="settings.promo_banner_2_link"
                        :href="settings.promo_banner_2_link"
                        class="block rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 transform hover:scale-[1.01]"
                    >
                        <img :src="`/storage/${settings.promo_banner_2_image_path}`" alt="Banner Promocional Direita" class="w-full h-auto object-cover max-h-56" />
                    </Link>
                    <div 
                        v-else
                        class="block rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 transform hover:scale-[1.01]"
                    >
                        <img :src="`/storage/${settings.promo_banner_2_image_path}`" alt="Banner Promocional Direita" class="w-full h-auto object-cover max-h-56" />
                    </div>
                </div>
            </div>
        </div>

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
                        <img :src="getCategoryImage(cat, index)" :alt="cat.name" class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
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
                        <!-- Badge de Destaque / Promoção / Estoque -->
                        <span v-if="product.track_stock && product.stock_quantity <= 0" class="absolute top-4 left-4 bg-rose-700 text-white font-bold text-xxs px-2.5 py-1 rounded-full uppercase tracking-wider z-10 shadow">Esgotado</span>
                        <span v-else-if="product.promotional_price" class="absolute top-4 left-4 bg-rose-600 text-white font-bold text-xxs px-2.5 py-1 rounded-full uppercase tracking-wider z-10 shadow">Oferta</span>
                        <span v-else class="absolute top-4 left-4 bg-slate-900 text-slate-100 font-bold text-xxs px-2.5 py-1 rounded-full uppercase tracking-wider z-10 shadow">Destaque</span>

                        <!-- Botão Favoritar (Coração Local) -->
                        <button @click="toggleWishlist(product.id, $event)" class="absolute top-4 right-4 p-2 bg-white/95 backdrop-blur hover:bg-slate-50 text-slate-400 hover:text-rose-500 rounded-full z-10 transition-colors shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" :fill="wishlist.includes(product.id) ? 'currentColor' : 'none'" :class="{'text-rose-500': wishlist.includes(product.id)}" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>

                        <!-- Foto do Produto com Hover Zoom -->
                        <div class="h-60 w-full overflow-hidden bg-slate-100 relative">
                            <img :src="product.main_image && product.main_image.path ? `/storage/${product.main_image.path}` : 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?auto=format&fit=crop&w=600&q=80'" :alt="product.name" class="w-full h-full object-cover transform group-hover:scale-104 transition-transform duration-500">
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
                                <button 
                                    @click="addToCart(product, $event)" 
                                    :disabled="product.track_stock && product.stock_quantity <= 0"
                                    class="w-full font-bold py-2.5 px-4 rounded-xl transition-all duration-200 text-xs flex items-center justify-center gap-1.5 shadow-sm"
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
                                    class="w-full border border-[#25D366] text-[#25D366] hover:bg-[#25D366] hover:text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 text-xs flex items-center justify-center gap-1.5"
                                >
                                    <span>Dúvidas pelo whatsapp</span>
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </a>
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
                        <i class="fa-brands fa-whatsapp text-lg"></i>
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

        <!-- Botão WhatsApp Flutuante -->
        <FloatingWhatsapp :settings="settings" />

        <!-- POPUP PROMOCIONAL DINÂMICO -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showPromoPopup && activePopup" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <!-- Backdrop Clique para fechar -->
                <div class="absolute inset-0" @click="closePromoPopup"></div>

                <!-- Card de Conteúdo -->
                <div class="relative bg-white rounded-3xl shadow-2xl overflow-hidden max-w-2xl w-full border border-slate-100 flex flex-col md:flex-row transform transition-all duration-300 z-10">
                    <!-- Botão Fechar -->
                    <button 
                        @click="closePromoPopup" 
                        class="absolute top-4 right-4 bg-white/80 hover:bg-slate-100 text-slate-500 hover:text-slate-800 rounded-full p-2 z-20 shadow transition-colors"
                        aria-label="Fechar popup"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>

                    <!-- Foto Lateral do Popup -->
                    <div v-if="activePopup.image_path" class="w-full md:w-1/2 min-h-[220px] md:min-h-[350px] relative bg-slate-100">
                        <img :src="`/storage/${activePopup.image_path}`" :alt="activePopup.title" class="absolute inset-0 w-full h-full object-cover" />
                    </div>

                    <!-- Conteúdo do Popup -->
                    <div class="p-6 md:p-8 flex-grow flex flex-col justify-center space-y-5" :class="activePopup.image_path ? 'w-full md:w-1/2' : 'w-full'">
                        <div class="space-y-2">
                            <span class="text-xxs font-black tracking-widest text-rose-600 uppercase">Oferta Especial</span>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight">{{ activePopup.title }}</h3>
                            <p v-if="activePopup.description" class="text-xs text-slate-500 leading-relaxed">{{ activePopup.description }}</p>
                        </div>

                        <!-- Contador de Tempo (Opcional) -->
                        <div v-if="activePopup.has_countdown" class="bg-amber-50/70 border border-amber-200/50 rounded-2xl p-4 space-y-2">
                            <span class="block text-center text-xxs font-bold text-amber-800 uppercase tracking-wider">A promoção termina em:</span>
                            <div class="grid grid-cols-4 gap-2 text-center">
                                <div class="bg-white rounded-xl p-2 shadow-xs">
                                    <span class="block text-base sm:text-lg font-black text-amber-900">{{ countdown.days }}</span>
                                    <span class="block text-[9px] text-amber-700 uppercase font-semibold">Dias</span>
                                </div>
                                <div class="bg-white rounded-xl p-2 shadow-xs">
                                    <span class="block text-base sm:text-lg font-black text-amber-900">{{ countdown.hours }}</span>
                                    <span class="block text-[9px] text-amber-700 uppercase font-semibold">Horas</span>
                                </div>
                                <div class="bg-white rounded-xl p-2 shadow-xs">
                                    <span class="block text-base sm:text-lg font-black text-amber-900">{{ countdown.minutes }}</span>
                                    <span class="block text-[9px] text-amber-700 uppercase font-semibold">Min</span>
                                </div>
                                <div class="bg-white rounded-xl p-2 shadow-xs">
                                    <span class="block text-base sm:text-lg font-black text-amber-900">{{ countdown.seconds }}</span>
                                    <span class="block text-[9px] text-amber-700 uppercase font-semibold">Seg</span>
                                </div>
                            </div>
                        </div>

                        <!-- Botão CTA -->
                        <div v-if="activePopup.button_text && activePopup.button_link">
                            <a 
                                :href="activePopup.button_link" 
                                class="block w-full text-center bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs py-3 px-6 rounded-2xl shadow-md hover:shadow-lg transition-all"
                            >
                                {{ activePopup.button_text }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
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