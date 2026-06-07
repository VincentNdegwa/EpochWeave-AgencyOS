<script setup lang="ts">
import BlockPickerModal from '@/pages/proposals/components/block-picker/BlockPickerModal.vue';
import type { BlockType } from '@/types/proposal-builder';

const emit = defineEmits<{
  (e: 'add-first', blockType: BlockType): void;
}>();

const props = defineProps<{
  isLocked: boolean;
}>();

const handleSelect = (blockType: BlockType) => {
  emit('add-first', blockType);
};
</script>

<template>
  <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border/70 px-6 py-12 text-center text-muted-foreground">
    <p class="text-sm">No blocks yet</p>
    <p class="mt-2 max-w-md text-xs text-muted-foreground">
      Add a block to start designing your canvas. Build a cover, lay out pricing, and collect e-signatures—all in one place.
    </p>
    <BlockPickerModal v-if="!props.isLocked" trigger @select="handleSelect">
      <button
        class="mt-4 inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
        type="button"
      >
        Add first block
      </button>
    </BlockPickerModal>
  </div>
</template>
