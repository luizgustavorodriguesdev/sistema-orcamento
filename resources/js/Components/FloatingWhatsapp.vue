<script setup>
import { computed } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
        default: () => ({})
    }
});

const whatsappUrl = computed(() => {
    if (!props.settings.company_whatsapp) return null;
    const cleanNumber = props.settings.company_whatsapp.replace(/\D/g, '');
    return `https://wa.me/${cleanNumber}?text=Olá! Gostaria de fazer um orçamento de brindes personalizados.`;
});
</script>

<template>
    <div v-if="whatsappUrl" class="fixed bottom-6 right-6 z-50 flex items-center group">
        <!-- Tooltip -->
        <span class="mr-3 max-w-0 opacity-0 whitespace-nowrap overflow-hidden transition-all duration-300 group-hover:max-w-xs group-hover:opacity-100 bg-slate-900 text-white shadow-xl rounded-lg py-1.5 px-3 text-xs font-semibold select-none">
            Fale Conosco
        </span>

        <!-- Botão WhatsApp -->
        <a 
            :href="whatsappUrl" 
            target="_blank" 
            rel="noopener noreferrer"
            class="relative flex items-center justify-center w-14 h-14 bg-[#25D366] text-white rounded-full shadow-2xl hover:bg-[#20ba5a] transition-all duration-300 transform hover:scale-110"
            aria-label="Falar conosco via WhatsApp"
        >
            <!-- Efeito de Pulso -->
            <span class="absolute -inset-1 rounded-full bg-[#25D366] opacity-30 animate-pulse"></span>
            <span class="absolute inset-0 rounded-full bg-[#25D366] opacity-40 animate-ping group-hover:animate-none"></span>
            
            <!-- Ícone Font Awesome -->
            <i class="fa-brands fa-whatsapp text-3xl relative z-10"></i>
        </a>
    </div>
</template>
