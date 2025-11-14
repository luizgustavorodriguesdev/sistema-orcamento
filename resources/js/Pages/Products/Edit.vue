<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Editor from '@/Components/Editor.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    product: Object,
    categories: Array,
});

// O Inertia tem uma forma especial de lidar com formulários que contêm ficheiros.
// Em vez de 'put', usamos 'post' e adicionamos um campo '_method' para simular um PUT.
const form = useForm({
    _method: 'PUT', // Truque para enviar ficheiros com o método PUT
    name: props.product.name,
    description: props.product.description,
    price: props.product.price,
    promotional_price: props.product.promotional_price,
    category_id: props.product.category_id,
    main_image: null,
    gallery_images: [],
     price_tiers: props.product.price_tiers || [],
});

// --- Lógica para as Escalas de Preços ---
const addPriceTier = () => {
    form.price_tiers.push({
        min_quantity: '',
        price: '',
    });
};

const removePriceTier = (index) => {
    form.price_tiers.splice(index, 1);
};

const mainImagePreview = ref(null);

function updateMainImagePreview(event) {
    const file = event.target.files[0];
    if (file) {
        mainImagePreview.value = URL.createObjectURL(file);
    } else {
        mainImagePreview.value = null;
    }
}

// A submissão agora usa 'post' por causa dos ficheiros.
const submit = () => {
    form.post(route('products.update', props.product.id), {
        forceFormData: true, // Garante que os dados são enviados como FormData
    });
};

// Separa as imagens do produto em principal e galeria para facilitar a exibição.
const mainImage = props.product.images.find(img => img.is_main);
const galleryImages = props.product.images.filter(img => !img.is_main);
</script>

<template>
    <Head :title="'Editar Produto: ' + product.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Produto: {{ product.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <form @submit.prevent="submit">
                            <!-- Campo Nome do Produto -->
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Nome</label>
                                <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.name" required autofocus />
                                <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
                            </div>

                            <!-- Campo Categoria -->
                            <div class="mt-4">
                                <label for="category_id" class="block font-medium text-sm text-gray-700">Categoria</label>
                                <select id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.category_id">
                                    <option :value="null">-- Nenhuma --</option>
                                    <!-- O loop 'v-for' agora deve funcionar, pois a prop 'categories' está a ser recebida corretamente. -->
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.category_id" class="text-sm text-red-600 mt-2">{{ form.errors.category_id }}</p>
                            </div>

                            <!-- Campo Descrição -->
                            <div class="mt-4">
                                <label for="description" class="block font-medium text-sm text-gray-700">Descrição</label>
                                <Editor id="description" v-model="form.description" />
                                <p v-if="form.errors.description" class="text-sm text-red-600 mt-2">{{ form.errors.description }}</p>
                            </div>

                            <!-- Campos de Preço + Promoção -->
                           <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="price" class="block font-medium text-sm text-gray-700">Preço Normal (R$)</label>
                                    <input id="price" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.price" required />
                                    <p v-if="form.errors.price" class="text-sm text-red-600 mt-2">{{ form.errors.price }}</p>
                                </div>
                                <div>
                                    <label for="promotional_price" class="block font-medium text-sm text-gray-700">Preço Promocional (Opcional)</label>
                                    <input id="promotional_price" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.promotional_price" />
                                    <p v-if="form.errors.promotional_price" class="text-sm text-red-600 mt-2">{{ form.errors.promotional_price }}</p>
                                </div>
                            </div>

                            <!-- Secção de Escalas de Preços -->
                            <div class="mt-6 pt-6 border-t">
                                <h3 class="text-lg font-medium text-gray-900">Preços por Quantidade</h3>
                                <div v-for="(tier, index) in form.price_tiers" :key="index" class="mt-4 grid grid-cols-3 gap-4 items-center">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Qtd. Mínima</label>
                                        <input type="number" v-model="tier.min_quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Preço por Unidade (R$)</label>
                                        <input type="number" step="0.01" v-model="tier.price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                                    </div>
                                    <div class="pt-6">
                                        <button @click="removePriceTier(index)" type="button" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                            Remover
                                        </button>
                                    </div>
                                </div>
                                <button @click="addPriceTier" type="button" class="mt-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                                    Adicionar Escala de Preço
                                </button>
                            </div>

                            <!-- Secção de Imagens -->
                            <div class="mt-6 pt-6 border-t">
                                <h3 class="text-lg font-medium text-gray-900">Imagens do Produto</h3>
                                
                                <!-- Imagem Principal Existente -->
                                <div class="mt-4">
                                    <label class="block font-medium text-sm text-gray-700">Imagem Principal Atual</label>
                                    <div v-if="mainImage" class="mt-2 relative w-48">
                                        <img :src="`/storage/${mainImage.path}`" class="w-48 h-48 object-cover rounded-md" />
                                        <Link :href="route('products.images.destroy', mainImage.id)" method="delete" as="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs">X</Link>
                                    </div>
                                    <p v-else class="text-sm text-gray-500">Nenhuma imagem principal definida.</p>
                                </div>

                                <!-- Galeria Existente -->
                                <div class="mt-4">
                                    <label class="block font-medium text-sm text-gray-700">Galeria Atual</label>
                                    <div v-if="galleryImages.length > 0" class="mt-2 flex flex-wrap gap-4">
                                        <div v-for="image in galleryImages" :key="image.id" class="relative w-32">
                                            <img :src="`/storage/${image.path}`" class="w-32 h-32 object-cover rounded-md" />
                                            <Link :href="route('products.images.destroy', image.id)" method="delete" as="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs">X</Link>
                                        </div>
                                    </div>
                                    <p v-else class="text-sm text-gray-500">Nenhuma imagem na galeria.</p>
                                </div>

                                <!-- Upload de Novas Imagens -->
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="main_image" class="block font-medium text-sm text-gray-700">Substituir Imagem Principal</label>
                                        <input id="main_image" type="file" class="mt-1 block w-full" @input="form.main_image = $event.target.files[0]" @change="updateMainImagePreview" accept="image/*" />
                                        <div v-if="mainImagePreview" class="mt-4">
                                            <p class="text-sm font-medium">Pré-visualização:</p>
                                            <img :src="mainImagePreview" class="w-48 h-48 object-cover rounded-md" />
                                        </div>
                                        <p v-if="form.errors.main_image" class="text-sm text-red-600 mt-2">{{ form.errors.main_image }}</p>
                                    </div>
                                    <div>
                                        <label for="gallery_images" class="block font-medium text-sm text-gray-700">Adicionar à Galeria</label>
                                        <input id="gallery_images" type="file" class="mt-1 block w-full" @input="form.gallery_images = $event.target.files" multiple accept="image/*" />
                                        <p v-if="form.errors.gallery_images" class="text-sm text-red-600 mt-2">{{ form.errors.gallery_images }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botão de Submissão -->
                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ form.processing ? 'A Atualizar...' : 'Atualizar Produto' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
