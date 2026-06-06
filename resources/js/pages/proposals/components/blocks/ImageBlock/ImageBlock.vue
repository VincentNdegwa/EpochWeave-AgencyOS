<script setup lang="ts">
import BlockEmptyPlaceholder from '@/pages/proposals/components/blocks/shared/BlockEmptyPlaceholder.vue';
import type { BaseBlock, ImageBlockData } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';

const props = defineProps<{
  data: ImageBlockData;
  block: BaseBlock;
  isLocked: boolean;
}>();

const store = useProposalBuilderStore();

const updateData = (changes: Partial<ImageBlockData>) => {
  store.updateBlockData(props.block.id, {
    ...props.data,
    ...changes,
  });
};

const alignmentClasses: Record<ImageBlockData['alignment'], string> = {
  left: 'mr-auto',
  center: 'mx-auto',
  right: 'ml-auto',
  full: 'w-full',
};

const widthStyle = (widthPercent: number) => {
  if (props.data.alignment === 'full') {
    return {};
  }
  return { width: `${widthPercent}%` };
};

const handleUrlInput = (event: Event) => {
  updateData({ url: (event.target as HTMLInputElement).value });
};
</script>

<template>
  <section class="space-y-3">
    <div v-if="props.data.url" class="space-y-2">
      <div :class="['overflow-hidden rounded-lg', alignmentClasses[props.data.alignment]]" :style="widthStyle(props.data.width_percent)">
        <img :src="props.data.url" :alt="props.data.alt_text ?? ''" class="w-full rounded-lg object-cover" />
      </div>
      <p v-if="props.data.caption" class="text-center text-sm text-muted-foreground">{{ props.data.caption }}</p>
    </div>

    <div v-else-if="!props.isLocked" class="rounded-lg border-2 border-dashed border-border p-6 text-center">
      <p class="text-sm text-muted-foreground">Paste an image URL to display it here.</p>
      <input
        type="url"
        class="mt-3 w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
        placeholder="https://example.com/image.jpg"
        @input="handleUrlInput"
      />
    </div>

    <div v-else class="rounded-lg border border-border p-6 text-center text-sm text-muted-foreground">
      No image provided.
    </div>
  </section>
</template>
