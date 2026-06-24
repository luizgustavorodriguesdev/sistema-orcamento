<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    page: Object,
    categories: Array,
    settings: Object,
    menuItems: Array,
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

// --- MENU MOBILE ---
const showingMobileMenu = ref(false);
const openDropdown = ref(null);
</script>

<template>
    <Head :title="`${page.title} - ${settings.company_name || 'GiftJoy'}`" />

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
