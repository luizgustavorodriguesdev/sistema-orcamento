<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    pages: Object,
});
</script>

<template>
    <Head title="Páginas Institucionais" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestão de Páginas Institucionais</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold">Páginas</h3>
                            <Link :href="route('pages.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                                Criar Nova Página
                            </Link>
                        </div>

                        <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                        </div>

                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                     <tr>
                                         <th scope="col" class="px-6 py-3">Título</th>
                                         <th scope="col" class="px-6 py-3">Slug (Link)</th>
                                         <th scope="col" class="px-6 py-3">Status</th>
                                         <th scope="col" class="px-6 py-3">Ações</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <tr v-for="page in pages.data" :key="page.id" class="bg-white border-b hover:bg-gray-50">
                                         <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">{{ page.title }}</td>
                                         <td class="px-6 py-4">
                                             <a :href="route('storefront.page.show', { page: page.slug })" target="_blank" class="text-blue-500 hover:underline">
                                                 /{{ page.slug }}
                                             </a>
                                         </td>
                                         <td class="px-6 py-4">
                                             <span v-if="page.is_active" class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-bold shadow-sm">Ativa</span>
                                             <span v-else class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs shadow-sm">Rascunho / Inativa</span>
                                         </td>
                                         <td class="px-6 py-4">
                                             <Link :href="route('pages.edit', page.id)" class="font-medium text-blue-600 hover:underline mr-4">Editar</Link>
                                             <Link :href="route('pages.destroy', page.id)" method="delete" as="button" class="font-medium text-red-600 hover:underline" preserve-scroll>Apagar</Link>
                                         </td>
                                     </tr>
                                     <tr v-if="pages.data.length === 0">
                                         <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                             Nenhuma página institucional criada.
                                         </td>
                                     </tr>
                                 </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
