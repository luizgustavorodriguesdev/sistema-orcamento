<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    title: '',
    subtitle: '',
    link: '',
    image: null,
    order: 0,
    is_active: true,
});

const imagePreview = ref(null);

const handleImageChange = (event) => {
    const file = event.target.files[0];
    form.image = file;
    if (file) {
        imagePreview.value = URL.createObjectURL(file);
    } else {
        imagePreview.value = null;
    }
};

const submit = () => {
    form.post(route('banners.store'));
};
</script>

<template>
    <Head title="Adicionar Banner" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Adicionar Novo Banner</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold">Preencha os dados do Banner</h3>
                            <Link :href="route('banners.index')" class="text-sm font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                Voltar
                            </Link>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Imagem -->
                            <div>
                                <label for="image" class="block font-medium text-sm text-gray-700">Imagem de Fundo (Recomendado: 1920x600 ou similar)</label>
                                <input id="image" type="file" class="mt-1 block w-full border border-gray-300 rounded-xl p-2 bg-white text-sm" @change="handleImageChange" required />
                                <p v-if="form.errors.image" class="text-sm text-red-600 mt-2">{{ form.errors.image }}</p>
                                
                                <!-- Preview da Imagem -->
                                <div v-if="imagePreview" class="mt-4 border rounded-xl overflow-hidden shadow-sm bg-slate-50 p-2">
                                    <span class="block text-xs font-semibold text-gray-400 mb-2">Pré-visualização:</span>
                                    <img :src="imagePreview" alt="Preview" class="w-full max-h-60 object-cover rounded-lg" />
                                </div>
                            </div>

                            <!-- Título -->
                            <div>
                                <label for="title" class="block font-medium text-sm text-gray-700">Título (Opcional)</label>
                                <input id="title" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" v-model="form.title" placeholder="Ex: Copos Térmicos Personalizados" />
                                <p v-if="form.errors.title" class="text-sm text-red-600 mt-2">{{ form.errors.title }}</p>
                            </div>

                            <!-- Subtítulo -->
                            <div>
                                <label for="subtitle" class="block font-medium text-sm text-gray-700">Subtítulo / Descrição (Opcional)</label>
                                <input id="subtitle" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" v-model="form.subtitle" placeholder="Ex: Conserve sua bebida na temperatura ideal com a sua marca" />
                                <p v-if="form.errors.subtitle" class="text-sm text-red-600 mt-2">{{ form.errors.subtitle }}</p>
                            </div>

                            <!-- Link do Botão -->
                            <div>
                                <label for="link" class="block font-medium text-sm text-gray-700">Link do Botão (Opcional)</label>
                                <input id="link" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" v-model="form.link" placeholder="Ex: /category/copos-termicos ou https://link-externo.com" />
                                <p v-if="form.errors.link" class="text-sm text-red-600 mt-2">{{ form.errors.link }}</p>
                            </div>

                            <!-- Ordem e Status -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="order" class="block font-medium text-sm text-gray-700">Ordem de Exibição</label>
                                    <input id="order" type="number" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" v-model="form.order" min="0" />
                                    <p v-if="form.errors.order" class="text-sm text-red-600 mt-2">{{ form.errors.order }}</p>
                                </div>

                                <div class="flex items-center pt-6">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                        <span class="ms-2 text-sm text-gray-600">Banner Ativo (será exibido no carrossel)</span>
                                    </label>
                                    <p v-if="form.errors.is_active" class="text-sm text-red-600 mt-2">{{ form.errors.is_active }}</p>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                                <Link :href="route('banners.index')" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2.5 px-4 rounded-xl text-sm transition-colors">
                                    Cancelar
                                </Link>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-xl text-sm shadow transition-colors" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ form.processing ? 'A Guardar...' : 'Salvar Banner' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
