<script setup>
import { EditorContent, useEditor } from '@tiptap/vue-3';
import Color from '@tiptap/extension-color';
import TextStyle from '@tiptap/extension-text-style';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import {
    BoldIcon,
    ItalicIcon,
    PaperClipIcon,
    UnderlineIcon,
} from '@heroicons/vue/24/outline';
import { onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    readonly: { type: Boolean, default: false },
    placeholder: { type: String, default: 'Detalhes, observações e contexto da tarefa…' },
    showAttachment: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'attach']);

const fileInputRef = ref(null);
const textColor = ref('#1e293b');

function toEditorHtml(value) {
    const text = String(value || '').trim();
    if (!text) return '<p></p>';
    if (text.startsWith('<')) return text;
    const escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    return `<p>${escaped.replace(/\n/g, '<br>')}</p>`;
}

function fromEditorHtml(html) {
    const value = String(html || '').trim();
    if (!value || value === '<p></p>') return '';
    return value;
}

const editor = useEditor({
    editable: !props.readonly,
    extensions: [
        StarterKit.configure({ heading: false }),
        Underline,
        TextStyle,
        Color,
    ],
    content: toEditorHtml(props.modelValue),
    editorProps: {
        attributes: {
            class:
                'min-h-[160px] max-h-[320px] overflow-y-auto px-3 py-2 text-sm text-slate-800 focus:outline-none prose prose-sm max-w-none [&_p]:my-1',
            'data-placeholder': props.placeholder,
        },
    },
    onUpdate: ({ editor: ed }) => {
        emit('update:modelValue', fromEditorHtml(ed.getHTML()));
    },
});

watch(
    () => props.modelValue,
    (value) => {
        const current = fromEditorHtml(editor.value?.getHTML());
        const next = String(value || '');
        if (current !== next) {
            editor.value?.commands.setContent(toEditorHtml(next), false);
        }
    },
);

watch(
    () => props.readonly,
    (readOnly) => {
        editor.value?.setEditable(!readOnly);
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

function toggleBold() {
    editor.value?.chain().focus().toggleBold().run();
}

function toggleItalic() {
    editor.value?.chain().focus().toggleItalic().run();
}

function toggleUnderline() {
    editor.value?.chain().focus().toggleUnderline().run();
}

function applyColor(color) {
    textColor.value = color;
    editor.value?.chain().focus().setColor(color).run();
}

function triggerAttachment() {
    fileInputRef.value?.click();
}

function onFileChange(event) {
    emit('attach', event);
}
</script>

<template>
    <div v-if="readonly" class="mt-1 rounded-md border border-slate-200 bg-slate-50/50 px-3 py-2">
        <div
            v-if="modelValue?.trim()"
            class="prose prose-sm max-w-none text-slate-700 [&_p]:my-1"
            v-html="toEditorHtml(modelValue)"
        />
        <p v-else class="text-sm text-slate-500">—</p>
    </div>
    <div v-else class="mt-1 overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="flex flex-wrap items-center gap-1 border-b border-slate-200 bg-slate-50/80 px-2 py-1.5">
            <button
                type="button"
                class="rounded p-1.5 text-slate-600 transition hover:bg-white hover:text-slate-900"
                :class="{ 'bg-white text-talents-700 ring-1 ring-slate-200': editor?.isActive('bold') }"
                title="Negrito"
                @click="toggleBold"
            >
                <BoldIcon class="h-4 w-4" aria-hidden="true" />
            </button>
            <button
                type="button"
                class="rounded p-1.5 text-slate-600 transition hover:bg-white hover:text-slate-900"
                :class="{ 'bg-white text-talents-700 ring-1 ring-slate-200': editor?.isActive('italic') }"
                title="Itálico"
                @click="toggleItalic"
            >
                <ItalicIcon class="h-4 w-4" aria-hidden="true" />
            </button>
            <button
                type="button"
                class="rounded p-1.5 text-slate-600 transition hover:bg-white hover:text-slate-900"
                :class="{ 'bg-white text-talents-700 ring-1 ring-slate-200': editor?.isActive('underline') }"
                title="Sublinhado"
                @click="toggleUnderline"
            >
                <UnderlineIcon class="h-4 w-4" aria-hidden="true" />
            </button>
            <label
                class="inline-flex cursor-pointer items-center gap-1 rounded border border-slate-200 bg-white px-1.5 py-1 text-[11px] text-slate-600"
                title="Cor do texto"
            >
                <span class="hidden sm:inline">Cor</span>
                <input
                    type="color"
                    :value="textColor"
                    class="h-5 w-6 cursor-pointer border-0 bg-transparent p-0"
                    @input="applyColor($event.target.value)"
                />
            </label>
            <button
                v-if="showAttachment"
                type="button"
                class="ml-auto inline-flex items-center gap-1 rounded px-2 py-1.5 text-[11px] font-medium text-slate-600 transition hover:bg-white hover:text-talents-700"
                title="Anexar arquivo"
                @click="triggerAttachment"
            >
                <PaperClipIcon class="h-4 w-4" aria-hidden="true" />
                Anexo
            </button>
            <input
                v-if="showAttachment"
                ref="fileInputRef"
                type="file"
                class="hidden"
                @change="onFileChange"
            />
        </div>
        <EditorContent :editor="editor" />
    </div>
</template>

<style scoped>
:deep(.ProseMirror p.is-editor-empty:first-child::before) {
    color: #94a3b8;
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}
</style>
