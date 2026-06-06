<script setup lang="ts">
import { computed, ref } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { blockCategories, blockRegistry } from '@/pages/proposals/components/blocks/registry';
import type { BlockType } from '@/types/proposal-builder';

const props = defineProps<{
  open: boolean;
}>();

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
  (e: 'select', type: BlockType): void;
}>();

const search = ref('');

const filteredBlocks = computed(() => {
  const query = search.value.toLowerCase().trim();
  if (!query) {
    return blockRegistry;
  }
  return blockRegistry.filter((block) => block.label.toLowerCase().includes(query));
});

const groupedBlocks = computed(() => {
  return filteredBlocks.value.reduce((groups, block) => {
    const category = blockCategories[block.category];
    if (!groups[category]) {
      groups[category] = [];
    }
    groups[category].push(block);
    return groups;
  }, {} as Record<string, typeof blockRegistry>);
});

const handleSelect = (type: BlockType) => {
  emit('select', type);
  emit('update:open', false);
};
</script>

<template>
  <Dialog :open="props.open" @update:open="emit('update:open', $event)">
    <DialogContent class="max-w-xl">
      <DialogHeader>
        <DialogTitle>Add block</DialogTitle>
      </DialogHeader>
      <div class="space-y-4">
        <Input v-model="search" placeholder="Search blocks" />
        <div class="space-y-6">
          <div v-for="(blocks, category) in groupedBlocks" :key="category" class="space-y-2">
            <p class="text-xs font-medium uppercase text-muted-foreground">{{ category }}</p>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="block in blocks"
                :key="block.type"
                type="button"
                class="flex flex-col rounded-lg border border-border p-3 text-left hover:border-primary"
                @click="handleSelect(block.type as BlockType)"
              >
                <component :is="block.icon" class="h-4 w-4 text-muted-foreground" />
                <span class="text-sm font-medium">{{ block.label }}</span>
                <span class="text-xs text-muted-foreground">{{ block.description }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>
