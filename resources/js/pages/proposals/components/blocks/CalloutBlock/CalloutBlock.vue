<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import type { BaseBlock, CalloutBlockData } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{
  data: CalloutBlockData;
  block: BaseBlock;
  isLocked: boolean;
}>();

const store = useProposalBuilderStore();
const editorContent = ref(props.data.content);
const isFocused     = ref(false);

const updateData = (changes: Partial<CalloutBlockData>) => {
  // Use recursive update to handle nested blocks properly
  store.updateBlockDataRecursive(props.block.id, { ...props.data, ...changes });
};

watch(() => props.data.content, (incoming) => {
  if (editorContent.value !== incoming) editorContent.value = incoming;
});

// ── Computed styles from data ─────────────────────────────────
const containerStyle = computed(() => ({
  backgroundColor: props.data.background_color ?? 'transparent',
  borderLeftColor: props.data.border_left ? (props.data.accent_color ?? '#6366f1') : 'transparent',
  borderLeftWidth: props.data.border_left ? '4px' : '0',
  borderLeftStyle: (props.data.border_left ? 'solid' : 'none') as 'solid' | 'none',
}));

const isEmpty = computed(() =>
  !props.data.content || props.data.content === '<p><br></p>' || props.data.content.trim() === '',
);

const toolbarOptions = [
  ['bold', 'italic', 'underline'],
  [{ list: 'ordered' }, { list: 'bullet' }],
  ['link', 'clean'],
];
</script>

<template>
  <section
    class="px-8 py-5"
    :style="containerStyle"
  >
    <div class="flex gap-4">

      <!-- Content -->
      <div class="flex-1 min-w-0">

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
          class="ql-editor prose prose-sm max-w-none leading-relaxed text-foreground
                 [&_p]:mb-2 [&_strong]:font-semibold [&_ul]:list-disc [&_ul]:pl-5
                 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-primary [&_a]:underline"
          v-html="isEmpty
            ? '<p class=\'text-muted-foreground italic\'>No content.</p>'
            : data.content"
        />
      </div>
    </div>
  </section>
</template>
