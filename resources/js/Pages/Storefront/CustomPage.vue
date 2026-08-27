<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FloatingWhatsapp from '@/Components/FloatingWhatsapp.vue';

const props = defineProps({
    page: Object,
    categories: Array,
    settings: Object,
    menuItems: Array,
    highlightedMenuItems: Array,
});

// --- LÓGICA DO CARRINHO (Apenas para exibir a bolinha no header) ---
const cart = ref(JSON.parse(localStorage.getItem('cart') || '[]'));

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
        <title>{{ page.meta_title || `${page.title} - ${settings.company_name || 'GiftJoy'}` }}</title>
        <meta name="description" :content="page.meta_description || settings.seo_meta_description || 'Confira nossa página institucional.'" />
        <meta name="keywords" :content="page.meta_keywords || settings.seo_meta_keywords || 'brindes, personalizados'" />

        <!-- URL Canônica -->
        <link rel="canonical" :href="$page.props.current_url" />

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="article" />
        <meta property="og:url" :content="$page.props.current_url" />
        <meta property="og:title" :content="page.meta_title || `${page.title} - ${settings.company_name || 'GiftJoy'}`" />
        <meta property="og:description" :content="page.meta_description || settings.seo_meta_description || 'Confira nossa página institucional.'" />
        <meta property="og:image" content="/images/hero_showcase.png" />

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:url" :content="$page.props.current_url" />
        <meta property="twitter:title" :content="page.meta_title || `${page.title} - ${settings.company_name || 'GiftJoy'}`" />
        <meta property="twitter:description" :content="page.meta_description || settings.seo_meta_description || 'Confira nossa página institucional.'" />
        <meta property="twitter:image" content="/images/hero_showcase.png" />
    </Head>

    <div class="bg-slate-50 min-h-screen font-sans antialiased text-slate-800 flex flex-col justify-between">
        <div>
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
                            <span v-if="cart.length > 0" class="absolute -top-1 -right-1 bg-blue-600 text-white text-xxs font-black rounded-full h-5 w-5 flex items-center justify-center border-2 border-white shadow-md">{{ cart.length }}</span>
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
                <div v-if="highlightedMenuItems && highlightedMenuItems.length > 0" class="border-t border-slate-100 py-2.5 bg-slate-50/50 overflow-x-auto md:overflow-visible scrollbar-none relative">
                    <div class="container mx-auto px-4 max-w-6xl flex justify-start md:justify-center items-center gap-6 whitespace-nowrap md:relative">
                        
                        <div 
                            v-for="(item, idx) in highlightedMenuItems" 
                            :key="idx" 
                            class="group"
                        >
                            <Link 
                                :href="item.url" 
                                class="text-xs font-bold text-slate-700 hover:text-blue-600 transition-all tracking-wide uppercase px-2.5 py-1 rounded-lg block"
                                :class="item.type === 'product' ? 'bg-blue-600 text-white shadow-sm hover:text-white hover:bg-blue-700' : 'hover:bg-slate-200/50'"
                            >
                                {{ item.label }}
                            </Link>

                            <!-- MEGA MENU CONTAINER -->
                            <div 
                                v-if="item.type === 'category' && ((item.subcategories && item.subcategories.length > 0) || item.mega_menu_banner)"
                                class="absolute left-4 right-4 top-full z-50 bg-white border border-slate-100 shadow-2xl rounded-b-2xl p-6 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 max-w-6xl mx-auto md:left-4 md:right-4 whitespace-normal"
                            >
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-left">
                                    <!-- Colunas de Subcategorias -->
                                    <div class="col-span-1 md:col-span-3">
                                        <h4 class="text-xs font-black text-slate-400 tracking-wider uppercase mb-4 border-b border-slate-50 pb-2">
                                            {{ item.label }}
                                        </h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-2.5">
                                            <Link 
                                                v-for="(sub, subIdx) in item.subcategories" 
                                                :key="subIdx" 
                                                :href="sub.url"
                                                class="text-sm font-semibold text-slate-600 hover:text-blue-600 hover:translate-x-1 transition-all block py-0.5"
                                            >
                                                {{ sub.label }}
                                            </Link>
                                            <Link 
                                                :href="item.url"
                                                class="text-sm font-bold text-blue-600 hover:underline block py-0.5"
                                            >
                                                Ver Tudo em {{ item.label }} &rarr;
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Banner Promocional -->
                                    <div v-if="item.mega_menu_banner" class="col-span-1 md:border-l md:border-slate-100 md:pl-8 flex flex-col justify-center">
                                        <div class="overflow-hidden rounded-xl shadow-md group/banner relative aspect-[4/3] bg-slate-50">
                                            <img 
                                                :src="`/storage/${item.mega_menu_banner}`" 
                                                alt="Banner Promocional" 
                                                class="w-full h-full object-cover transition-transform duration-500 group-hover/banner:scale-105"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </header>

            <!-- CONTEÚDO PRINCIPAL -->
            <main class="container mx-auto px-4 py-12 max-w-6xl">
                <!-- Estilos Customizados -->
                <component is="style" v-if="page.custom_css" v-html="page.custom_css"></component>

                <!-- BREADCRUMB -->
                <nav class="flex text-xs font-semibold text-slate-400 mb-8 items-center gap-2">
                    <Link :href="route('storefront.index')" class="hover:text-blue-600 transition-colors">Início</Link>
                    <span>/</span>
                    <span class="text-slate-800 font-bold">{{ page.title }}</span>
                </nav>

                <!-- Card de Conteúdo -->
                <article class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-12 shadow-sm space-y-8">
                    <header class="border-b border-slate-100 pb-6">
                        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">{{ page.title }}</h1>
                    </header>

                    <!-- Conteúdo HTML -->
                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-light" v-html="page.content"></div>
                </article>
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
                        Personalizando momentos e presentes com quality extrema, foco na experiência do cliente e entregas em tempo recorde por todo o território nacional.
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
.prose h1, .prose h2, .prose h3, .prose h4 {
    font-weight: 800;
    color: #1e293b;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}
.prose p {
    margin-bottom: 1rem;
}
.prose ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}
.prose ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}
</style>
