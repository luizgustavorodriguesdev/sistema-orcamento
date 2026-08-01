<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    warehouses: Object,
});

const isModalOpen = ref(false);
const editingWarehouse = ref(null);

const form = useForm({
    name: '',
    code: '',
    description: '',
    is_active: true,
});

const openCreateModal = () => {
    editingWarehouse.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (w) => {
    editingWarehouse.value = w;
    form.name = w.name;
    form.code = w.code;
    form.description = w.description || '';
    form.is_active = !!w.is_active;
    form.clearErrors();
    isModalOpen.value = true;
};

const submit = () => {
    if (editingWarehouse.value) {
        form.put(route('warehouses.update', editingWarehouse.value.id), {
            onSuccess: () => {
                isModalOpen.value = false;
                alert('Depósito atualizado com sucesso!');
            }
        });
    } else {
        form.post(route('warehouses.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                alert('Depósito criado com sucesso!');
            }
        });
    }
};
</script>

<template>
    <Head title="Gerenciamento de Depósitos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Almoxarifados / Depósitos</h2>
                    <p class="text-xs text-slate-400 mt-1">Gerencie os locais físicos de armazenamento do sistema.</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('stock.index')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">
                        Voltar ao Estoque
                    </Link>
                    <button 
                        @click="openCreateModal"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-3 py-2 rounded-xl transition-all shadow-lg shadow-blue-600/10 flex items-center gap-1"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Novo Depósito
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-5 border-b border-slate-800">
                        <h3 class="text-sm font-black text-white">Lista de Depósitos Cadastrados</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-slate-300">
                            <thead class="text-xxs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 font-black">Depósito</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Código Único</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Descrição</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Status</th>
                                    <th scope="col" class="px-6 py-3.5 font-black text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="w in warehouses.data" :key="w.id" class="border-b border-slate-800/50 hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4 font-bold text-white">{{ w.name }}</td>
                                    <td class="px-6 py-4 font-mono text-slate-400 font-semibold">{{ w.code }}</td>
                                    <td class="px-6 py-4 text-slate-400">{{ w.description || '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span 
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xxs font-bold"
                                            :class="w.is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'"
                                        >
                                            {{ w.is_active ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-3">
                                            <button 
                                                @click="openEditModal(w)" 
                                                class="text-xxs font-black text-blue-400 hover:text-blue-300 uppercase tracking-wider transition-colors"
                                            >
                                                Editar
                                            </button>
                                            <Link 
                                                :href="route('warehouses.destroy', w.id)" 
                                                method="delete" 
                                                as="button" 
                                                class="text-xxs font-black text-rose-500 hover:text-rose-400 uppercase tracking-wider transition-colors"
                                                preserve-scroll
                                            >
                                                Excluir
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="warehouses.data.length === 0">
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-500">Nenhum depósito cadastrado ainda.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINAÇÃO -->
                    <div class="p-5 border-t border-slate-800 flex justify-center">
                        <div class="flex">
                            <template v-for="(link, key) in warehouses.links" :key="key">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-3 py-1.5 mx-1 text-xs rounded-xl font-bold transition-all"
                                    :class="{ 'bg-blue-600 text-white shadow-lg shadow-blue-600/10': link.active, 'text-slate-400 bg-slate-950 border border-slate-800 hover:text-white': !link.active }"
                                />
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- MODAL EDIT/CREATE -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
                <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-black text-white">{{ editingWarehouse ? 'Editar Depósito' : 'Novo Depósito' }}</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="name" class="block text-xxs font-black text-slate-400 uppercase mb-1">Nome do Depósito</label>
                        <input 
                            id="name" 
                            type="text" 
                            v-model="form.name" 
                            placeholder="Ex: Depósito Principal, Almoxarifado 02" 
                            class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                            required
                        />
                        <p v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="code" class="block text-xxs font-black text-slate-400 uppercase mb-1">Código Único (Sigla)</label>
                        <input 
                            id="code" 
                            type="text" 
                            v-model="form.code" 
                            placeholder="Ex: DEP-01, SHOWROOM" 
                            class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                            required
                        />
                        <p v-if="form.errors.code" class="text-xs text-rose-500 mt-1">{{ form.errors.code }}</p>
                    </div>

                    <div>
                        <label for="description" class="block text-xxs font-black text-slate-400 uppercase mb-1">Descrição</label>
                        <textarea 
                            id="description" 
                            rows="2" 
                            v-model="form.description" 
                            placeholder="Localização ou detalhes do depósito..." 
                            class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500"
                        ></textarea>
                        <p v-if="form.errors.description" class="text-xs text-rose-500 mt-1">{{ form.errors.description }}</p>
                    </div>

                    <div class="flex items-center">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="rounded border-slate-800 text-blue-600 bg-slate-950 focus:ring-blue-500" />
                            <span class="ms-2 text-xs text-slate-300 font-semibold">Depósito Ativo</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                        <button type="button" @click="isModalOpen = false" class="text-xxs text-slate-400 hover:text-white font-bold px-4 py-2">Cancelar</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xxs px-4 py-2 rounded-xl transition-all">{{ editingWarehouse ? 'Salvar' : 'Criar' }}</button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
