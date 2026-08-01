<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    popups: Object,
});

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Popups Promocionais" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Popups Promocionais</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold">Lista de Popups</h3>
                            <Link :href="route('promotion-popups.create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl shadow transition-colors text-sm">
                                Adicionar Popup
                            </Link>
                        </div>

                        <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
                            <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                        </div>

                        <div class="relative overflow-x-auto shadow-md sm:rounded-2xl">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                     <tr>
                                         <th scope="col" class="px-6 py-3">Miniatura</th>
                                         <th scope="col" class="px-6 py-3">Título</th>
                                         <th scope="col" class="px-6 py-3">Início</th>
                                         <th scope="col" class="px-6 py-3">Fim</th>
                                         <th scope="col" class="px-6 py-3">Contador</th>
                                         <th scope="col" class="px-6 py-3">Status</th>
                                         <th scope="col" class="px-6 py-3">Ações</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <tr v-for="popup in popups.data" :key="popup.id" class="bg-white border-b hover:bg-gray-50">
                                         <td class="px-6 py-4">
                                             <img v-if="popup.image_path" :src="`/storage/${popup.image_path}`" alt="Popup Preview" class="w-16 h-16 object-cover rounded-lg shadow-sm border" />
                                             <span v-else class="text-xs text-gray-400">Sem imagem</span>
                                         </td>
                                         <td class="px-6 py-4 font-semibold text-gray-900">{{ popup.title }}</td>
                                         <td class="px-6 py-4">{{ formatDate(popup.start_date) }}</td>
                                         <td class="px-6 py-4">{{ formatDate(popup.end_date) }}</td>
                                         <td class="px-6 py-4">
                                             <div v-if="popup.has_countdown" class="flex flex-col">
                                                 <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-xxs font-bold self-start mb-1">Sim</span>
                                                 <span class="text-xxs text-gray-400">{{ formatDate(popup.countdown_end) }}</span>
                                             </div>
                                             <span v-else class="text-xs text-gray-400">Não</span>
                                         </td>
                                         <td class="px-6 py-4">
                                             <span v-if="popup.is_active" class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-bold shadow-sm">Ativo</span>
                                             <span v-else class="px-2 py-1 rounded bg-red-100 text-red-800 text-xs font-bold shadow-sm">Inativo</span>
                                         </td>
                                         <td class="px-6 py-4">
                                            <Link :href="route('promotion-popups.edit', popup.id)" class="font-medium text-blue-600 hover:underline mr-4">Editar</Link>
                                            <Link :href="route('promotion-popups.destroy', popup.id)" method="delete" as="button" class="font-medium text-red-600 hover:underline" preserve-scroll>Excluir</Link>
                                         </td>
                                     </tr>
                                     <tr v-if="popups.data.length === 0">
                                         <td colspan="7" class="px-6 py-8 text-center text-gray-500 font-light">
                                             Nenhum popup promocional cadastrado.
                                         </td>
                                     </tr>
                                 </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação -->
                        <div v-if="popups.links.length > 3" class="mt-6 flex justify-end space-x-1">
                            <Link
                                v-for="(link, index) in popups.links"
                                :key="index"
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                                :class="{
                                    'bg-blue-600 text-white shadow': link.active,
                                    'bg-white text-gray-600 border hover:bg-gray-50': !link.active && link.url,
                                    'bg-gray-100 text-gray-300 cursor-not-allowed border': !link.url
                                }"
                            />
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
