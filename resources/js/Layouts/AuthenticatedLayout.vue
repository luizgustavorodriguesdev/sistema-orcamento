<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';

const sidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-slate-950 flex text-slate-200 dark-admin-theme">
        <!-- Backdrop Mobile -->
        <div 
            v-if="sidebarOpen" 
            class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-sm md:hidden" 
            @click="sidebarOpen = false"
        ></div>

        <!-- SIDEBAR (Barra Lateral Esquerda) -->
        <aside 
            class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 transform transition-transform duration-300 -translate-x-full md:translate-x-0 flex flex-col justify-between shadow-2xl border-r border-slate-800"
            :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
        >
            <!-- Top Logo & Brand -->
            <div>
                <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800 bg-slate-950">
                    <Link :href="route('dashboard')" class="flex items-center gap-2 font-black text-white text-base tracking-tight hover:opacity-90 transition-opacity">
                        <ApplicationLogo class="block h-7 w-auto fill-current text-blue-500" />
                        <span>OrçaBrindes Admin</span>
                    </Link>
                    <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Navigation Links Vertical -->
                <nav class="p-4 space-y-6 overflow-y-auto sidebar-scroll" style="max-height: calc(100vh - 10rem);">
                    <!-- Grupo GESTÃO -->
                    <div class="space-y-1">
                        <div class="px-3 py-1 text-xxs font-black text-slate-500 uppercase tracking-widest mb-1">
                            Gestão
                        </div>
                        
                        <Link 
                            :href="route('dashboard')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            <span>Dashboard</span>
                        </Link>

                        <Link 
                            :href="route('quotes.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('quotes.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <span>Orçamentos</span>
                        </Link>

                        <Link 
                            :href="route('clients.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('clients.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            <span>Clientes</span>
                        </Link>

                        <Link 
                            :href="route('products.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('products.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            <span>Produtos</span>
                        </Link>

                        <Link 
                            :href="route('categories.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('categories.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Categorias</span>
                        </Link>

                        <Link 
                            :href="route('media.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('media.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>Mídias</span>
                        </Link>

                        <Link 
                            :href="route('pages.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('pages.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            <span>Páginas</span>
                        </Link>
                    </div>

                    <!-- Grupo FILTROS -->
                    <div class="space-y-1 pt-4 border-t border-slate-800/80">
                        <div class="px-3 py-1 text-xxs font-black text-slate-500 uppercase tracking-widest mb-1">
                            Atributos e Filtros
                        </div>

                        <Link 
                            :href="route('themes.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('themes.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                            <span>Temas</span>
                        </Link>

                        <Link 
                            :href="route('personalization-types.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('personalization-types.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            <span>Personalizações</span>
                        </Link>

                        <Link 
                            :href="route('colors.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('colors.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                            <span>Cores</span>
                        </Link>

                        <Link 
                            :href="route('characteristics.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('characteristics.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>
                            <span>Características</span>
                        </Link>
                    </div>

                    <!-- Grupo SISTEMA -->
                    <div class="space-y-1 pt-4 border-t border-slate-800/80">
                        <div class="px-3 py-1 text-xxs font-black text-slate-500 uppercase tracking-widest mb-1">
                            Sistema
                        </div>

                        <Link 
                            :href="route('users.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('users.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            <span>Utilizadores</span>
                        </Link>

                        <Link 
                            :href="route('payment-methods.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('payment-methods.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            <span>Pagamentos</span>
                        </Link>

                        <Link 
                            :href="route('menu-items.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('menu-items.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            <span>Menus</span>
                        </Link>

                        <Link 
                            :href="route('settings.index')" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                            :class="route().current('settings.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4-5 w-4-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span>Configurações</span>
                        </Link>
                    </div>
                </nav>
            </div>

            <!-- Sidebar User Profile Footer -->
            <div class="p-4 border-t border-slate-800 bg-slate-950 flex items-center justify-between">
                <div class="flex items-center gap-2.5 truncate">
                    <div class="h-9-5 w-9-5 rounded-xl bg-blue-600 text-white font-extrabold flex items-center justify-center text-sm shadow-inner shadow-blue-500">
                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="truncate text-xxs leading-tight">
                        <p class="font-bold text-white truncate">{{ $page.props.auth.user.name }}</p>
                        <p class="text-slate-500 truncate mt-0.5">{{ $page.props.auth.user.email }}</p>
                    </div>
                </div>

                <!-- Settings/Logout Dropdown -->
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button class="text-slate-400 hover:text-white p-1.5 hover:bg-slate-800 rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                        <a :href="route('storefront.index')" target="_blank" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">Ver Vitrine</a>
                        <DropdownLink :href="route('logout')" method="post" as="button" class="w-full text-left">Sair</DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <!-- MAIN AREA (Conteúdo da Direita) -->
        <div class="flex-grow md:pl-64 flex flex-col min-h-screen">
            <!-- Mobile Header Top Bar -->
            <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between px-6 md:px-8 sticky top-0 z-30 shadow-sm">
                <!-- Left: Hamburger toggle (mobile) & Title (desktop) -->
                <div class="flex items-center gap-4">
                    <button 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="md:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-gray-500 dark:text-gray-400"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    
                    <!-- Slot Header Title -->
                    <div v-if="$slots.header" class="text-sm font-black text-gray-800 dark:text-gray-200">
                        <slot name="header" />
                    </div>
                </div>

                <!-- Right: Header actions -->
                <div class="flex items-center gap-4">
                    <a 
                        :href="route('storefront.index')" 
                        target="_blank" 
                        class="bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 font-bold text-xxs px-3 py-1.5 rounded-xl shadow-sm transition-all flex items-center gap-1"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <span>Ver Loja</span>
                    </a>
                </div>
            </header>

            <!-- Page Content Slot -->
            <main class="flex-grow p-6 md:p-8">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="py-6 text-center text-xxs text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800">
                <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p>&copy; {{ new Date().getFullYear() }} OrçaBrindes. Todos os direitos reservados.</p>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400">Versão do Sistema:</span>
                        <span v-if="$page.props.appVersion" class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono transition-colors duration-200">
                            v{{ $page.props.appVersion }}
                        </span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.text-xxs {
    font-size: 0.65rem;
}
.h-4-5 {
    height: 1.125rem;
}
.w-4-5 {
    width: 1.125rem;
}
.h-9-5 {
    height: 2.375rem;
}
.w-9-5 {
    width: 2.375rem;
}
</style>

<style>
/* ==========================================================================
   TEMA ESCURO GLOBAL - PAINEL ADMINISTRATIVO
   ========================================================================== */

.dark-admin-theme {
    background-color: #0f172a !important; /* Slate 900 */
    color: #e2e8f0 !important; /* Slate 200 */
}

/* Ocultar scrollbar grosseira e adicionar customizada para o menu lateral */
.sidebar-scroll {
    scrollbar-width: thin;
    scrollbar-color: #475569 transparent;
}
.sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}
.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.sidebar-scroll::-webkit-scrollbar-thumb {
    background: #475569; /* Slate 600 */
    border-radius: 999px;
}
.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: #3b82f6; /* Blue 500 */
}

/* Forçar fundos de cartões, containers e tabelas a ficarem escuros */
.dark-admin-theme .bg-white {
    background-color: #1e293b !important; /* Slate 800 */
    color: #f8fafc !important;
}

/* Sobrescrever tons de textos */
.dark-admin-theme .text-gray-900,
.dark-admin-theme .text-gray-800,
.dark-admin-theme .text-slate-800 {
    color: #f8fafc !important; /* Slate 50 */
}

.dark-admin-theme .text-gray-700,
.dark-admin-theme .text-gray-600,
.dark-admin-theme .text-slate-600 {
    color: #cbd5e1 !important; /* Slate 300 */
}

.dark-admin-theme .text-gray-500,
.dark-admin-theme .text-gray-400,
.dark-admin-theme .text-slate-400 {
    color: #94a3b8 !important; /* Slate 400 */
}

.dark-admin-theme .bg-gray-50,
.dark-admin-theme .bg-slate-50,
.dark-admin-theme .bg-slate-50\/50,
.dark-admin-theme .bg-slate-50\/80 {
    background-color: #0f172a !important; /* Slate 900 */
    color: #cbd5e1 !important;
}

/* Sobrescrever bordas */
.dark-admin-theme .border-b,
.dark-admin-theme .border,
.dark-admin-theme .border-slate-100,
.dark-admin-theme .border-slate-200,
.dark-admin-theme .border-slate-800,
.dark-admin-theme .border-gray-200,
.dark-admin-theme .border-gray-100 {
    border-color: #334155 !important; /* Slate 700 */
}

/* Hover de linhas nas tabelas */
.dark-admin-theme .hover\:bg-gray-50:hover,
.dark-admin-theme .hover\:bg-slate-50:hover,
.dark-admin-theme .hover\:bg-slate-50\/80:hover {
    background-color: #334155 !important; /* Slate 700 */
}

/* Estilização para Inputs, Selects, File Upload e Textareas nos formulários */
.dark-admin-theme input[type="text"],
.dark-admin-theme input[type="email"],
.dark-admin-theme input[type="number"],
.dark-admin-theme input[type="password"],
.dark-admin-theme input[type="search"],
.dark-admin-theme input[type="file"],
.dark-admin-theme select,
.dark-admin-theme textarea {
    background-color: #0f172a !important; /* Slate 900 */
    border-color: #334155 !important; /* Slate 700 */
    color: #f8fafc !important; /* Slate 50 */
}

.dark-admin-theme input[type="text"]:focus,
.dark-admin-theme input[type="email"]:focus,
.dark-admin-theme input[type="number"]:focus,
.dark-admin-theme input[type="password"]:focus,
.dark-admin-theme select:focus,
.dark-admin-theme textarea:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
}

/* Estilo para Badges / Labels */
.dark-admin-theme .bg-gray-100 {
    background-color: #334155 !important; /* Slate 700 */
    color: #cbd5e1 !important;
}
.dark-admin-theme .bg-yellow-100 {
    background-color: rgba(234, 179, 8, 0.15) !important;
    color: #fef08a !important;
}
.dark-admin-theme .bg-green-100 {
    background-color: rgba(34, 197, 94, 0.15) !important;
    color: #bbf7d0 !important;
}
.dark-admin-theme .bg-blue-100 {
    background-color: rgba(59, 130, 246, 0.15) !important;
    color: #bfdbfe !important;
}

/* Tiptap / Editor Visual Overrides */
.dark-admin-theme .ProseMirror {
    background-color: #0f172a !important;
    color: #f8fafc !important;
    border-color: #334155 !important;
}
.dark-admin-theme .editor-toolbar button {
    color: #cbd5e1 !important;
}
.dark-admin-theme .editor-toolbar button:hover {
    background-color: #334155 !important;
}
</style>
