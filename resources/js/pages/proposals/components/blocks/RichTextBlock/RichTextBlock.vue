<script setup lang="ts">
import { computed, watch, ref } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import BlockTitle from '@/pages/proposals/components/blocks/shared/BlockTitle.vue';
import BlockEmptyPlaceholder from '@/pages/proposals/components/blocks/shared/BlockEmptyPlaceholder.vue';
import type { BaseBlock, RichTextBlockData } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{
  data: RichTextBlockData;
  block: BaseBlock;
  isLocked: boolean;
}>();

const store = useProposalBuilderStore();

const editorContent = ref(props.data.content);

const updateData = (changes: Partial<RichTextBlockData>) => {
  store.updateBlockData(props.block.id, {
    ...props.data,
    ...changes,
  });
};

const handleContentUpdate = (content: string) => {
  updateData({ content });
};

watch(
  () => props.data.content,
  (newContent) => {
    if (editorContent.value !== newContent) {
      editorContent.value = newContent;
    }
  }
);

const titleColor = computed(() => props.data.title_color ?? '#111827');
const contentColor = computed(() => props.data.content_color ?? '#374151');
const backgroundColor = computed(() => props.data.background_color ?? '#ffffff');

const sectionStyle = computed(() => ({
  backgroundColor: backgroundColor.value,
}));

const contentStyle = computed(() => ({
  color: contentColor.value,
}));
</script>

<template>
  <section class="space-y-4 p-6" :style="sectionStyle">
    <BlockTitle
      v-if="props.data.show_title"
      :model-value="props.data.title"
      placeholder="Add a section title"
      :is-locked="props.isLocked"
      :color="titleColor"
      @update:model-value="(value) => updateData({ title: value })"
    />
    <div v-if="!props.isLocked" class="border border-border cursor-text transition-colors">
      <QuillEditor
        v-model:content="editorContent"
        theme="snow"
        toolbar="minimal"
        :disabled="props.isLocked"
        contentType="html"
        @update:content="handleContentUpdate"
        :style="contentStyle"
      />
      <BlockEmptyPlaceholder v-if="!props.data.content" message="Write your content" action-label="Start typing" />
    </div>
    <div
      v-else
      class="ql-editor prose prose-sm max-w-none"
      :style="contentStyle"
      v-html="props.data.content || '<p class=\'text-muted-foreground\'>No content</p>'"
    ></div>
  </section>
</template>
