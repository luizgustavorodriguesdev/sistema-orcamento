<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

// Define as props que o componente recebe do controller
const props = defineProps({
    product: Object,
    categories: Array,
    settings: Object,
});

// --- LÓGICA DO CARRINHO ---
const cart = ref(JSON.parse(localStorage.getItem('cart') || '[]'));

const addToCart = (product) => {
    const existingProduct = cart.value.find(item => item.id === product.id);
    if (!existingProduct) {
        cart.value.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.main_image ? `/storage/${product.main_image.path}` : 'https://placehold.co/600x400/E2E8F0/A0AEC0?text=Brinde',
            price_tiers: product.price_tiers,
        });
        localStorage.setItem('cart', JSON.stringify(cart.value));
        showToastNotification(`"${product.name}" foi adicionado ao orçamento!`);
    } else {
        showToastNotification(`"${product.name}" já está no seu orçamento.`);
    }
};

// --- LÓGICA DA GALERIA DE IMAGENS ---
// Verifica se a imagem principal existe antes de criar o caminho
const mainImageSrc = computed(() => {
    return props.product?.main_image?.path
        ? `/storage/${props.product.main_image.path}`
        : 'https://placehold.co/600x400/E2E8F0/A0AEC0?text=Brinde';
});
const mainImage = ref(mainImageSrc.value);

// Filtra para obter apenas as imagens da galeria
const galleryImages = computed(() => props.product?.images?.filter(img => !img.is_main) || []);

const changeMainImage = (imagePath) => {
    mainImage.value = `/storage/${imagePath}`;
};

// --- LÓGICA DA NOTIFICAÇÃO (TOAST) ---
const toast = ref({ show: false, message: '' });

const showToastNotification = (message) => {
    toast.value.message = message;
    toast.value.show = true;
    setTimeout(() => { toast.value.show = false; }, 3000);
};

</script>

<template>
    <Head :title="product.name" />

    <!-- Notificação Toast -->
    <div v-if="toast.show" class="fixed top-5 right-5 bg-green-500 text-white py-2 px-4 rounded-lg shadow-lg z-50">
        {{ toast.message }}
    </div>

    <div class="bg-gray-100 min-h-screen">
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

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="container mx-auto px-4 py-8">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Coluna das Imagens -->
                    <div>
                        <div class="mb-4">
                            <img :src="mainImage" alt="Imagem Principal do Produto" class="w-full h-auto object-cover rounded-lg">
                        </div>
                        <div v-if="galleryImages.length > 0" class="grid grid-cols-4 gap-2">
                            <img
                                v-for="image in galleryImages"
                                :key="image.id"
                                :src="`/storage/${image.path}`"
                                alt="Imagem da Galeria"
                                class="w-full h-24 object-cover rounded-md cursor-pointer hover:opacity-75"
                                @click="changeMainImage(image.path)"
                            >
                        </div>
                    </div>

                    <!-- Coluna de Detalhes -->
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
                        <!-- Verificação para a categoria -->
                        <p v-if="product.category" class="text-gray-500 mb-4">{{ product.category.name }}</p>

                        <!-- Preços -->
                        <div class="mb-6">
                            <p v-if="product.promotional_price" class="text-2xl font-bold text-red-600">
                                R$ {{ parseFloat(product.promotional_price).toFixed(2) }}
                                <span class="text-lg text-gray-500 line-through ml-2">R$ {{ parseFloat(product.price).toFixed(2) }}</span>
                            </p>
                            <p v-else class="text-2xl font-bold text-gray-800">
                                R$ {{ parseFloat(product.price).toFixed(2) }}
                            </p>
                        </div>

                        <!-- Descrição -->
                        <div class="prose mb-6" v-html="product.description"></div>

                        <!-- Botão de Adicionar -->
                        <button @click="addToCart(product)" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                            Adicionar ao Orçamento
                        </button>

                         <!-- Tabela de Preços por Quantidade -->
                        <div v-if="product.price_tiers && product.price_tiers.length > 0" class="mt-8">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">Preços por Quantidade</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Quantidade</th>
                                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Preço por Unidade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="tier in product.price_tiers" :key="tier.id" class="hover:bg-gray-50">
                                            <td class="py-2 px-4 border-b text-gray-700">A partir de {{ tier.min_quantity }} un.</td>
                                            <td class="py-2 px-4 border-b text-gray-700">R$ {{ parseFloat(tier.price).toFixed(2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

         <!-- RODAPÉ -->
        <footer class="bg-gray-800 text-white mt-12">
            <div class="container mx-auto px-4 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-xl font-bold mb-4">{{ settings.company_name || 'OrçaBrindes' }}</h3>
                        <p class="text-gray-400">{{ settings.company_address || 'Endereço não informado' }}</p>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-4">Contacto</h3>
                        <p class="text-gray-400">Email: {{ settings.company_email || 'email@exemplo.com' }}</p>
                        <p class="text-gray-400">Telefone: {{ settings.company_phone || '(00) 0000-0000' }}</p>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-4">Redes Sociais</h3>
                        <div class="flex space-x-4">
                           <a v-if="settings.company_facebook" :href="settings.company_facebook" target="_blank" class="text-gray-400 hover:text-white">Facebook</a>
                           <a v-if="settings.company_instagram" :href="settings.company_instagram" target="_blank" class="text-gray-400 hover:text-white">Instagram</a>
                           <a v-if="settings.company_linkedin" :href="settings.company_linkedin" target="_blank" class="text-gray-400 hover:text-white">LinkedIn</a>
                        </div>
                    </div>
                </div>
                <div class="text-center text-gray-500 mt-8 border-t border-gray-700 pt-4">
                    &copy; {{ new Date().getFullYear() }} {{ settings.company_name || 'OrçaBrindes' }}. Todos os direitos reservados.
                </div>
            </div>
        </footer>
    </div>
</template>
