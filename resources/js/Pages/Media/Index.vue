<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    mediaItems: Object,
    filters: Object,
});

const searchQuery = ref(props.filters?.q || '');
const selectedMedia = ref(null);

const handleSearch = () => {
    router.get(route('media.index'), { q: searchQuery.value }, {
        preserveState: true,
        replace: true,
    });
};

// Form para upload de arquivo
const uploadForm = useForm({
    file: null,
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    uploadForm.file = file;
    uploadForm.post(route('media.store'), {
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.reset();
        },
    });
};

const isDragging = ref(false);

const handleDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer?.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        alert('Por favor, envie apenas arquivos de imagem.');
        return;
    }

    uploadForm.file = file;
    uploadForm.post(route('media.store'), {
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.reset();
        },
    });
};

// Form para edição dos dados de SEO
const seoForm = useForm({
    title: '',
    alt_text: '',
    caption: '',
    description: '',
});

const selectMedia = (media) => {
    selectedMedia.value = media;
    seoForm.title = media.title || '';
    seoForm.alt_text = media.alt_text || '';
    seoForm.caption = media.caption || '';
    seoForm.description = media.description || '';
};

const saveSeo = () => {
    if (!selectedMedia.value) return;

    seoForm.put(route('media.update', selectedMedia.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Atualiza os dados no item selecionado localmente para refletir as mudanças
            selectedMedia.value.title = seoForm.title;
            selectedMedia.value.alt_text = seoForm.alt_text;
            selectedMedia.value.caption = seoForm.caption;
            selectedMedia.value.description = seoForm.description;
            alert('Configurações de SEO salvas com sucesso!');
        },
    });
};

const deleteMedia = () => {
    if (!selectedMedia.value) return;

    if (confirm('Tem certeza que deseja apagar esta mídia permanentemente do servidor?')) {
        router.delete(route('media.destroy', selectedMedia.value.id), {
            onSuccess: () => {
                selectedMedia.value = null;
            },
        });
    }
};

const formatBytes = (bytes, decimals = 2) => {
    if (!bytes) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
};

const copied = ref(false);
const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
};
</script>

<template>
    <Head title="Gerenciador de Mídias" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gerenciador de Mídias</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row gap-8 items-start">
                    
                    <!-- AREA CENTRAL (Upload & Grid de Mídias) -->
                    <div class="flex-grow w-full lg:w-2/3 space-y-6">
                        <!-- Card de Upload -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Enviar Novo Arquivo</h3>
                            
                            <!-- Área de Upload Simplificada -->
                            <label 
                                class="border-2 border-dashed rounded-2xl p-8 text-center transition-all flex flex-col items-center justify-center space-y-4 cursor-pointer block"
                                :class="isDragging ? 'border-blue-500 bg-blue-50/50' : 'border-slate-200 hover:border-blue-400 bg-slate-50'"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                            >
                                <div class="p-3 bg-blue-50 text-blue-500 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <div class="text-sm">
                                    <span class="font-bold text-blue-600">Clique aqui para escolher</span>
                                    <span class="text-slate-500"> ou arraste uma imagem de até 5MB</span>
                                    <input 
                                        type="file" 
                                        accept="image/*" 
                                        class="hidden" 
                                        @change="handleFileUpload"
                                        :disabled="uploadForm.processing"
                                    />
                                </div>
                                <p class="text-xs text-slate-400">Formatos aceitos: JPG, PNG, GIF, WEBP</p>
                                <div v-if="uploadForm.processing" class="text-xs font-semibold text-blue-600">Enviando mídia...</div>
                                <div v-if="uploadForm.errors.file" class="text-xs font-bold text-rose-600">{{ uploadForm.errors.file }}</div>
                            </label>
                        </div>

                        <!-- Grid de Mídias -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-6">
                            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
                                <h3 class="text-lg font-bold text-gray-800">Biblioteca de Imagens</h3>
                                
                                <!-- Pesquisa -->
                                <div class="relative w-full sm:w-72">
                                    <input 
                                        v-model="searchQuery" 
                                        @keyup.enter="handleSearch"
                                        type="text" 
                                        placeholder="Buscar no acervo..." 
                                        class="w-full bg-slate-50 border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-blue-500 focus:border-blue-500 p-2.5 pl-8"
                                    />
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-2.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                            </div>

                            <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                            </div>

                            <!-- Listagem -->
                            <div v-if="mediaItems.data && mediaItems.data.length > 0" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                                <div 
                                    v-for="media in mediaItems.data" 
                                    :key="media.id" 
                                    @click="selectMedia(media)"
                                    class="bg-white border rounded-2xl overflow-hidden cursor-pointer hover:shadow transition-all p-2 flex flex-col justify-between"
                                    :class="{'border-blue-500 ring-2 ring-blue-100': selectedMedia?.id === media.id, 'border-slate-100': selectedMedia?.id !== media.id}"
                                >
                                    <div class="aspect-square w-full rounded-xl bg-slate-50 overflow-hidden flex items-center justify-center p-1">
                                        <img :src="media.url" :alt="media.alt_text" class="max-w-full max-h-full object-contain rounded-lg">
                                    </div>
                                    <div class="mt-2 text-center">
                                        <p class="text-xxs font-bold text-slate-700 truncate px-1">{{ media.title || media.filename }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-20 space-y-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="text-xs text-slate-400 font-bold">Nenhuma mídia encontrada na biblioteca.</p>
                            </div>

                            <!-- Paginação -->
                            <div v-if="mediaItems.links && mediaItems.links.length > 3" class="mt-6 flex justify-center gap-1">
                                <Component
                                    v-for="(link, i) in mediaItems.links"
                                    :key="i"
                                    :is="link.url ? 'Link' : 'span'"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    class="px-3 py-1.5 text-xxs font-bold rounded-lg border transition-all"
                                    :class="{
                                        'bg-blue-600 text-white border-blue-600 shadow-sm': link.active,
                                        'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100': !link.active && link.url,
                                        'text-slate-300 cursor-not-allowed border-slate-100': !link.url
                                    }"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- BARRA LATERAL (Detalhes de Metadados e SEO da Mídia selecionada) -->
                    <aside class="w-full lg:w-1/3 bg-white shadow-sm sm:rounded-lg p-6 space-y-6 lg:sticky lg:top-6">
                        <h3 class="text-lg font-bold text-gray-800 border-b border-slate-100 pb-3">Metadados & SEO</h3>
                        
                        <div v-if="selectedMedia" class="space-y-6">
                            <!-- Preview Imagem -->
                            <div class="aspect-video w-full rounded-2xl bg-slate-50 overflow-hidden flex items-center justify-center p-2 border">
                                <img :src="selectedMedia.url" :alt="selectedMedia.alt_text" class="max-w-full max-h-full object-contain rounded-lg">
                            </div>

                            <!-- Ficha Técnica -->
                            <div class="bg-slate-50 rounded-2xl p-4 text-xs space-y-2 border">
                                <p class="truncate"><strong class="text-slate-700">Arquivo:</strong> {{ selectedMedia.filename }}</p>
                                <p><strong class="text-slate-700">Dimensão/Tipo:</strong> {{ selectedMedia.mime_type }}</p>
                                <p><strong class="text-slate-700">Tamanho:</strong> {{ formatBytes(selectedMedia.size) }}</p>
                                
                                <!-- Campo URL com Copy Button -->
                                <div class="mt-4 space-y-1">
                                    <label class="block font-bold text-slate-700">Link URL Público:</label>
                                    <div class="flex gap-1.5">
                                        <input 
                                            type="text" 
                                            readonly 
                                            :value="selectedMedia.url" 
                                            class="w-full bg-white border border-slate-200 text-slate-500 rounded-lg p-1.5 text-xxs font-mono"
                                        />
                                        <button 
                                            @click="copyToClipboard(selectedMedia.url)"
                                            class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 rounded-lg border text-xxs font-bold transition-all whitespace-nowrap"
                                        >
                                            {{ copied ? 'Copiado!' : 'Copiar' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Formulário SEO -->
                            <form @submit.prevent="saveSeo" class="space-y-4">
                                <!-- Título SEO -->
                                <div>
                                    <label for="seo_title" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Título (SEO)</label>
                                    <input 
                                        v-model="seoForm.title" 
                                        type="text" 
                                        id="seo_title" 
                                        class="mt-1.5 block w-full border-gray-300 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500" 
                                        placeholder="Título SEO da imagem"
                                    />
                                    <div v-if="seoForm.errors.title" class="text-xs text-rose-600 font-semibold">{{ seoForm.errors.title }}</div>
                                </div>

                                <!-- Alt Text SEO -->
                                <div>
                                    <label for="seo_alt" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Texto Alternativo (Alt Text)</label>
                                    <input 
                                        v-model="seoForm.alt_text" 
                                        type="text" 
                                        id="seo_alt" 
                                        class="mt-1.5 block w-full border-gray-300 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500" 
                                        placeholder="Descreva a imagem (SEO alt)"
                                    />
                                    <div v-if="seoForm.errors.alt_text" class="text-xs text-rose-600 font-semibold">{{ seoForm.errors.alt_text }}</div>
                                </div>

                                <!-- Legenda -->
                                <div>
                                    <label for="seo_caption" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Legenda da Imagem</label>
                                    <input 
                                        v-model="seoForm.caption" 
                                        type="text" 
                                        id="seo_caption" 
                                        class="mt-1.5 block w-full border-gray-300 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500" 
                                        placeholder="Texto visível em legendas"
                                    />
                                    <div v-if="seoForm.errors.caption" class="text-xs text-rose-600 font-semibold">{{ seoForm.errors.caption }}</div>
                                </div>

                                <!-- Descrição -->
                                <div>
                                    <label for="seo_description" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Descrição Interna</label>
                                    <textarea 
                                        v-model="seoForm.description" 
                                        id="seo_description" 
                                        rows="3" 
                                        class="mt-1.5 block w-full border-gray-300 rounded-xl p-2.5 text-xs focus:ring-blue-500 focus:border-blue-500" 
                                        placeholder="Anotações internas sobre esta mídia"
                                    ></textarea>
                                    <div v-if="seoForm.errors.description" class="text-xs text-rose-600 font-semibold">{{ seoForm.errors.description }}</div>
                                </div>

                                <!-- Botões Ação do Card -->
                                <div class="flex gap-3 pt-2 border-t">
                                    <button 
                                        type="submit" 
                                        :disabled="seoForm.processing"
                                        class="flex-grow bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-2 px-4 rounded-xl shadow transition-all disabled:opacity-50"
                                    >
                                        {{ seoForm.processing ? 'Aguarde...' : 'Salvar SEO' }}
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="deleteMedia"
                                        class="bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold text-xs py-2 px-4 rounded-xl border border-rose-100 transition-all"
                                    >
                                        Remover
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Mensagem quando nenhum selecionado -->
                        <div v-else class="text-center py-20 text-slate-400 space-y-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <p class="text-xs font-semibold">Selecione uma imagem na biblioteca ao lado para visualizar e editar seus metadados de SEO.</p>
                        </div>
                    </aside>
                    
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.text-xxs {
    font-size: 0.65rem;
}
</style>
