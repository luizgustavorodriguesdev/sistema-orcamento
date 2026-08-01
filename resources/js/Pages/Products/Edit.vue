<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Editor from '@/Components/Editor.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    product: Object,
    categories: Array,
    themes: Array,
    personalization_types: Array,
    colors: Array,
    characteristics: Array,
});

const getIds = (relation) => {
    return relation ? relation.map(item => item.id) : [];
};

// O Inertia tem uma forma especial de lidar com formulários que contêm ficheiros.
// Em vez de 'put', usamos 'post' e adicionamos um campo '_method' para simular um PUT.
const form = useForm({
    _method: 'PUT', // Truque para enviar ficheiros com o método PUT
    name: props.product.name,
    description: props.product.description,
    price: props.product.price,
    cost_price: props.product.cost_price || 0,
    promotional_price: props.product.promotional_price,
    track_stock: props.product.track_stock ?? false,
    stock_quantity: props.product.stock_quantity ?? 0,
    minimum_stock: props.product.minimum_stock ?? 0,
    category_id: props.product.category_id,
    main_image: null,
    gallery_images: [],
    price_tiers: props.product.price_tiers || [],
    themes: getIds(props.product.themes),
    personalization_types: getIds(props.product.personalization_types || props.product.personalizationTypes),
    colors: getIds(props.product.colors),
    characteristics: getIds(props.product.characteristics),
    meta_title: props.product.meta_title || '',
    meta_description: props.product.meta_description || '',
    meta_keywords: props.product.meta_keywords || '',
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

                                <!-- Campos de Preço + Promoção -->
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="price" class="block font-medium text-sm text-gray-700">Preço Venda (R$)</label>
                                        <input id="price" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.price" required />
                                        <p v-if="form.errors.price" class="text-sm text-red-600 mt-2">{{ form.errors.price }}</p>
                                    </div>
                                    <div>
                                        <label for="cost_price" class="block font-medium text-sm text-gray-700">Preço de Custo (R$)</label>
                                        <input id="cost_price" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.cost_price" required />
                                        <p v-if="form.errors.cost_price" class="text-sm text-red-600 mt-2">{{ form.errors.cost_price }}</p>
                                    </div>
                                    <div>
                                        <label for="promotional_price" class="block font-medium text-sm text-gray-700">Preço Promocional (Opcional)</label>
                                        <input id="promotional_price" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.promotional_price" />
                                        <p v-if="form.errors.promotional_price" class="text-sm text-red-600 mt-2">{{ form.errors.promotional_price }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Controle de Estoque -->
                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <h3 class="text-lg font-medium text-gray-955 mb-3">Estoque do Produto</h3>
                                <div class="space-y-4 bg-slate-50/50 border border-slate-100 rounded-2xl p-5">
                                    <div class="flex items-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" v-model="form.track_stock" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                            <span class="ms-2 text-sm text-slate-700 font-semibold">Ativar controle de estoque para este produto</span>
                                        </label>
                                    </div>
                                    <div v-if="form.track_stock" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="stock_quantity" class="block font-medium text-sm text-slate-700">Quantidade em Estoque</label>
                                            <input id="stock_quantity" type="number" min="0" class="mt-1 block w-full rounded-xl border-slate-300 bg-white shadow-sm text-sm" v-model.number="form.stock_quantity" :required="form.track_stock" />
                                            <p v-if="form.errors.stock_quantity" class="text-sm text-rose-600 mt-2">{{ form.errors.stock_quantity }}</p>
                                        </div>
                                        <div>
                                            <label for="minimum_stock" class="block font-medium text-sm text-slate-700">Estoque Mínimo de Segurança</label>
                                            <input id="minimum_stock" type="number" min="0" class="mt-1 block w-full rounded-xl border-slate-300 bg-white shadow-sm text-sm" v-model.number="form.minimum_stock" :required="form.track_stock" />
                                            <p v-if="form.errors.minimum_stock" class="text-sm text-rose-600 mt-2">{{ form.errors.minimum_stock }}</p>
                                        </div>
                                    </div>
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

                            <!-- Secção de Atributos para Filtros -->
                            <div class="mt-6 pt-6 border-t">
                                <h3 class="text-lg font-medium text-gray-900">Atributos de Filtragem (Selecione as opções)</h3>
                                <p class="text-xs text-gray-500 mb-4">Escolha os termos cadastrados no sistema que serão associados a este produto.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Temas -->
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 mb-2">Temas</label>
                                        <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50 custom-scrollbar">
                                            <label v-for="theme in themes" :key="theme.id" class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-blue-600">
                                                <input type="checkbox" :value="theme.id" v-model="form.themes" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                                <span>{{ theme.name }}</span>
                                            </label>
                                            <p v-if="themes.length === 0" class="text-xs text-gray-400">Nenhum tema cadastrado.</p>
                                        </div>
                                        <p v-if="form.errors.themes" class="text-sm text-red-600 mt-2">{{ form.errors.themes }}</p>
                                    </div>

                                    <!-- Tipo de Personalização -->
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 mb-2">Tipo de Personalização</label>
                                        <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50 custom-scrollbar">
                                            <label v-for="pers in personalization_types" :key="pers.id" class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-blue-600">
                                                <input type="checkbox" :value="pers.id" v-model="form.personalization_types" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                                <span>{{ pers.name }}</span>
                                            </label>
                                            <p v-if="personalization_types.length === 0" class="text-xs text-gray-400">Nenhuma personalização cadastrada.</p>
                                        </div>
                                        <p v-if="form.errors.personalization_types" class="text-sm text-red-600 mt-2">{{ form.errors.personalization_types }}</p>
                                    </div>

                                    <!-- Cores -->
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 mb-2">Cores</label>
                                        <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50 custom-scrollbar">
                                            <label v-for="color in colors" :key="color.id" class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-blue-600">
                                                <input type="checkbox" :value="color.id" v-model="form.colors" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                                <span class="w-4 h-4 rounded-full border border-gray-300 block shadow-sm flex-shrink-0" :style="{ backgroundColor: color.hex_code || '#fff' }"></span>
                                                <span>{{ color.name }}</span>
                                            </label>
                                            <p v-if="colors.length === 0" class="text-xs text-gray-400">Nenhuma cor cadastrada.</p>
                                        </div>
                                        <p v-if="form.errors.colors" class="text-sm text-red-600 mt-2">{{ form.errors.colors }}</p>
                                    </div>

                                    <!-- Características -->
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 mb-2">Características</label>
                                        <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50 custom-scrollbar">
                                            <label v-for="char in characteristics" :key="char.id" class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-blue-600">
                                                <input type="checkbox" :value="char.id" v-model="form.characteristics" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                                <span>{{ char.name }}</span>
                                            </label>
                                            <p v-if="characteristics.length === 0" class="text-xs text-gray-400">Nenhuma característica cadastrada.</p>
                                        </div>
                                        <p v-if="form.errors.characteristics" class="text-sm text-red-600 mt-2">{{ form.errors.characteristics }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Secção de SEO -->
                            <div class="mt-6 pt-6 border-t">
                                <h3 class="text-lg font-medium text-gray-900">Configurações de SEO</h3>
                                <p class="text-xs text-gray-500 mb-4">Insira os metadados para melhorar o ranqueamento deste produto nos motores de busca (Google, Bing, etc.).</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="meta_title" class="block font-medium text-sm text-gray-700">Meta Title (Título da Página)</label>
                                        <input id="meta_title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.meta_title" placeholder="Deixe em branco para usar o nome do produto" />
                                        <p v-if="form.errors.meta_title" class="text-sm text-red-600 mt-2">{{ form.errors.meta_title }}</p>
                                    </div>
                                    <div>
                                        <label for="meta_description" class="block font-medium text-sm text-gray-700">Meta Description (Descrição da Página)</label>
                                        <textarea id="meta_description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.meta_description" rows="3" placeholder="Insira uma descrição resumida de até 160 caracteres"></textarea>
                                        <p v-if="form.errors.meta_description" class="text-sm text-red-600 mt-2">{{ form.errors.meta_description }}</p>
                                    </div>
                                    <div>
                                        <label for="meta_keywords" class="block font-medium text-sm text-gray-700">Meta Keywords (Palavras-chave separadas por vírgula)</label>
                                        <input id="meta_keywords" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.meta_keywords" placeholder="Ex: caneca personalizada, brindes corporativos, caneca de chopp" />
                                        <p v-if="form.errors.meta_keywords" class="text-sm text-red-600 mt-2">{{ form.errors.meta_keywords }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botão de Submissão -->
                            <div class="flex items-center justify-end mt-6">
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
