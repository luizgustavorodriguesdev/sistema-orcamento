<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    banner: Object,
});

const form = useForm({
    _method: 'PUT',
    title: props.banner.title || '',
    subtitle: props.banner.subtitle || '',
    link: props.banner.link || '',
    image: null,
    order: props.banner.order ?? 0,
    is_active: props.banner.is_active ?? true,
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
    form.post(route('banners.update', props.banner.id));
};
</script>

<template>
    <Head title="Editar Banner" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Banner</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold">Edite as informações do Banner</h3>
                            <Link :href="route('banners.index')" class="text-sm font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                Voltar
                            </Link>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Imagem Atual vs Nova -->
                            <div>
                                <label for="image" class="block font-medium text-sm text-gray-700">Substituir Imagem (Opcional - Deixe vazio para manter a atual)</label>
                                <input id="image" type="file" class="mt-1 block w-full border border-gray-300 rounded-xl p-2 bg-white text-sm" @change="handleImageChange" />
                                <p v-if="form.errors.image" class="text-sm text-red-600 mt-2">{{ form.errors.image }}</p>
                                
                                <!-- Preview da Imagem Atual / Nova -->
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="border rounded-xl p-2 bg-slate-50">
                                        <span class="block text-xs font-semibold text-gray-400 mb-2">Imagem Atual:</span>
                                        <img :src="`/storage/${props.banner.image_path}`" alt="Banner Atual" class="w-full max-h-40 object-cover rounded-lg" />
                                    </div>
                                    <div v-if="imagePreview" class="border rounded-xl p-2 bg-slate-50">
                                        <span class="block text-xs font-semibold text-gray-400 mb-2">Nova Imagem Selecionada:</span>
                                        <img :src="imagePreview" alt="Nova Imagem Preview" class="w-full max-h-40 object-cover rounded-lg" />
                                    </div>
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
                                    {{ form.processing ? 'A Guardar...' : 'Atualizar Banner' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
