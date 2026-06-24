<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    items: Object,
    filters: Object,
});

// Busca reativa
const searchQuery = ref(props.filters.search || '');
const handleSearch = () => {
    router.get(route('personalization-types.index'), { search: searchQuery.value }, {
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
});

const openCreateModal = () => {
    isEditing.value = false;
    currentId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    currentId.value = item.id;
    form.name = item.name;
    form.clearErrors();
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('personalization-types.update', currentId.value), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('personalization-types.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteItem = (id) => {
    if (confirm('Tem certeza que deseja excluir esta personalização? Isto removerá a associação em todos os produtos.')) {
        router.delete(route('personalization-types.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Personalizações" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestão de Personalizações</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <!-- Top Header Actions -->
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                            <h3 class="text-2xl font-bold">Lista de Personalizações</h3>
                            
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <!-- Campo de Busca -->
                                <div class="relative flex-1 sm:w-64">
                                    <input 
                                        type="text" 
                                        v-model="searchQuery" 
                                        placeholder="Buscar..." 
                                        @input="handleSearch"
                                        class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>

                                <button 
                                    @click="openCreateModal" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors whitespace-nowrap"
                                >
                                    Adicionar Personalização
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
                                        <th scope="col" class="px-6 py-3">Nome</th>
                                        <th scope="col" class="px-6 py-3">Slug (Identificador Único)</th>
                                        <th scope="col" class="px-6 py-3 w-40 text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in items.data" :key="item.id" class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ item.name }}
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs">
                                            {{ item.slug }}
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <button @click="openEditModal(item)" class="font-medium text-blue-600 hover:underline mr-4">Editar</button>
                                            <button @click="deleteItem(item.id)" class="font-medium text-red-600 hover:underline">Deletar</button>
                                        </td>
                                    </tr>
                                    <tr v-if="items.data.length === 0">
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                            Nenhuma personalização encontrada.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Links -->
                        <div v-if="items.links && items.links.length > 3" class="mt-6 flex justify-center">
                            <div class="flex">
                                <template v-for="(link, key) in items.links" :key="key">
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
                    <h3 class="text-lg font-bold text-gray-900">{{ isEditing ? 'Editar Personalização' : 'Nova Personalização' }}</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label for="name" class="block font-medium text-sm text-gray-700">Nome da Personalização</label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Ex: Silk-Screen, Gravação Laser, Sublimação"
                            class="w-full text-sm border-gray-300 rounded-lg mt-1 block shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        <p v-if="form.errors.name" class="text-sm text-red-600 mt-2">{{ form.errors.name }}</p>
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
