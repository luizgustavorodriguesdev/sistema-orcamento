<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    parentCategories: Array,
});

// Usamos o 'useForm' para gerir os dados do formulário.
const form = useForm({
    name: '',
    description: '',
    is_featured: false,
    show_in_highlighted_menu: false,
    parent_id: '',
    image: null,
    mega_menu_banner: null,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
});

// Função de submissão do formulário.
const submit = () => {
    form.post(route('categories.store'));
};
</script>

<template>
    <Head title="Adicionar Categoria" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Adicionar Nova Categoria</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <form @submit.prevent="submit">
                            <!-- Campo Nome -->
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">Nome da Categoria</label>
                                <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.name" required autofocus />
                                <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
                            </div>

                            <!-- Campo Categoria Pai (Para subcategorias) -->
                            <div class="mt-4">
                                <label for="parent_id" class="block font-medium text-sm text-gray-700">Categoria Pai (Deixe em branco para Categoria Principal)</label>
                                <select id="parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.parent_id">
                                    <option value="">Nenhuma (Categoria Principal)</option>
                                    <option v-for="cat in parentCategories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.parent_id" class="text-sm text-red-600 mt-2">{{ form.errors.parent_id }}</p>
                            </div>

                            <!-- Campo Descrição -->
                             <div class="mt-4">
                                 <label for="description" class="block font-medium text-sm text-gray-700">Descrição (Opcional)</label>
                                 <textarea id="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.description"></textarea>
                                 <p v-if="form.errors.description" class="text-sm text-red-600 mt-2">{{ form.errors.description }}</p>
                             </div>

                             <!-- Campo Imagem -->
                             <div class="mt-4">
                                 <label for="image" class="block font-medium text-sm text-gray-700">Imagem da Categoria (Para a Vitrine)</label>
                                 <input id="image" type="file" class="mt-1 block w-full border border-gray-300 rounded-md p-1 bg-white" @input="form.image = $event.target.files[0]" />
                                 <p v-if="form.errors.image" class="text-sm text-red-600 mt-2">{{ form.errors.image }}</p>
                             </div>

                             <!-- Campo Banner do Mega Menu -->
                             <div v-if="!form.parent_id" class="mt-4">
                                 <label for="mega_menu_banner" class="block font-medium text-sm text-gray-700">Banner Promocional do Mega Menu (Opcional)</label>
                                 <input id="mega_menu_banner" type="file" class="mt-1 block w-full border border-gray-300 rounded-md p-1 bg-white" @input="form.mega_menu_banner = $event.target.files[0]" />
                                 <p v-if="form.errors.mega_menu_banner" class="text-sm text-red-600 mt-2">{{ form.errors.mega_menu_banner }}</p>
                             </div>

                             <!-- Campo Destaque -->
                             <div class="mt-4">
                                 <label class="inline-flex items-center">
                                     <input type="checkbox" v-model="form.is_featured" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                     <span class="ms-2 text-sm text-gray-600">Categoria em Destaque (será exibida na Vitrine Principal)</span>
                                 </label>
                                 <p v-if="form.errors.is_featured" class="text-sm text-red-600 mt-2">{{ form.errors.is_featured }}</p>
                             </div>

                             <!-- Campo Exibir no Menu em Destaque -->
                             <div class="mt-4">
                                 <label class="inline-flex items-center">
                                     <input type="checkbox" v-model="form.show_in_highlighted_menu" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                     <span class="ms-2 text-sm text-gray-600">Exibir no Menu em Destaque</span>
                                 </label>
                                 <p v-if="form.errors.show_in_highlighted_menu" class="text-sm text-red-600 mt-2">{{ form.errors.show_in_highlighted_menu }}</p>
                             </div>

                             <!-- Secção de SEO -->
                             <div class="mt-6 pt-6 border-t">
                                 <h3 class="text-lg font-medium text-gray-900">Configurações de SEO</h3>
                                 <p class="text-xs text-gray-500 mb-4">Insira os metadados para melhorar o ranqueamento desta categoria nos motores de busca.</p>
                                 
                                 <div class="space-y-4">
                                     <div>
                                         <label for="meta_title" class="block font-medium text-sm text-gray-700">Meta Title (Título da Página)</label>
                                         <input id="meta_title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.meta_title" placeholder="Deixe em branco para usar o nome da categoria" />
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
                             <div class="flex items-center justify-end mt-4">
                                 <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                     {{ form.processing ? 'A Guardar...' : 'Guardar Categoria' }}
                                 </button>
                             </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
