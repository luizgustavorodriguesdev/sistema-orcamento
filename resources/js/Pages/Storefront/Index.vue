<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

// Define as props que o componente recebe do controller
const props = defineProps({
    products: Object,
    categories: Array,
    settings: Object,
});

// --- LÓGICA DO CARRINHO ---
const cart = ref(JSON.parse(localStorage.getItem('cart') || '[]'));

// Função para adicionar um produto ao carrinho
const addToCart = (product, event) => {
    // Impede que o clique no botão ative o link do cartão (navegação)
    event.stopPropagation();
    event.preventDefault();

    const existingProduct = cart.value.find(item => item.id === product.id);

    if (!existingProduct) {
        cart.value.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.main_image && product.main_image.path ? `/storage/${product.main_image.path}` : 'https://placehold.co/600x400/E2E8F0/A0AEC0?text=Brinde',
            price_tiers: product.price_tiers,
        });
        localStorage.setItem('cart', JSON.stringify(cart.value));
        // Chama a notificação
        showToastNotification(`"${product.name}" foi adicionado ao orçamento!`);
    } else {
        // Chama a notificação
        showToastNotification(`"${product.name}" já está no seu orçamento.`);
    }
};

// --- LÓGICA DA NOTIFICAÇÃO (TOAST) ---
const toast = ref({
    show: false,
    message: '',
});

// Função para mostrar a notificação
const showToastNotification = (message) => {
    toast.value.message = message;
    toast.value.show = true;
    setTimeout(() => {
        toast.value.show = false;
    }, 3000); // A notificação desaparece após 3 segundos
};
</script>

<template>
    <Head title="Vitrine de Produtos" />

    <!-- Elemento da Notificação (Toast) -->
    <div v-if="toast.show" class="fixed top-5 right-5 bg-green-500 text-white py-2 px-4 rounded-lg shadow-lg z-50">
        {{ toast.message }}
    </div>

    <div class="bg-gray-100">
        <!-- CABEÇALHO -->
         <header class="bg-white shadow-md">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <Link :href="route('storefront.index')" class="text-2xl font-bold text-gray-800">
                    {{ settings.company_name || 'OrçaBrindes' }}
                </Link>
                <nav class="hidden md:flex space-x-6">
                    <Link v-for="category in categories" :key="category.id" href="#" class="text-gray-600 hover:text-blue-600">{{ category.name }}</Link>
                </nav>
                 <Link :href="route('storefront.cart')" class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 hover:text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span v-if="cart.length > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">{{ cart.length }}</span>
                </Link>
            </div>
        </header>

        <!-- BANNER -->
        <section class="bg-blue-600 text-white">
            <div class="container mx-auto px-4 py-16 text-center">
                <h1 class="text-4xl font-extrabold mb-4">Brindes Personalizados para a sua Marca</h1>
                <p class="text-lg">Encontre o produto ideal e monte o seu orçamento online de forma rápida e fácil.</p>
            </div>
        </section>

        <!-- GRELHA DE PRODUTOS -->
        <main class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <template v-for="product in products.data" :key="product.id">
                    <!-- [CORREÇÃO] Voltamos a usar o <Link> para que o cartão seja clicável -->
                    <Link
                        v-if="product && product.slug"
                        :href="route('storefront.product.show', { product: product.slug })"
                        class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:-translate-y-1 transition-transform duration-300"
                    >
                        <img :src="product.main_image && product.main_image.path ? `/storage/${product.main_image.path}` : 'https://placehold.co/600x400/E2E8F0/A0AEC0?text=Brinde'" alt="Imagem do Produto" class="w-full h-56 object-cover">
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-2">{{ product.name }}</h2>
                            <div class="flex justify-between items-center mb-4">
                                <p v-if="product.promotional_price" class="text-lg font-semibold text-red-600">
                                    R$ {{ parseFloat(product.promotional_price).toFixed(2) }}
                                    <span class="text-sm text-gray-500 line-through ml-2">R$ {{ parseFloat(product.price).toFixed(2) }}</span>
                                </p>
                                <p v-else class="text-lg font-semibold text-gray-800">
                                    R$ {{ parseFloat(product.price).toFixed(2) }}
                                </p>
                            </div>
                            <!-- O @click passa o $event para a função, permitindo o stopPropagation -->
                            <button @click="addToCart(product, $event)" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                                Adicionar ao Orçamento
                            </button>
                        </div>
                    </Link>
                </template>
            </div>

            <!-- PAGINAÇÃO -->
             <div v-if="products.links.length > 3" class="mt-8 flex justify-center space-x-1">
                <Link
                    v-for="(link, index) in products.links"
                    :key="index"
                    :href="link.url"
                    v-html="link.label"
                    class="px-4 py-2 rounded-md text-sm font-medium"
                    :class="{
                        'bg-blue-600 text-white': link.active,
                        'bg-white text-gray-700 hover:bg-gray-200': !link.active && link.url,
                        'bg-gray-100 text-gray-400 cursor-not-allowed': !link.url
                    }"
                />
            </div>
        </main>

         <!-- RODAPÉ -->
        <footer class="bg-gray-800 text-white mt-12">
            <!-- ... (código do rodapé) ... -->
        </footer>
    </div>
</template>