<script setup>
import { PaperClipIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps({
    attachments: { type: Array, default: () => [] },
    compact: { type: Boolean, default: false },
    linkPrefix: { type: String, default: 'Anexo' },
    removable: { type: Boolean, default: false },
});

const emit = defineEmits(['remove']);

function isVideo(att) {
    if (typeof att?.mime === 'string') {
        const mime = att.mime.toLowerCase();
        if (mime.startsWith('video/') || mime === 'application/mp4' || mime === 'application/x-mp4') {
            return true;
        }
    }
    const name = String(att?.name ?? '');
    const ext = name.includes('.') ? name.split('.').pop()?.toLowerCase() : '';

    return ['mp4', 'webm', 'mov', 'avi', 'mkv', 'm4v'].includes(ext);
}

const hasAttachments = computed(() => props.attachments?.length > 0);

function removeAttachment(id) {
    emit('remove', id);
}
</script>

<template>
    <ul v-if="hasAttachments" :class="compact ? 'space-y-2' : 'space-y-3'">
        <li
            v-for="att in attachments"
            :key="att.id"
            :class="removable ? 'flex items-start justify-between gap-2 rounded-lg border border-slate-200 px-3 py-2' : ''"
        >
            <div class="min-w-0 flex-1">
                <div v-if="isVideo(att)" class="space-y-1.5">
                    <video
                        :src="att.url"
                        controls
                        preload="metadata"
                        class="max-h-64 w-full max-w-full rounded-lg border border-slate-200 bg-black"
                    >
                        O seu browser não suporta reprodução de vídeo.
                    </video>
                    <a
                        v-if="att.url"
                        :href="att.url"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-talents-700 hover:underline"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <PaperClipIcon class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                        {{ att.name }}
                    </a>
                </div>
                <a
                    v-else-if="att.url"
                    :href="att.url"
                    :class="[
                        'inline-flex min-w-0 items-center gap-1.5 font-medium text-talents-700 hover:underline',
                        compact ? 'text-xs' : 'text-sm',
                    ]"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <PaperClipIcon :class="compact ? 'h-3.5 w-3.5' : 'h-4 w-4'" class="shrink-0" aria-hidden="true" />
                    <span class="truncate">{{ linkPrefix ? `${linkPrefix}: ` : '' }}{{ att.name }}</span>
                </a>
            </div>
            <button
                v-if="removable"
                type="button"
                class="shrink-0 rounded p-1 text-slate-400 hover:bg-red-50 hover:text-red-600"
                title="Remover anexo"
                @click="removeAttachment(att.id)"
            >
                <TrashIcon class="h-4 w-4" aria-hidden="true" />
            </button>
        </li>
    </ul>
</template>
