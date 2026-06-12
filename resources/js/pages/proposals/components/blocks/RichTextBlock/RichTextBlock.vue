<script setup lang="ts">
import { QuillEditor } from '@vueup/vue-quill';
import { computed, watch, ref } from 'vue';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { useBlockMeta } from '@/pages/proposals/components/blocks/shared/blockMetaContext';
import BlockTitle from '@/pages/proposals/components/blocks/shared/BlockTitle.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { BaseBlock, RichTextBlockData } from '@/types/proposal-builder';

const props = defineProps<{
    data: RichTextBlockData;
    block: BaseBlock;
    isLocked: boolean;
}>();

const blockMeta = useBlockMeta();
const store = useProposalBuilderStore();
const editorContent = ref(props.data.content);
const isFocused = ref(false);

const updateData = (changes: Partial<RichTextBlockData>) => {
    store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

const handleContentUpdate = (content: string) => {
    updateData({ content });
};

// Keep local ref in sync when store updates externally (e.g. undo/redo)
watch(
    () => props.data.content,
    (incoming) => {
        if (editorContent.value !== incoming) {
            editorContent.value = incoming;
        }
    },
);

const isEmpty = computed(
    () =>
        !props.data.content ||
        props.data.content === '<p><br></p>' ||
        props.data.content.trim() === '',
);

const sectionStyle = computed(() => ({
    backgroundColor: blockMeta.value.background_color ?? 'transparent',
}));

const contentStyle = computed(() => ({
    color: props.data.content_color ?? 'inherit',
}));

// Quill toolbar — minimal but complete
const toolbarOptions = [
    [{ header: [1, 2, 3, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['blockquote', 'link'],
    ['clean'],
];
</script>

<template>
    <section class="group/richtext px-8" :style="sectionStyle">
        <!-- Optional section title -->
        <BlockTitle
            v-if="data.show_title"
            :model-value="data.title"
            placeholder="Section title…"
            :is-locked="isLocked"
            :color="data.title_color ?? 'inherit'"
            class="mb-4"
            @update:model-value="(v) => updateData({ title: v })"
        />

        <!-- ── Edit mode ──────────────────────────────────────────── -->
        <div
            v-if="!isLocked"
            class="richtext-editor-wrap relative rounded-md transition-all duration-150"
            :class="
                isFocused
                    ? 'ring-2 ring-primary ring-offset-1 ring-offset-background'
                    : 'ring-1 ring-transparent hover:ring-border'
            "
        >
            <QuillEditor
                v-model:content="editorContent"
                theme="snow"
                content-type="html"
                :options="{
                    modules: { toolbar: toolbarOptions },
                    placeholder: 'Start writing…',
                }"
                :style="contentStyle"
                @update:content="handleContentUpdate"
                @focus="isFocused = true"
                @blur="isFocused = false"
            />
        </div>

        <!-- ── Locked / preview mode ──────────────────────────────── -->
        <div
            v-else
            class="ql-editor prose prose-sm max-w-none leading-relaxed [&_a]:text-primary [&_a]:underline [&_blockquote]:my-3 [&_blockquote]:border-l-4 [&_blockquote]:border-border [&_blockquote]:pl-4 [&_blockquote]:text-muted-foreground [&_blockquote]:italic [&_h1]:mb-3 [&_h1]:text-2xl [&_h1]:font-bold [&_h2]:mb-2 [&_h2]:text-xl [&_h2]:font-semibold [&_h3]:mb-2 [&_h3]:text-base [&_h3]:font-semibold [&_ol]:mb-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-3 [&_p]:leading-relaxed [&_strong]:font-semibold [&_ul]:mb-3 [&_ul]:list-disc [&_ul]:pl-5"
            :style="contentStyle"
            v-html="
                isEmpty
                    ? '<p class=\'text-muted-foreground italic\'>No content added.</p>'
                    : data.content
            "
        />
    </section>
</template>
