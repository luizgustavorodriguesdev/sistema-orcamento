<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Recebemos as configurações salvas como uma prop.
const props = defineProps({
    settings: Object,
});

// Usamos o 'useForm' e inicializamos cada campo com o valor correspondente
// que veio do controller, ou com uma string vazia se ainda não existir.
const form = useForm({
    company_name: props.settings.company_name || '',
    company_cnpj: props.settings.company_cnpj || '',
    company_contact: props.settings.company_contact || '',
    company_email: props.settings.company_email || '',
    company_address: props.settings.company_address || '',
    company_city: props.settings.company_city || '',
    company_state: props.settings.company_state || '',
    company_zip: props.settings.company_zip || '',
    company_phone: props.settings.company_phone || '',
    company_whatsapp: props.settings.company_whatsapp || '',
    company_observations: props.settings.company_observations || '',
    social_facebook: props.settings.social_facebook || '',
    social_instagram: props.settings.social_instagram || '',
    social_linkedin: props.settings.social_linkedin || '',
    app_domain: props.settings.app_domain || '',
});

// Função para submeter o formulário.
const submit = () => {
    form.post(route('settings.store'), {
        preserveScroll: true, // Mantém a posição da página após o envio
    });
};

// --- Lógica da Mensagem de Sucesso (CORRIGIDA) ---
const page = usePage();
const showSuccessMessage = ref(false);
const successMessageText = ref('');

// 'watch' observa a propriedade 'success' de forma segura, usando o operador '?' (optional chaining).
// Isto evita o erro se 'page.props.flash' for nulo ou indefinido.
watch(() => page.props.flash?.success, (newValue) => {
    // Se um novo valor para a mensagem de sucesso aparecer...
    if (newValue) {
        successMessageText.value = newValue; // Armazenamos o texto da mensagem
        showSuccessMessage.value = true;     // Ativamos a exibição
        
        // Definimos um temporizador para esconder a mensagem após 3 segundos.
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 3000);
    }
});

</script>

<template>
    <Head title="Configurações" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-50 leading-tight">Configurações Gerais</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Mensagem de Sucesso com Transição -->
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-4"
                >
                    <div v-if="showSuccessMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline">{{ successMessageText }}</span>
                    </div>
                </transition>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit">
                        <div class="p-6 space-y-6">
                            
                            <!-- Secção de Dados da Empresa -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Dados da Empresa</h3>
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="company_name" class="block text-sm font-medium text-gray-700">Nome da Empresa</label>
                                        <input id="company_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.company_name" />
                                    </div>
                                    <div>
                                        <label for="company_cnpj" class="block text-sm font-medium text-gray-700">CNPJ</label>
                                        <input id="company_cnpj" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.company_cnpj" />
                                    </div>
                                    <div>
                                        <label for="company_email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input id="company_email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.company_email" />
                                    </div>
                                    <div>
                                        <label for="company_phone" class="block text-sm font-medium text-gray-700">Telefone</label>
                                        <input id="company_phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.company_phone" />
                                    </div>
                                    <div>
                                        <label for="company_whatsapp" class="block text-sm font-medium text-gray-700">WhatsApp (com código do país, ex: 55629...)</label>
                                        <input id="company_whatsapp" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.company_whatsapp" />
                                    </div>
                                    <div>
                                        <label for="company_contact" class="block text-sm font-medium text-gray-700">Nome do Contacto</label>
                                        <input id="company_contact" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.company_contact" />
                                    </div>
                                </div>
                            </div>

                            <!-- Secção de Endereço -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Endereço</h3>
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="md:col-span-2">
                                        <label for="company_address" class="block text-sm font-medium text-gray-700">Morada</label>
                                        <input id="company_address" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.company_address" />
                                    </div>
                                    <div>
                                        <label for="company_zip" class="block text-sm font-medium text-gray-700">CEP</label>
                                        <input id="company_zip" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.company_zip" />
                                    </div>
                                    <div>
                                        <label for="company_city" class="block text-sm font-medium text-gray-700">Cidade</label>
                                        <input id="company_city" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.company_city" />
                                    </div>
                                    <div>
                                        <label for="company_state" class="block text-sm font-medium text-gray-700">Estado</label>
                                        <input id="company_state" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.company_state" />
                                    </div>
                                </div>
                            </div>

                            <!-- Secção de Redes Sociais e Domínio -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Redes Sociais & Sistema</h3>
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="social_facebook" class="block text-sm font-medium text-gray-700">Link do Facebook</label>
                                        <input id="social_facebook" type="url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.social_facebook" />
                                    </div>
                                    <div>
                                        <label for="social_instagram" class="block text-sm font-medium text-gray-700">Link do Instagram</label>
                                        <input id="social_instagram" type="url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.social_instagram" />
                                    </div>
                                    <div>
                                        <label for="social_linkedin" class="block text-sm font-medium text-gray-700">Link do LinkedIn</label>
                                        <input id="social_linkedin" type="url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.social_linkedin" />
                                    </div>
                                    <div>
                                        <label for="app_domain" class="block text-sm font-medium text-gray-700">Domínio da Aplicação (ex: https://app.sualoja.com)</label>
                                        <input id="app_domain" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.app_domain" />
                                    </div>
                                </div>
                            </div>

                            <!-- Secção de Observações -->
                            <div>
                                <label for="company_observations" class="block text-sm font-medium text-gray-700">Observações (aparecerá no rodapé do orçamento)</label>
                                <textarea id="company_observations" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.company_observations" rows="4"></textarea>
                            </div>

                        </div>

                        <!-- Botão de Guardar -->
                        <div class="flex items-center justify-end p-6 bg-gray-50 border-t border-gray-200">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" :disabled="form.processing">
                                Guardar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
