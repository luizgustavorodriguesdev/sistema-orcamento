<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Recebemos as configurações salvas como uma prop.
const props = defineProps({
    settings: Object,
});

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
    social_youtube: props.settings.social_youtube || '',
    seo_meta_title: props.settings.seo_meta_title || '',
    seo_meta_description: props.settings.seo_meta_description || '',
    seo_meta_keywords: props.settings.seo_meta_keywords || '',
    app_domain: props.settings.app_domain || '',

    // Vantagens da Home (1 a 4)
    advantage_1_icon: props.settings.advantage_1_icon || '',
    advantage_1_title: props.settings.advantage_1_title || '',
    advantage_1_subtitle: props.settings.advantage_1_subtitle || '',
    advantage_2_icon: props.settings.advantage_2_icon || '',
    advantage_2_title: props.settings.advantage_2_title || '',
    advantage_2_subtitle: props.settings.advantage_2_subtitle || '',
    advantage_3_icon: props.settings.advantage_3_icon || '',
    advantage_3_title: props.settings.advantage_3_title || '',
    advantage_3_subtitle: props.settings.advantage_3_subtitle || '',
    advantage_4_icon: props.settings.advantage_4_icon || '',
    advantage_4_title: props.settings.advantage_4_title || '',
    advantage_4_subtitle: props.settings.advantage_4_subtitle || '',

    // Banners Promocionais
    promo_banner_1_link: props.settings.promo_banner_1_link || '',
    promo_banner_2_link: props.settings.promo_banner_2_link || '',
    promo_banner_1_image: null,
    promo_banner_2_image: null,
});

const banner1Preview = ref(null);
const banner2Preview = ref(null);

const handleBanner1Change = (e) => {
    const file = e.target.files[0];
    form.promo_banner_1_image = file;
    if (file) {
        banner1Preview.value = URL.createObjectURL(file);
    } else {
        banner1Preview.value = null;
    }
};

const handleBanner2Change = (e) => {
    const file = e.target.files[0];
    form.promo_banner_2_image = file;
    if (file) {
        banner2Preview.value = URL.createObjectURL(file);
    } else {
        banner2Preview.value = null;
    }
};

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
                                        <label for="social_youtube" class="block text-sm font-medium text-gray-700">Link do YouTube</label>
                                        <input id="social_youtube" type="url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.social_youtube" />
                                    </div>
                                    <div>
                                        <label for="app_domain" class="block text-sm font-medium text-gray-700">Domínio da Aplicação (ex: https://app.sualoja.com)</label>
                                        <input id="app_domain" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.app_domain" />
                                    </div>
                                </div>
                            </div>

                            <!-- Secção de SEO Global -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Configurações Globais de SEO</h3>
                                <p class="text-xs text-gray-500 mb-4">Estas configurações serão usadas como fallback caso páginas, produtos ou categorias não tenham SEO customizado.</p>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="seo_meta_title" class="block text-sm font-medium text-gray-700">Título Padrão do Site (Meta Title)</label>
                                        <input id="seo_meta_title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.seo_meta_title" placeholder="Ex: GiftJoy | Brindes Personalizados Premium" />
                                    </div>
                                    <div>
                                        <label for="seo_meta_description" class="block text-sm font-medium text-gray-700">Meta Descrição Padrão</label>
                                        <textarea id="seo_meta_description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.seo_meta_description" rows="3" placeholder="Ex: Encontre canecas, copos térmicos e kits corporativos personalizados de alta durabilidade..."></textarea>
                                    </div>
                                    <div>
                                        <label for="seo_meta_keywords" class="block text-sm font-medium text-gray-700">Palavras-chave Padrão (separadas por vírgula)</label>
                                        <input id="seo_meta_keywords" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" v-model="form.seo_meta_keywords" placeholder="Ex: brindes, personalizados, corporativo, caneca, copo termico" />
                                    </div>
                                </div>
                            </div>

                            <!-- Secção de Vantagens e Banners da Home -->
                            <div class="pt-6 border-t border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Vantagens & Chamadas da Home</h3>
                                <p class="text-xs text-gray-500 mb-6">Configure a exibição das 4 vantagens (ícone, título e subtítulo) e das 2 chamadas promocionais (imagens e links) na página inicial.</p>
                                
                                <!-- 4 Blocos de Vantagens -->
                                <div class="space-y-6">
                                    <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Os 4 Blocos de Vantagens (Abaixo do Banner)</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Bloco 1 -->
                                        <div class="p-4 border rounded-xl bg-slate-50/50 space-y-3">
                                            <span class="block text-xs font-bold text-blue-600 uppercase">Bloco 1</span>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Ícone (Font Awesome Class)</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_1_icon" placeholder="Ex: fa-solid fa-truck" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Título</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_1_title" placeholder="Ex: Do Oiapoque ao Chuí" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Subtítulo</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_1_subtitle" placeholder="Ex: Entregas em Todo Brasil" />
                                            </div>
                                        </div>

                                        <!-- Bloco 2 -->
                                        <div class="p-4 border rounded-xl bg-slate-50/50 space-y-3">
                                            <span class="block text-xs font-bold text-blue-600 uppercase">Bloco 2</span>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Ícone (Font Awesome Class)</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_2_icon" placeholder="Ex: fa-solid fa-credit-card" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Título</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_2_title" placeholder="Ex: Parcelamento" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Subtítulo</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_2_subtitle" placeholder="Ex: Em até 3x sem juros" />
                                            </div>
                                        </div>

                                        <!-- Bloco 3 -->
                                        <div class="p-4 border rounded-xl bg-slate-50/50 space-y-3">
                                            <span class="block text-xs font-bold text-blue-600 uppercase">Bloco 3</span>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Ícone (Font Awesome Class)</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_3_icon" placeholder="Ex: fa-solid fa-gem" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Título</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_3_title" placeholder="Ex: Ganhe Desconto" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Subtítulo</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_3_subtitle" placeholder="Ex: Pagando com PIX" />
                                            </div>
                                        </div>

                                        <!-- Bloco 4 -->
                                        <div class="p-4 border rounded-xl bg-slate-50/50 space-y-3">
                                            <span class="block text-xs font-bold text-blue-600 uppercase">Bloco 4</span>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Ícone (Font Awesome Class)</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_4_icon" placeholder="Ex: fa-solid fa-shield-halved" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Título</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_4_title" placeholder="Ex: Segurança" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Subtítulo</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.advantage_4_subtitle" placeholder="Ex: Loja com SSL de proteção" />
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xxs text-gray-400 mt-1">Dica de ícones úteis: <code class="bg-gray-100 px-1 py-0.5 rounded font-mono">fa-solid fa-truck</code> (Caminhão), <code class="bg-gray-100 px-1 py-0.5 rounded font-mono">fa-solid fa-credit-card</code> (Cartão), <code class="bg-gray-100 px-1 py-0.5 rounded font-mono">fa-solid fa-gem</code> (Desconto), <code class="bg-gray-100 px-1 py-0.5 rounded font-mono">fa-solid fa-shield-halved</code> (Segurança).</p>
                                </div>

                                <!-- 2 Chamadas Promocionais (Banners) -->
                                <div class="mt-8 space-y-6 pt-6 border-t">
                                    <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider">As 2 Chamadas Promocionais (Banners da Home)</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <!-- Chamada 1 -->
                                        <div class="space-y-4 p-4 border rounded-xl bg-slate-50/50">
                                            <span class="block text-xs font-bold text-rose-600 uppercase">Banner Promocional 1 (Esquerda)</span>
                                            
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Nova Imagem (Formatos aceitos: JPG, PNG, WEBP)</label>
                                                <input type="file" class="mt-1 block w-full text-xs border border-gray-300 bg-white p-1 rounded" @change="handleBanner1Change" />
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div v-if="settings.promo_banner_1_image_path" class="border rounded p-1.5 bg-white text-center">
                                                    <span class="block text-[10px] font-semibold text-gray-400 mb-1">Atual:</span>
                                                    <img :src="`/storage/${settings.promo_banner_1_image_path}`" alt="Banner 1" class="max-h-20 object-contain mx-auto rounded" />
                                                </div>
                                                <div v-if="banner1Preview" class="border rounded p-1.5 bg-white text-center">
                                                    <span class="block text-[10px] font-semibold text-gray-400 mb-1">Selecionada:</span>
                                                    <img :src="banner1Preview" alt="Preview Banner 1" class="max-h-20 object-contain mx-auto rounded" />
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Link de Direcionamento</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.promo_banner_1_link" placeholder="Ex: /category/canecas-musica" />
                                            </div>
                                        </div>

                                        <!-- Chamada 2 -->
                                        <div class="space-y-4 p-4 border rounded-xl bg-slate-50/50">
                                            <span class="block text-xs font-bold text-rose-600 uppercase">Banner Promocional 2 (Direita)</span>
                                            
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Nova Imagem (Formatos aceitos: JPG, PNG, WEBP)</label>
                                                <input type="file" class="mt-1 block w-full text-xs border border-gray-300 bg-white p-1 rounded" @change="handleBanner2Change" />
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div v-if="settings.promo_banner_2_image_path" class="border rounded p-1.5 bg-white text-center">
                                                    <span class="block text-[10px] font-semibold text-gray-400 mb-1">Atual:</span>
                                                    <img :src="`/storage/${settings.promo_banner_2_image_path}`" alt="Banner 2" class="max-h-20 object-contain mx-auto rounded" />
                                                </div>
                                                <div v-if="banner2Preview" class="border rounded p-1.5 bg-white text-center">
                                                    <span class="block text-[10px] font-semibold text-gray-400 mb-1">Selecionada:</span>
                                                    <img :src="banner2Preview" alt="Preview Banner 2" class="max-h-20 object-contain mx-auto rounded" />
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700">Link de Direcionamento</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-xs" v-model="form.promo_banner_2_link" placeholder="Ex: /category/canecas-foto" />
                                            </div>
                                        </div>
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
