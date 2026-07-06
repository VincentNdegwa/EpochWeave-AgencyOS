<script setup lang="ts">
import { QuillEditor } from '@vueup/vue-quill';
import { ref, watch, computed } from 'vue';
import '@vueup/vue-quill/dist/vue-quill.bubble.css';
import BlockTitle from '@/pages/proposals/components/blocks/shared/BlockTitle.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { BaseBlock, CalloutBlockData } from '@/types/proposal-builder';

const props = defineProps<{
    data: CalloutBlockData;
    block: BaseBlock;
    isLocked: boolean;
}>();

const store = useProposalBuilderStore();
const editorContent = ref(props.data.content);
const isFocused = ref(false);

const updateData = (changes: Partial<CalloutBlockData>) => {
    store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

watch(
    () => props.data.content,
    (incoming) => {
        if (editorContent.value !== incoming) {
            editorContent.value = incoming;
        }
    },
);

// ── Computed styles from data ─────────────────────────────────
const containerStyle = computed(() => ({
    backgroundColor: props.data.background_color ?? 'transparent',
    borderLeftColor: props.data.border_left
        ? (props.data.accent_color ?? '#6366f1')
        : 'transparent',
    borderLeftWidth: props.data.border_left ? '4px' : '0',
    borderLeftStyle: (props.data.border_left ? 'solid' : 'none') as
        | 'solid'
        | 'none',
}));

const isEmpty = computed(
    () =>
        !props.data.content ||
        props.data.content === '<p><br></p>' ||
        props.data.content.trim() === '',
);

const toolbarOptions = [
    ['bold', 'italic', 'underline'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['link', 'clean'],
];
</script>

<template>
    <section class="px-8 py-5" :style="containerStyle">
        <!-- Optional section title -->
        <BlockTitle
            v-if="data.title || !isLocked"
            :model-value="data.title"
            placeholder="Callout title…"
            :is-locked="isLocked"
            class="mb-4"
            @update:model-value="(v) => updateData({ title: v })"
        />

        <!-- Edit mode -->
        <div
            v-if="!isLocked"
            class="richtext-editor-wrap relative rounded-md outline-none transition-colors duration-150"
            :class="isFocused ? '' : 'hover:bg-muted/30'"
        >
            <QuillEditor
                v-model:content="editorContent"
                theme="bubble"
                content-type="html"
                :options="{
                    modules: { toolbar: toolbarOptions },
                    placeholder: 'Write your callout content…',
                }"
                @update:content="(v) => updateData({ content: v })"
                @focus="isFocused = true"
                @blur="isFocused = false"
            />
        </div>

        <!-- Locked / preview mode -->
        <div
            v-else
            class="ql-editor prose prose-sm max-w-none leading-relaxed text-foreground [&_a]:text-primary [&_a]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-2 [&_strong]:font-semibold [&_ul]:list-disc [&_ul]:pl-5"
            v-html="
                isEmpty
                    ? '<p class=\'text-muted-foreground italic\'>No content.</p>'
                    : data.content
            "
        />
    </section>
</template>

<style scoped>
:deep(.ql-editor) {
    padding: 0;
}
</style>