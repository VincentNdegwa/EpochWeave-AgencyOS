<script setup lang="ts">
import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import type { BaseBlock } from '@/types/proposal-builder';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import BlockToolbar from '@/pages/proposals/components/canvas/BlockToolbar.vue';
import BlockLockedOverlay from '@/pages/proposals/components/canvas/BlockLockedOverlay.vue';

const props = defineProps<{
  block: BaseBlock;
  isLocked: boolean;
}>();

const store = useProposalBuilderStore();
const { selectedBlockId } = storeToRefs(store);
const isSelected = computed(() => selectedBlockId.value === props.block.id);

const handleSelect = () => {
  if (props.isLocked) {
    return;
  }
  store.selectBlock(props.block.id);
};
</script>

<template>
  <div
    class="relative"
    :class="[
      isSelected && !props.isLocked ? 'ring-2 ring-primary ring-offset-2' : 'ring-1 ring-transparent',
    ]"
    @click.stop="handleSelect"
  >
    <BlockToolbar v-if="!props.isLocked" :block="props.block" :is-selected="isSelected" />
    <slot />
    <BlockLockedOverlay v-if="props.block.is_locked && props.isLocked" />
  </div>
</template>
