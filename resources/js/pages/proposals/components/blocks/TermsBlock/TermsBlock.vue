<script setup lang="ts">
import { QuillEditor } from '@vueup/vue-quill';
import { ref, watch } from 'vue';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import BlockTitle from '@/pages/proposals/components/blocks/shared/BlockTitle.vue';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import type { BaseBlock, TermsBlockData } from '@/types/proposal-builder';

const props = defineProps<{
  data: TermsBlockData;
  block: BaseBlock;
  isLocked: boolean;
}>();

const store = useProposalBuilderStore();
const editorContent = ref(props.data.content);
const isFocused = ref(false);

const updateData = (changes: Partial<TermsBlockData>) => {
  store.updateBlockData(props.block.id, { ...props.data, ...changes });
};

watch(() => props.data.content, (incoming) => {
  if (editorContent.value !== incoming) {
editorContent.value = incoming;
}
});

const toolbarOptions = [
  ['bold', 'italic', 'underline'],
  [{ list: 'ordered' }, { list: 'bullet' }],
  [{ header: [1, 2, 3, false] }],
  ['clean'],
];
</script>

<template>
  <section class="px-8 py-6">
    <BlockTitle
      v-if="data.title || !isLocked"
      :model-value="data.title"
      placeholder="Terms & Conditions"
      :is-locked="isLocked"
      class="mb-4"
      @update:model-value="(v) => updateData({ title: v })"
    />

    <!-- Edit mode -->
    <div
      v-if="!isLocked"
      class="richtext-editor-wrap rounded-md transition-all duration-150"
      :class="isFocused
        ? 'ring-2 ring-primary ring-offset-1 ring-offset-background'
        : 'ring-1 ring-transparent hover:ring-border'"
    >
      <QuillEditor
        v-model:content="editorContent"
        theme="snow"
        content-type="html"
        :options="{ modules: { toolbar: toolbarOptions }, placeholder: 'Add your terms and conditions here…' }"
        @update:content="(v) => updateData({ content: v })"
        @focus="isFocused = true"
        @blur="isFocused = false"
      />
    </div>

    <!-- Locked / preview mode -->
    <div
      v-else
      class="ql-editor prose prose-sm max-w-none text-foreground leading-relaxed
             [&_h1]:text-xl [&_h1]:font-bold [&_h1]:mb-3
             [&_h2]:text-lg [&_h2]:font-semibold [&_h2]:mb-2
             [&_h3]:text-base [&_h3]:font-semibold [&_h3]:mb-2
             [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:mb-3
             [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:mb-3
             [&_strong]:font-semibold"
      v-html="data.content || '<p class=\'text-muted-foreground italic\'>No terms added.</p>'"
    />
  </section>
</template>