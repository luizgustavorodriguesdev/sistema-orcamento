<script setup>
import { ref, watch, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: 'Selecionar Mídia',
    }
});

const emit = defineEmits(['close', 'select']);

const mediaList = ref({ data: [], links: [] });
const loading = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);

const fetchMedia = async (page = 1) => {
    loading.value = true;
    currentPage.value = page;
    try {
        const url = `/api/media?page=${page}&q=${searchQuery.value}`;
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const json = await response.json();
        mediaList.value = json;
    } catch (error) {
        console.error('Erro ao buscar mídias:', error);
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    fetchMedia(1);
};

// Lógica de upload rápido
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
            fetchMedia(1); // recarrega a lista na pág 1
        },
    });
};

const selectMedia = (media) => {
    emit('select', media);
    emit('close');
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        fetchMedia(1);
    }
});

onMounted(() => {
    if (props.show) {
        fetchMedia(1);
    }
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>

        <!-- Modal Content -->
        <div class="bg-white rounded-3xl overflow-hidden shadow-2xl max-w-4xl w-full max-h-[85vh] flex flex-col z-10 border border-slate-100 transform transition-all duration-300">
            <!-- Header -->
            <header class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-lg font-black text-slate-800">{{ title }}</h3>
                <button @click="emit('close')" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </header>

            <!-- Toolbar (Search & Quick Upload) -->
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 bg-white">
                <!-- Search Input -->
                <div class="relative flex-grow max-w-md">
                    <input 
                        v-model="searchQuery" 
                        @keyup.enter="handleSearch"
                        type="text" 
                        placeholder="Pesquisar mídia pelo nome..." 
                        class="w-full bg-slate-50 border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-blue-500 focus:border-blue-500 p-2.5 pl-8"
                    />
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 absolute left-2.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>

                <!-- Quick Upload Button -->
                <div class="flex items-center gap-2">
                    <label class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl cursor-pointer shadow transition-all flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        <span>{{ uploadForm.processing ? 'Enviando...' : 'Fazer Upload' }}</span>
                        <input 
                            type="file" 
                            accept="image/*" 
                            class="hidden" 
                            @change="handleFileUpload" 
                            :disabled="uploadForm.processing"
                        />
                    </label>
                </div>
            </div>

            <!-- Media Grid -->
            <div class="p-6 overflow-y-auto flex-grow bg-slate-50 min-h-[300px]">
                <div v-if="loading" class="flex flex-col items-center justify-center py-20 space-y-3">
                    <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-xs text-slate-400 font-bold">Buscando mídias...</span>
                </div>

                <div v-else-if="mediaList.data && mediaList.data.length > 0" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                    <div 
                        v-for="media in mediaList.data" 
                        :key="media.id" 
                        @click="selectMedia(media)"
                        class="bg-white border border-slate-100 rounded-2xl overflow-hidden cursor-pointer hover:border-blue-500 hover:ring-4 hover:ring-blue-100 transition-all shadow-sm group p-2 flex flex-col justify-between"
                    >
                        <div class="aspect-square w-full rounded-xl bg-slate-50 overflow-hidden flex items-center justify-center relative p-1">
                            <img :src="media.url" :alt="media.alt_text" class="max-w-full max-h-full object-contain rounded-lg">
                        </div>
                        <div class="mt-2 text-center">
                            <p class="text-xxs font-bold text-slate-800 truncate px-1" :title="media.filename">{{ media.title || media.filename }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-20 space-y-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <p class="text-xs text-slate-400 font-bold">Nenhuma imagem encontrada no gerenciador.</p>
                </div>
            </div>

            <!-- Footer (Pagination) -->
            <footer v-if="mediaList.links && mediaList.links.length > 3 && !loading" class="p-6 border-t border-slate-100 flex justify-center bg-white">
                <div class="flex gap-1">
                    <button
                        v-for="(link, i) in mediaList.links"
                        :key="i"
                        @click="link.url ? fetchMedia(new URL(link.url).searchParams.get('page')) : null"
                        v-html="link.label"
                        class="px-3 py-1.5 text-xxs font-bold rounded-lg border transition-all"
                        :class="{
                            'bg-blue-600 text-white border-blue-600 shadow-sm': link.active,
                            'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100': !link.active && link.url,
                            'text-slate-300 cursor-not-allowed border-slate-100': !link.url
                        }"
                        :disabled="!link.url"
                    />
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.text-xxs {
    font-size: 0.65rem;
}
</style>
