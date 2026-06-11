<script setup lang="ts">
import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useProposalBuilderStore } from '@/stores/proposalBuilder';
import { getBlockDefinition } from '@/pages/proposals/components/blocks/registry';
import BlockMetaEditor from '@/pages/proposals/components/sidebar/BlockMetaEditor.vue';

const store = useProposalBuilderStore();
const { selectedBlock } = storeToRefs(store);

const settingsComponent = computed(() => {
  if (!selectedBlock.value) {
    return null;
  }
  return getBlockDefinition(selectedBlock.value.type)?.settings ?? null;
});

</script>

<template>
  <div class="flex flex-col">
    <div class="flex-1 p-4">
      <p v-if="!selectedBlock" class="text-sm text-muted-foreground">Select a block to edit its settings.</p>
      <p v-else-if="!settingsComponent" class="text-sm text-muted-foreground">No settings available for this block.</p>
      <component
        v-else
        :is="settingsComponent"
        :key="selectedBlock.id"
        :block-id="selectedBlock.id"
      />
    </div>
    <div v-if="selectedBlock" class="border-t border-border p-4">
      <BlockMetaEditor :block-id="selectedBlock.id" />
    </div>
  </div>
</template>
