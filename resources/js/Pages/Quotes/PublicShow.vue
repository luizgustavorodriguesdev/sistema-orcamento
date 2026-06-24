<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
// O controller passa a prop 'quote' com todos os dados necessários.
const props = defineProps({
    quote: Object,
    settings: Object,
});

// Funções de formatação que já conhecemos.
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('pt-BR', options);
};

// Monta a mensagem para o WhatsApp.
const getWhatsAppMessage = () => {
    const vendorName = props.quote.user ? props.quote.user.name : 'nosso vendedor';
    const message = `Olá, ${vendorName}! Gostaria de falar sobre o orçamento Nº ${props.quote.id}.`;
    return encodeURIComponent(message);
};



// Aqui você deve colocar o número de WhatsApp da sua loja.
const storePhoneNumber = '5562984312949'; // Exemplo: 55 (Brasil) + 62 (Goiás) + 999999999 (Número)
//const whatsappLink = `https://wa.me/${storePhoneNumber}?text=${getWhatsAppMessage()}`;
// O link do WhatsApp agora é dinâmico, usando o número das configurações.
const whatsappLink = computed(() => {
    const vendorName = props.quote.user ? props.quote.user.name : 'nosso vendedor';
    const message = `Olá, ${vendorName}! Gostaria de falar sobre o orçamento Nº ${props.quote.id}.`;
    const encodedMessage = encodeURIComponent(message);
    // Usamos o número de WhatsApp das configurações, com um fallback.
    const phoneNumber = props.settings.company_whatsapp || '5562999999999';
    return `https://wa.me/${phoneNumber}?text=${encodedMessage}`;
});

</script>

<template>
    <Head :title="'Orçamento Nº ' + quote.id" />

    <div class="bg-gray-100 min-h-screen font-sans">
        <div class="container mx-auto p-4 sm:p-8">
            <div class="bg-white p-6 sm:p-10 rounded-lg shadow-lg max-w-4xl mx-auto">

                <!-- Cabeçalho -->
                <header class="flex justify-between items-start border-b pb-6 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Orçamento</h1>
                        <p class="text-gray-500">Nº: {{ String(quote.id).padStart(4, '0') }}</p>
                        <p class="text-gray-500">Data: {{ formatDate(quote.created_at) }}</p>
                    </div>
                    <div class="text-right">
                        <h2 class="text-2xl font-semibold text-blue-600">{{ settings.company_name || 'Sua Loja de Brindes' }}</h2>
                        <p class="text-gray-600">{{ settings.company_address || 'Rua Exemplo, 123' }}</p>
                        <p class="text-gray-600">{{ settings.company_email || 'contato@sualoja.com' }}</p>
                    </div>
                </header>

                <!-- Informações do Cliente e Vendedor -->
                <section class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Cliente:</h3>
                        <p class="text-gray-800 font-medium">{{ quote.client_name }}</p>
                        <p class="text-gray-600">E-mail: {{ quote.client_contact }}</p>
                        <p v-if="quote.customer_phone" class="text-gray-600">Telefone: {{ quote.customer_phone }}</p>
                        
                        <div v-if="quote.cep" class="mt-4 p-3 bg-gray-50 border rounded-lg text-sm text-gray-600">
                            <p class="font-semibold text-gray-700 mb-1">Endereço:</p>
                            <p>{{ quote.address_street }}</p>
                            <p>{{ quote.address_neighborhood }}</p>
                            <p>{{ quote.address_city }} - {{ quote.address_state }}</p>
                            <p>CEP: {{ quote.cep }}</p>
                        </div>
                    </div>
                    <div class="md:text-right">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Atendido por:</h3>
                        <p class="text-gray-800 font-medium">{{ quote.user ? quote.user.name : 'Equipe de Vendas' }}</p>
                        <p class="text-gray-600">{{ quote.user ? quote.user.email : '' }}</p>
                    </div>
                </section>

                <!-- Tabela de Itens -->
                <section class="mb-8">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 font-semibold text-gray-600">Produto</th>
                                <th class="p-3 text-center font-semibold text-gray-600">Qtd.</th>
                                <th class="p-3 text-right font-semibold text-gray-600">Preço Unit.</th>
                                <th class="p-3 text-right font-semibold text-gray-600">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in quote.products" :key="product.id" class="border-b">
                                <td class="p-3">{{ product.name }}</td>
                                <td class="p-3 text-center">{{ product.pivot.quantity }}</td>
                                <td class="p-3 text-right">{{ formatCurrency(product.pivot.unit_price) }}</td>
                                <td class="p-3 text-right">{{ formatCurrency(product.pivot.quantity * product.pivot.unit_price) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Total e Informações Adicionais -->
                <section class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                    <div class="space-y-4">
                        <div v-if="quote.payment_terms">
                            <h4 class="font-semibold text-gray-700">Condições de Pagamento:</h4>
                            <p class="text-gray-600 whitespace-pre-wrap">{{ quote.payment_terms }}</p>
                        </div>
                        <div v-if="quote.delivery_info">
                            <h4 class="font-semibold text-gray-700">Prazo de Entrega:</h4>
                            <p class="text-gray-600 whitespace-pre-wrap">{{ quote.delivery_info }}</p>
                        </div>
                        <div v-if="quote.customization_details" class="p-4 bg-blue-50 border border-blue-100 rounded-lg">
                            <h4 class="font-semibold text-blue-800 mb-1 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                Personalização Solicitada:
                            </h4>
                            <p class="text-blue-900 text-sm whitespace-pre-wrap">{{ quote.customization_details }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg text-gray-600">Total do Orçamento:</p>
                        <p class="text-4xl font-bold text-gray-900 mb-6">{{ formatCurrency(quote.total_amount) }}</p>
                        
                        <a :href="whatsappLink" target="_blank" class="inline-block bg-green-500 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600 transition-colors">
                            Aprovar via WhatsApp
                        </a>
                    </div>
                </section>
                

                <!-- Rodapé com observações dinâmicas -->
                <footer class="text-center text-gray-500 border-t pt-6 mt-8">
                    <p v-if="settings.company_observations">{{ settings.company_observations }}</p>
                    <p v-else>Obrigado pela sua preferência!</p>
                </footer>

            </div>
        </div>
    </div>
</template>
