<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    button_text: '',
    button_link: '',
    has_countdown: false,
    countdown_end: '',
    is_active: true,
    image: null,
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
    form.post(route('promotion-popups.store'));
};
</script>

<template>
    <Head title="Adicionar Popup" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Criar Popup Promocional</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold">Preencha os dados do Popup</h3>
                            <Link :href="route('promotion-popups.index')" class="text-sm font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                Voltar
                            </Link>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6" enctype="multipart/form-data">
                            <!-- Título -->
                            <div>
                                <label for="title" class="block font-medium text-sm text-gray-700">Título da Campanha</label>
                                <input id="title" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" v-model="form.title" placeholder="Ex: Black Friday - 20% OFF!" required />
                                <p v-if="form.errors.title" class="text-sm text-red-600 mt-2">{{ form.errors.title }}</p>
                            </div>

                            <!-- Descrição -->
                            <div>
                                <label for="description" class="block font-medium text-sm text-gray-700">Descrição / Mensagem (Opcional)</label>
                                <textarea id="description" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" v-model="form.description" placeholder="Ex: Use o cupom BRINDES20 e aproveite frete grátis em toda a loja."></textarea>
                                <p v-if="form.errors.description" class="text-sm text-red-600 mt-2">{{ form.errors.description }}</p>
                            </div>

                            <!-- Imagem -->
                            <div>
                                <label for="image" class="block font-medium text-sm text-gray-700">Imagem do Popup (Recomendado: Formato Retangular/Quadrado)</label>
                                <input id="image" type="file" class="mt-1 block w-full border border-gray-300 rounded-xl p-2 bg-white text-sm" @change="handleImageChange" />
                                <p v-if="form.errors.image" class="text-sm text-red-600 mt-2">{{ form.errors.image }}</p>
                                
                                <!-- Preview da Imagem -->
                                <div v-if="imagePreview" class="mt-4 border rounded-xl overflow-hidden shadow-sm bg-slate-50 p-2">
                                    <span class="block text-xs font-semibold text-gray-400 mb-2">Pré-visualização:</span>
                                    <img :src="imagePreview" alt="Preview" class="w-full max-h-60 object-contain rounded-lg" />
                                </div>
                            </div>

                            <!-- Datas de Agendamento -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="start_date" class="block font-medium text-sm text-gray-700">Data e Hora de Início</label>
                                    <input id="start_date" type="datetime-local" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" v-model="form.start_date" required />
                                    <p v-if="form.errors.start_date" class="text-sm text-red-600 mt-2">{{ form.errors.start_date }}</p>
                                </div>

                                <div>
                                    <label for="end_date" class="block font-medium text-sm text-gray-700">Data e Hora de Fim</label>
                                    <input id="end_date" type="datetime-local" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" v-model="form.end_date" required />
                                    <p v-if="form.errors.end_date" class="text-sm text-red-600 mt-2">{{ form.errors.end_date }}</p>
                                </div>
                            </div>

                            <!-- Botão de Ação -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="button_text" class="block font-medium text-sm text-gray-700">Texto do Botão CTA (Opcional)</label>
                                    <input id="button_text" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" v-model="form.button_text" placeholder="Ex: Conferir Promoção" />
                                    <p v-if="form.errors.button_text" class="text-sm text-red-600 mt-2">{{ form.errors.button_text }}</p>
                                </div>

                                <div>
                                    <label for="button_link" class="block font-medium text-sm text-gray-700">Link do Botão (Opcional)</label>
                                    <input id="button_link" type="text" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" v-model="form.button_link" placeholder="Ex: /category/canecas ou https://wa.me/..." />
                                    <p v-if="form.errors.button_link" class="text-sm text-red-600 mt-2">{{ form.errors.button_link }}</p>
                                </div>
                            </div>

                            <!-- Configuração de Contador Regressivo -->
                            <div class="p-4 border border-amber-200 rounded-xl bg-amber-50/50 space-y-4">
                                <div class="flex items-center">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" v-model="form.has_countdown" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                        <span class="ms-2 text-sm font-bold text-gray-700">Ativar Contador Regressivo (Cronômetro)</span>
                                    </label>
                                </div>

                                <div v-if="form.has_countdown" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="countdown_end" class="block font-medium text-sm text-gray-700">Encerrar Contador Em</label>
                                        <input id="countdown_end" type="datetime-local" class="mt-1 block w-full rounded-xl border-gray-300 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" v-model="form.countdown_end" :required="form.has_countdown" />
                                        <p v-if="form.errors.countdown_end" class="text-sm text-red-600 mt-2">{{ form.errors.countdown_end }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Status de Ativação -->
                            <div class="flex items-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                    <span class="ms-2 text-sm text-gray-600">Popup Ativo (estará elegível para exibição conforme as datas)</span>
                                </label>
                            </div>

                            <!-- Botões de Ação do Formulário -->
                            <div class="flex items-center justify-end space-x-4 bg-gray-50 p-4 -mx-6 -mb-6 rounded-b-lg border-t">
                                <Link :href="route('promotion-popups.index')" class="text-sm text-gray-600 hover:text-gray-900 font-semibold">Cancelar</Link>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow transition-colors text-sm" :disabled="form.processing">
                                    Salvar Popup
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
