<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// O controller passa a prop 'clients' para este componente.
const props = defineProps({
    clients: Object,
});
</script>

<template>
    <Head title="Clientes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestão de Clientes</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold">Lista de Clientes</h3>
                            <Link :href="route('clients.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Adicionar Cliente
                            </Link>
                        </div>

                        <!-- Mensagem de sucesso -->
                        <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                        </div>

                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Nome</th>
                                        <th scope="col" class="px-6 py-3">Contacto Principal</th>
                                        <th scope="col" class="px-6 py-3">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="client in clients.data" :key="client.id" class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ client.name }}</td>
                                        <td class="px-6 py-4">{{ client.contact_main }}</td>
                                        <td class="px-6 py-4">
                                            <Link :href="route('clients.edit', client.id)" class="font-medium text-blue-600 hover:underline mr-4">Editar</Link>
                                            <Link :href="route('clients.destroy', client.id)" method="delete" as="button" class="font-medium text-red-600 hover:underline" preserve-scroll>Apagar</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="clients.data.length === 0">
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                            Nenhum cliente encontrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação -->
                        <div class="mt-4">
                            <!-- O componente de paginação -->
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
