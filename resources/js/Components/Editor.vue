<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
    ],
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
    editorProps: {
        attributes: {
            class: 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm min-h-[150px] p-2',
        },
    },
});
</script>

<template>
    <div>
        <div v-if="editor" class="border border-gray-300 rounded-md p-1 mb-1">
            <button @click="editor.chain().focus().toggleBold().run()" :class="{ 'is-active': editor.isActive('bold') }" class="px-2 py-1 m-1 rounded hover:bg-gray-200 font-bold">B</button>
            <button @click="editor.chain().focus().toggleItalic().run()" :class="{ 'is-active': editor.isActive('italic') }" class="px-2 py-1 m-1 rounded hover:bg-gray-200 italic">I</button>
            <button @click="editor.chain().focus().toggleBulletList().run()" :class="{ 'is-active': editor.isActive('bulletList') }" class="px-2 py-1 m-1 rounded hover:bg-gray-200">Lista</button>
        </div>
        <EditorContent :editor="editor" />
    </div>
</template>

<style>
.ProseMirror {
    outline: none;
}
.is-active {
    background-color: #e2e8f0;
}
</style>
