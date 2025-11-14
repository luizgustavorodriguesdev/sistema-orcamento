<script setup>
// Importações essenciais
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Layout padrão para páginas autenticadas
import { Head, useForm } from '@inertiajs/vue3'; // Head para o título da página e useForm para gerenciar o formulário
import Editor from '@/Components/Editor.vue'; // Importamos o nosso componete de editor
import {ref} from 'vue';

// Recebemos a lista de categorias do nosso controller.
const props = defineProps({
    categories: Array,
});

// O useForm do Inertia cria um objeto reativo que encapsula os dados do formulário.
// Ele também gerencia o estado de envio (processing), erros de validação (errors), e muito mais.
const form = useForm({
    name: '',          // Valor inicial para o nome do produto
    description: '',   // Valor inicial para a descrição
    price: null,       // Valor inicial para o preço
    promotional_price: null, // Valor inicial para o preço promocional
    category_id: null, // Valor inicial para o ID da categoria
    main_image: null,
    gallery_images: [],
    price_tiers:[],
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

// Variável reativa para mostrar a pré-visualização da imagem principal.
const mainImagePreview = ref(null);

// Função para atualizar a pré-visualização quando um ficheiro é selecionado.
function updateMainImagePreview(event) {
    const file = event.target.files[0];
    if (file) {
        mainImagePreview.value = URL.createObjectURL(file);
    } else {
        mainImagePreview.value = null;
    }
}

// Esta função será chamada quando o formulário for submetido.
const submit = () => {
    // O método 'post' do objeto 'form' envia os dados para a rota especificada.
    // O Inertia lida com a requisição AJAX em segundo plano.
    // Em caso de sucesso, o controller redirecionará para a lista de produtos.
    // Em caso de erro de validação, o Inertia preencherá o objeto 'form.errors' automaticamente.
    form.post(route('products.store'), {
        // onFinish é um callback que é executado após a requisição ser completada (com sucesso ou erro).
        // Aqui, estamos limpando o campo de senha, o que é uma boa prática (não aplicável aqui, mas bom saber).
        // onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <!-- Define o título que aparecerá na aba do navegador -->
    <Head title="Adicionar Produto" />

    <!-- Utiliza o layout autenticado, que já contém o menu de navegação e a estrutura da página -->
    <AuthenticatedLayout>
        <!-- Slot para o cabeçalho da página -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Adicionar Novo Produto</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <!-- O formulário chama a função 'submit' ao ser submetido.
                             O modificador '.prevent' impede o comportamento padrão de recarregamento da página. -->
                        <form @submit.prevent="submit">
                            <!-- Campo Nome do Produto -->
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Nome</label>
                                <input
                                    id="name"
                                    type="text"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />
                                <!-- Exibe a mensagem de erro para o campo 'name', se houver alguma vinda do backend -->
                                <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
                            </div>

                             <!-- Campo Categoria -->
                            <div class="mt-4">
                                <label for="category_id" class="block font-medium text-sm text-gray-700">Categoria</label>
                                <select id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.category_id">
                                    <option :value="null">-- Nenhuma --</option>
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

                            
                             <!-- Campos de Preço + Promoção    -->
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
                            <div class="mt-4">
                                <h3 class="text-lg font-medium text-gray-900">Imagens do Produto</h3>
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Upload da Imagem Principal -->
                                    <div>
                                        <label for="main_image" class="block font-medium text-sm text-gray-700">Imagem Principal</label>
                                        <input 
                                            id="main_image" 
                                            type="file" 
                                            class="mt-1 block w-full" 
                                            @input="form.main_image = $event.target.files[0]"
                                            @change="updateMainImagePreview"
                                            accept="image/*"
                                        />
                                        <!-- Pré-visualização da Imagem -->
                                        <div v-if="mainImagePreview" class="mt-4">
                                            <img :src="mainImagePreview" class="w-48 h-48 object-cover rounded-md" />
                                        </div>
                                        <p v-if="form.errors.main_image" class="text-sm text-red-600 mt-2">{{ form.errors.main_image }}</p>
                                    </div>

                                    <!-- Upload da Galeria de Imagens -->
                                    <div>
                                        <label for="gallery_images" class="block font-medium text-sm text-gray-700">Galeria de Imagens (múltiplas)</label>
                                        <input 
                                            id="gallery_images" 
                                            type="file" 
                                            class="mt-1 block w-full" 
                                            @input="form.gallery_images = $event.target.files" 
                                            multiple
                                            accept="image/*"
                                        />
                                        <p v-if="form.errors.gallery_images" class="text-sm text-red-600 mt-2">{{ form.errors.gallery_images }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botão de Submissão -->
                            <div class="flex items-center justify-end mt-4">
                                <button
                                    type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <!-- O texto do botão muda e ele fica desabilitado enquanto o formulário está sendo processado -->
                                    {{ form.processing ? 'Salvando...' : 'Salvar Produto' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
