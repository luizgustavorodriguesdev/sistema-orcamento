<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    suppliers: Object,
});

const isModalOpen = ref(false);
const editingSupplier = ref(null);

const form = useForm({
    name: '',
    cnpj: '',
    email: '',
    phone: '',
    contact_name: '',
});

const openCreateModal = () => {
    editingSupplier.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (s) => {
    editingSupplier.value = s;
    form.name = s.name;
    form.cnpj = s.cnpj || '';
    form.email = s.email || '';
    form.phone = s.phone || '';
    form.contact_name = s.contact_name || '';
    form.clearErrors();
    isModalOpen.value = true;
};

const submit = () => {
    if (editingSupplier.value) {
        form.put(route('suppliers.update', editingSupplier.value.id), {
            onSuccess: () => {
                isModalOpen.value = false;
                alert('Fornecedor atualizado com sucesso!');
            }
        });
    } else {
        form.post(route('suppliers.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                alert('Fornecedor cadastrado com sucesso!');
            }
        });
    }
};
</script>

<template>
    <Head title="Gerenciamento de Fornecedores" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Fornecedores</h2>
                    <p class="text-xs text-slate-400 mt-1">Gerencie a base de parceiros fornecedores para ordens de compra.</p>
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
                        Novo Fornecedor
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-5 border-b border-slate-800">
                        <h3 class="text-sm font-black text-white">Lista de Fornecedores</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-slate-300">
                            <thead class="text-xxs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 font-black">Fornecedor</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">CNPJ</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">E-mail</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Telefone</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Contato Principal</th>
                                    <th scope="col" class="px-6 py-3.5 font-black text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in suppliers.data" :key="s.id" class="border-b border-slate-800/50 hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4 font-bold text-white">{{ s.name }}</td>
                                    <td class="px-6 py-4 font-mono text-slate-400">{{ s.cnpj || '-' }}</td>
                                    <td class="px-6 py-4 text-slate-400">{{ s.email || '-' }}</td>
                                    <td class="px-6 py-4 text-slate-400 font-mono">{{ s.phone || '-' }}</td>
                                    <td class="px-6 py-4 text-slate-400 font-semibold">{{ s.contact_name || '-' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-3">
                                            <button 
                                                @click="openEditModal(s)" 
                                                class="text-xxs font-black text-blue-400 hover:text-blue-300 uppercase tracking-wider transition-colors"
                                            >
                                                Editar
                                            </button>
                                            <Link 
                                                :href="route('suppliers.destroy', s.id)" 
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
                                <tr v-if="suppliers.data.length === 0">
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-500">Nenhum fornecedor cadastrado ainda.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINAÇÃO -->
                    <div class="p-5 border-t border-slate-800 flex justify-center">
                        <div class="flex">
                            <template v-for="(link, key) in suppliers.links" :key="key">
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
                    <h3 class="text-sm font-black text-white">{{ editingSupplier ? 'Editar Fornecedor' : 'Novo Fornecedor' }}</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="name" class="block text-xxs font-black text-slate-400 uppercase mb-1">Razão Social / Nome</label>
                        <input 
                            id="name" 
                            type="text" 
                            v-model="form.name" 
                            placeholder="Ex: Fornecedor de Copos S.A." 
                            class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                            required
                        />
                        <p v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="cnpj" class="block text-xxs font-black text-slate-400 uppercase mb-1">CNPJ (Opcional)</label>
                        <input 
                            id="cnpj" 
                            type="text" 
                            v-model="form.cnpj" 
                            placeholder="Ex: 00.000.000/0001-00" 
                            class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                        />
                        <p v-if="form.errors.cnpj" class="text-xs text-rose-500 mt-1">{{ form.errors.cnpj }}</p>
                    </div>

                    <div>
                        <label for="email" class="block text-xxs font-black text-slate-400 uppercase mb-1">E-mail Comercial</label>
                        <input 
                            id="email" 
                            type="email" 
                            v-model="form.email" 
                            placeholder="Ex: comercial@fornecedor.com" 
                            class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                        />
                        <p v-if="form.errors.email" class="text-xs text-rose-500 mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-xxs font-black text-slate-400 uppercase mb-1">Telefone</label>
                            <input 
                                id="phone" 
                                type="text" 
                                v-model="form.phone" 
                                placeholder="Ex: (11) 99999-9999" 
                                class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                            />
                            <p v-if="form.errors.phone" class="text-xs text-rose-500 mt-1">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label for="contact_name" class="block text-xxs font-black text-slate-400 uppercase mb-1">Pessoa de Contato</label>
                            <input 
                                id="contact_name" 
                                type="text" 
                                v-model="form.contact_name" 
                                placeholder="Ex: João Silva" 
                                class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                            />
                            <p v-if="form.errors.contact_name" class="text-xs text-rose-500 mt-1">{{ form.errors.contact_name }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                        <button type="button" @click="isModalOpen = false" class="text-xxs text-slate-400 hover:text-white font-bold px-4 py-2">Cancelar</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xxs px-4 py-2 rounded-xl transition-all">{{ editingSupplier ? 'Salvar' : 'Criar' }}</button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
