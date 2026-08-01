<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    session: Object,
});

// Inicializa o formulário de contagens
const form = useForm({
    counts: props.session.items.map(item => ({
        product_id: item.product_id,
        name: item.product?.name || 'Excluído',
        expected_quantity: item.expected_quantity,
        counted_quantity: item.counted_quantity === null ? '' : item.counted_quantity,
    })),
});

const submitSaveDraft = () => {
    // Normaliza as contagens (transforma strings vazias em null)
    const countsToSend = form.counts.map(c => ({
        product_id: c.product_id,
        counted_quantity: c.counted_quantity === '' ? null : parseInt(c.counted_quantity),
    }));

    useForm({ counts: countsToSend }).post(route('inventory.save-counts', props.session.id), {
        onSuccess: () => {
            alert('Rascunho de contagens físicas salvo!');
        }
    });
};

const submitReconcile = () => {
    // Confirma se o usuário preencheu tudo
    const hasUncounted = form.counts.some(c => c.counted_quantity === '');
    if (hasUncounted) {
        if (!confirm('Alguns produtos não possuem contagem física informada. Eles serão desconsiderados na reconciliação. Deseja prosseguir?')) {
            return;
        }
    } else {
        if (!confirm('Atenção: Ao finalizar, o estoque oficial no depósito será substituído pelas quantidades físicas contadas. Deseja prosseguir com a reconciliação?')) {
            return;
        }
    }

    const countsToSend = form.counts.map(c => ({
        product_id: c.product_id,
        counted_quantity: c.counted_quantity === '' ? null : parseInt(c.counted_quantity),
    }));

    useForm({ counts: countsToSend }).post(route('inventory.reconcile', props.session.id), {
        onSuccess: () => {
            alert('Reconciliação concluída com sucesso! Saldos ajustados.');
        }
    });
};

const cancelSession = () => {
    if (confirm('Deseja realmente cancelar este inventário? Todas as contagens salvas serão descartadas.')) {
        useForm({}).post(route('inventory.cancel', props.session.id), {
            onSuccess: () => {
                alert('Sessão de inventário cancelada.');
            }
        });
    }
};

const getDelta = (item) => {
    if (item.counted_quantity === '') return null;
    const val = parseInt(item.counted_quantity) - item.expected_quantity;
    return val;
};
</script>

<template>
    <Head :title="`Inventário #${session.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center w-full">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Inventário #{{ session.id }}</h2>
                    <p class="text-xs text-slate-400 mt-1">Auditando Depósito: {{ session.warehouse?.name }} ({{ session.warehouse?.code }})</p>
                </div>
                <Link :href="route('inventory.index')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">
                    Voltar
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Informações e Ações do Inventário -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shadow-sm">
                    <div class="space-y-1">
                        <span class="px-2 py-0.5 rounded border text-[10px] font-black uppercase tracking-wider" :class="session.status === 'Em_Andamento' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'">
                            {{ session.status.replace('_', ' ') }}
                        </span>
                        <h3 class="text-sm font-bold text-white">{{ session.description }}</h3>
                        <p class="text-xxs text-slate-400">Criado em: {{ new Date(session.created_at).toLocaleString('pt-BR') }} | Responsável: {{ session.creator?.name }}</p>
                    </div>

                    <!-- Botões de Ações (Apenas em andamento) -->
                    <div v-if="session.status === 'Em_Andamento'" class="flex flex-wrap gap-2">
                        <button @click="cancelSession" class="bg-slate-950 hover:bg-rose-950/20 text-rose-500 border border-rose-500/20 font-bold text-xs px-4 py-2.5 rounded-xl transition-all">
                            Cancelar Inventário
                        </button>
                        <button @click="submitSaveDraft" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs px-4 py-2.5 rounded-xl transition-all">
                            Salvar Rascunho
                        </button>
                        <button @click="submitReconcile" class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition-all shadow-lg shadow-purple-600/10">
                            Finalizar & Reconciliar
                        </button>
                    </div>
                </div>

                <!-- Tabela de Contagem Física -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-5 border-b border-slate-800">
                        <h3 class="text-sm font-black text-white">Planilha de Auditoria e Contagem</h3>
                    </div>

                    <table class="w-full text-xs text-left text-slate-300">
                        <thead class="text-xxs text-slate-400 uppercase bg-slate-950 border-b border-slate-800">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 font-black">Produto</th>
                                <th scope="col" class="px-6 py-3.5 font-black text-center">Teórico no Sistema</th>
                                <th scope="col" class="px-6 py-3.5 font-black text-center w-40">Contagem Física</th>
                                <th scope="col" class="px-6 py-3.5 font-black text-center">Diferença (Delta)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se o inventário está concluído, mostra os registros salvos em banco -->
                            <template v-if="session.status !== 'Em_Andamento'">
                                <tr v-for="item in session.items" :key="item.id" class="border-b border-slate-800/50 hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4 font-bold text-white">{{ item.product?.name || 'Excluído' }}</td>
                                    <td class="px-6 py-4 text-center font-mono text-slate-400">{{ item.expected_quantity }}</td>
                                    <td class="px-6 py-4 text-center font-mono text-white font-bold">{{ item.counted_quantity ?? '-' }}</td>
                                    <td class="px-6 py-4 text-center font-mono font-bold" :class="item.adjusted_quantity > 0 ? 'text-emerald-400' : (item.adjusted_quantity < 0 ? 'text-rose-400' : 'text-slate-500')">
                                        {{ item.adjusted_quantity > 0 ? '+' : '' }}{{ item.adjusted_quantity ?? '-' }}
                                    </td>
                                </tr>
                            </template>

                            <!-- Se está em andamento, mostra os inputs interativos de contagem -->
                            <template v-else>
                                <tr v-for="item in form.counts" :key="item.product_id" class="border-b border-slate-800/50 hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4 font-bold text-white">{{ item.name }}</td>
                                    <td class="px-6 py-4 text-center font-mono text-slate-400">{{ item.expected_quantity }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <input 
                                            type="number" 
                                            min="0" 
                                            placeholder="Contar..."
                                            v-model="item.counted_quantity" 
                                            class="bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1 text-center font-mono text-xs w-28 text-white focus:outline-none focus:border-blue-500" 
                                        />
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono font-bold">
                                        <span v-if="getDelta(item) === null" class="text-slate-600 font-light">-</span>
                                        <span v-else :class="getDelta(item) > 0 ? 'text-emerald-400' : (getDelta(item) < 0 ? 'text-rose-400' : 'text-slate-400')">
                                            {{ getDelta(item) > 0 ? '+' : '' }}{{ getDelta(item) }}
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
