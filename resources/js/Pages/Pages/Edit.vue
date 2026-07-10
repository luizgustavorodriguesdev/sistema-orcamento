<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Editor from '@/Components/Editor.vue';
import MediaSelectorModal from '@/Components/MediaSelectorModal.vue';

const props = defineProps({
    page: Object,
});

const form = useForm({
    title: props.page.title || '',
    slug: props.page.slug || '',
    content: props.page.content || '',
    custom_css: props.page.custom_css || '',
    is_active: props.page.is_active === 1 || props.page.is_active === true,
    meta_title: props.page.meta_title || '',
    meta_description: props.page.meta_description || '',
    meta_keywords: props.page.meta_keywords || '',
});

const submit = () => {
    form.put(route('pages.update', props.page.id));
};

const activeTab = ref('visual'); // 'visual' | 'html'
const showMediaModal = ref(false);

const onMediaSelect = (media) => {
    const imageTag = `<img src="${media.url}" alt="${media.alt_text || ''}" title="${media.title || ''}" class="my-4 max-w-full h-auto rounded-xl">`;
    if (activeTab.value === 'html') {
        form.content += "\n" + imageTag;
    } else {
        form.content += imageTag;
    }
};
</script>

<template>
    <Head :title="`Editar Página - ${page.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Página: {{ page.title }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold">Modificar Página</h3>
                            <Link :href="route('pages.index')" class="text-blue-500 hover:underline text-sm font-semibold">
                                &larr; Voltar
                            </Link>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6 max-w-5xl">
                            <!-- Titulo -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Título da Página</label>
                                <input 
                                    v-model="form.title" 
                                    type="text" 
                                    id="title" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                                    required 
                                />
                                <div v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</div>
                            </div>

                            <!-- Slug -->
                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700">Slug (URL SEO)</label>
                                <input 
                                    v-model="form.slug" 
                                    type="text" 
                                    id="slug" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                                />
                                <div v-if="form.errors.slug" class="text-sm text-red-600 mt-1">{{ form.errors.slug }}</div>
                            </div>

                            <!-- Status Ativo -->
                            <div class="flex items-center">
                                <input 
                                    v-model="form.is_active" 
                                    type="checkbox" 
                                    id="is_active" 
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <label for="is_active" class="ml-2 block text-sm font-medium text-gray-900">
                                    Tornar esta página ativa e visível ao público
                                </label>
                            </div>

                            <!-- Conteúdo (Editor Visual vs Editor HTML) -->
                            <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                                <div class="bg-slate-50 p-4 border-b border-slate-200 flex flex-wrap justify-between items-center gap-4">
                                    <div class="flex gap-2">
                                        <button 
                                            type="button" 
                                            @click="activeTab = 'visual'"
                                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition-colors"
                                            :class="{'bg-blue-600 text-white shadow-sm': activeTab === 'visual', 'text-slate-600 hover:bg-slate-100': activeTab !== 'visual'}"
                                        >
                                            Editor Visual
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="activeTab = 'html'"
                                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition-colors"
                                            :class="{'bg-blue-600 text-white shadow-sm': activeTab === 'html', 'text-slate-600 hover:bg-slate-100': activeTab !== 'html'}"
                                        >
                                            Código Fonte (HTML)
                                        </button>
                                    </div>

                                    <!-- Botão Inserir Mídia -->
                                    <button 
                                        type="button" 
                                        @click="showMediaModal = true"
                                        class="bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold text-xs px-3 py-1.5 rounded-xl shadow-sm transition-all flex items-center gap-1.5"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <span>Inserir Imagem das Mídias</span>
                                    </button>
                                </div>

                                <div class="p-4 bg-white">
                                    <!-- Editor Tiptap -->
                                    <div v-show="activeTab === 'visual'">
                                        <Editor v-model="form.content" />
                                    </div>
                                    <!-- Textarea Código HTML -->
                                    <div v-show="activeTab === 'html'">
                                        <textarea 
                                            v-model="form.content" 
                                            rows="12" 
                                            class="w-full bg-slate-900 text-slate-100 font-mono text-xs rounded-xl border-slate-800 p-4 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Cole ou escreva seu código HTML cru aqui..."
                                        ></textarea>
                                    </div>
                                    <div v-if="form.errors.content" class="text-sm text-red-600 mt-1">{{ form.errors.content }}</div>
                                </div>
                            </div>

                            <!-- CSS Customizado -->
                            <div class="space-y-2">
                                <label for="custom_css" class="block text-sm font-medium text-gray-700">CSS Personalizado (Estilo da Página)</label>
                                <p class="text-xs text-slate-400 font-light mb-2">Adicione regras de estilos exclusivas para esta página (Ex: .btn-custom { color: red; }). Não é necessário colocar a tag &lt;style&gt;.</p>
                                <textarea 
                                    id="custom_css" 
                                    v-model="form.custom_css" 
                                    rows="8" 
                                    class="w-full bg-slate-900 text-teal-400 font-mono text-xs rounded-xl border-slate-800 p-4 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Escreva seu CSS aqui..."
                                ></textarea>
                                <div v-if="form.errors.custom_css" class="text-sm text-red-600 mt-1">{{ form.errors.custom_css }}</div>
                            </div>

                            <!-- Secção de SEO -->
                            <div class="mt-6 pt-6 border-t">
                                <h3 class="text-lg font-medium text-gray-900">Configurações de SEO</h3>
                                <p class="text-xs text-gray-500 mb-4">Insira os metadados para melhorar o ranqueamento desta página nos motores de busca.</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="meta_title" class="block font-medium text-sm text-gray-700">Meta Title (Título da Página)</label>
                                        <input id="meta_title" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" v-model="form.meta_title" placeholder="Deixe em branco para usar o título da página" />
                                        <div v-if="form.errors.meta_title" class="text-sm text-red-600 mt-1">{{ form.errors.meta_title }}</div>
                                    </div>
                                    <div>
                                        <label for="meta_description" class="block font-medium text-sm text-gray-700">Meta Description (Descrição da Página)</label>
                                        <textarea id="meta_description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" v-model="form.meta_description" rows="3" placeholder="Insira uma descrição resumida de até 160 caracteres"></textarea>
                                        <div v-if="form.errors.meta_description" class="text-sm text-red-600 mt-1">{{ form.errors.meta_description }}</div>
                                    </div>
                                    <div>
                                        <label for="meta_keywords" class="block font-medium text-sm text-gray-700">Meta Keywords (Palavras-chave separadas por vírgula)</label>
                                        <input id="meta_keywords" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" v-model="form.meta_keywords" placeholder="Ex: quem somos, instituicional, brindes" />
                                        <div v-if="form.errors.meta_keywords" class="text-sm text-red-600 mt-1">{{ form.errors.meta_keywords }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="flex gap-4">
                                <button 
                                    type="submit" 
                                    :disabled="form.processing" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded shadow disabled:opacity-50"
                                >
                                    Atualizar Página
                                </button>
                                <Link 
                                    :href="route('pages.index')" 
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2.5 px-6 rounded text-center"
                                >
                                    Cancelar
                                </Link>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Modal Seletor de Mídias -->
    <MediaSelectorModal 
        :show="showMediaModal" 
        @close="showMediaModal = false" 
        @select="onMediaSelect" 
    />
</template>
