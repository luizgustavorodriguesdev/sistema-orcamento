<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sessions: Object,
    warehouses: Array,
});

const isModalOpen = ref(false);

const newSessionForm = useForm({
    warehouse_id: props.warehouses[0]?.id || '',
    description: '',
});

const submitStartSession = () => {
    newSessionForm.post(route('inventory.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            newSessionForm.reset('description');
            alert('Sessão de inventário iniciada com sucesso!');
        }
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'Em_Andamento': 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'Finalizado': 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'Cancelado': 'bg-rose-500/10 text-rose-400 border-rose-500/20',
    };
    return classes[status] || 'bg-slate-500/10 text-slate-400';
};
</script>

<template>
    <Head title="Sessões de Inventário" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Inventários Físicos (Auditorias)</h2>
                    <p class="text-xs text-slate-400 mt-1">Abra sessões de contagem física para auditar e ajustar saldos de estoques.</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('stock.index')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">
                        Voltar ao Estoque
                    </Link>
                    <button 
                        @click="isModalOpen = true"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs px-3 py-2 rounded-xl transition-all shadow-lg shadow-purple-600/10 flex items-center gap-1"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Novo Inventário
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-5 border-b border-slate-800">
                        <h3 class="text-sm font-black text-white">Histórico de Sessões de Inventário</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-slate-300">
                            <thead class="text-xxs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 font-black">ID</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Descrição</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Depósito Auditado</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Status</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Criador</th>
                                    <th scope="col" class="px-6 py-3.5 font-black">Concluído Em</th>
                                    <th scope="col" class="px-6 py-3.5 font-black text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="session in sessions.data" :key="session.id" class="border-b border-slate-800/50 hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-slate-500">#{{ session.id }}</td>
                                    <td class="px-6 py-4 font-bold text-white">{{ session.description }}</td>
                                    <td class="px-6 py-4 text-slate-400 font-semibold">{{ session.warehouse?.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded border text-[10px] font-black uppercase tracking-wider" :class="getStatusBadgeClass(session.status)">
                                            {{ session.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-400">{{ session.creator?.name || 'Sistema' }}</td>
                                    <td class="px-6 py-4 text-slate-400 font-mono">{{ formatDate(session.completed_at) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <Link 
                                            :href="route('inventory.show', session.id)" 
                                            class="text-xxs font-black uppercase tracking-wider transition-colors"
                                            :class="session.status === 'Em_Andamento' ? 'text-blue-400 hover:text-blue-300' : 'text-slate-500 hover:text-white'"
                                        >
                                            {{ session.status === 'Em_Andamento' ? 'Conferir' : 'Ver Detalhes' }}
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="sessions.data.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-500">Nenhum inventário registrado.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINAÇÃO -->
                    <div class="p-5 border-t border-slate-800 flex justify-center">
                        <div class="flex">
                            <template v-for="(link, key) in sessions.links" :key="key">
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

        <!-- MODAL PARA ABERTURA DE INVENTÁRIO -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
                <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-black text-white">Iniciar Inventário</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submitStartSession" class="space-y-4">
                    <div>
                        <label for="modal_warehouse" class="block text-xxs font-black text-slate-400 uppercase mb-1">Depósito a Auditar</label>
                        <select 
                            id="modal_warehouse" 
                            v-model="newSessionForm.warehouse_id" 
                            class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                            required
                        >
                            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="modal_description" class="block text-xxs font-black text-slate-400 uppercase mb-1">Descrição / Motivo</label>
                        <input 
                            id="modal_description" 
                            type="text" 
                            v-model="newSessionForm.description" 
                            placeholder="Ex: Auditoria de estoque agosto, Balanço geral" 
                            class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white w-full focus:outline-none focus:border-blue-500" 
                            required
                        />
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                        <button type="button" @click="isModalOpen = false" class="text-xxs text-slate-400 hover:text-white font-bold px-4 py-2">Cancelar</button>
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-xxs px-4 py-2 rounded-xl transition-all">Iniciar Sessão</button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
