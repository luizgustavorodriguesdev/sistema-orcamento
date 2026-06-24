<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    colors: Object,
    filters: Object,
});

// Busca reativa
const searchQuery = ref(props.filters.search || '');
const handleSearch = () => {
    router.get(route('colors.index'), { search: searchQuery.value }, {
        preserveState: true,
        replace: true,
    });
};

// Modal de Formulário
const isModalOpen = ref(false);
const isEditing = ref(false);
const currentId = ref(null);

const form = useForm({
    name: '',
    hex_code: '#3b82f6',
});

const openCreateModal = () => {
    isEditing.value = false;
    currentId.value = null;
    form.reset();
    form.hex_code = '#3b82f6';
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (color) => {
    isEditing.value = true;
    currentId.value = color.id;
    form.name = color.name;
    form.hex_code = color.hex_code || '#ffffff';
    form.clearErrors();
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('colors.update', currentId.value), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('colors.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteColor = (id) => {
    if (confirm('Tem certeza que deseja excluir esta cor? Isto removerá a associação em todos os produtos.')) {
        router.delete(route('colors.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Cores" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestão de Cores</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <!-- Top Header Actions -->
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                            <h3 class="text-2xl font-bold">Lista de Cores</h3>
                            
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <!-- Campo de Busca -->
                                <div class="relative flex-1 sm:w-64">
                                    <input 
                                        type="text" 
                                        v-model="searchQuery" 
                                        placeholder="Buscar cores..." 
                                        @input="handleSearch"
                                        class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>

                                <button 
                                    @click="openCreateModal" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors whitespace-nowrap"
                                >
                                    Adicionar Cor
                                </button>
                            </div>
                        </div>

                        <!-- Flash Message -->
                        <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                        </div>

                        <!-- Table -->
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Cor</th>
                                        <th scope="col" class="px-6 py-3">Código Hex</th>
                                        <th scope="col" class="px-6 py-3">Slug</th>
                                        <th scope="col" class="px-6 py-3 w-40 text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="color in colors.data" :key="color.id" class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <span class="w-6 h-6 rounded-full border border-gray-200 block shadow-sm flex-shrink-0" :style="{ backgroundColor: color.hex_code || '#fff' }"></span>
                                                <span>{{ color.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs">
                                            {{ color.hex_code || '-' }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs">
                                            {{ color.slug }}
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <button @click="openEditModal(color)" class="font-medium text-blue-600 hover:underline mr-4">Editar</button>
                                            <button @click="deleteColor(color.id)" class="font-medium text-red-600 hover:underline">Deletar</button>
                                        </td>
                                    </tr>
                                    <tr v-if="colors.data.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            Nenhuma cor encontrada.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Links -->
                        <div v-if="colors.links && colors.links.length > 3" class="mt-6 flex justify-center">
                            <div class="flex">
                                <template v-for="(link, key) in colors.links" :key="key">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-4 py-2 mx-1 text-sm rounded-md border text-slate-700"
                                        :class="{ 'bg-blue-500 text-white border-blue-500': link.active, 'text-gray-700 bg-white hover:bg-gray-100 border-gray-300': !link.active }"
                                    />
                                    <span
                                        v-else
                                        v-html="link.label"
                                        class="px-4 py-2 mx-1 text-sm rounded-md text-gray-400 cursor-not-allowed border bg-gray-50"
                                    />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- FORM MODAL (Modal de Criação/Edição) -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-4">
                <div class="flex justify-between items-center border-b pb-2">
                    <h3 class="text-lg font-bold text-gray-900">{{ isEditing ? 'Editar Cor' : 'Nova Cor' }}</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label for="name" class="block font-medium text-sm text-gray-700">Nome da Cor</label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Ex: Azul Bic, Vermelho Vivo, Preto Fosco"
                            class="w-full text-sm border-gray-300 rounded-lg mt-1 block shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="hex_code" class="block font-medium text-sm text-gray-700">Código Hexadecimal</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input
                                id="hex_code"
                                type="text"
                                v-model="form.hex_code"
                                placeholder="#ffffff"
                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono"
                            />
                            <input
                                type="color"
                                v-model="form.hex_code"
                                class="w-12 h-9 border border-gray-300 rounded-lg cursor-pointer p-0"
                            />
                        </div>
                        <p v-if="form.errors.hex_code" class="text-sm text-red-600 mt-2">{{ form.errors.hex_code }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t">
                        <button 
                            type="button" 
                            @click="isModalOpen = false" 
                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors"
                            :class="{'opacity-50': form.processing}"
                        >
                            {{ form.processing ? 'Salvando...' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
