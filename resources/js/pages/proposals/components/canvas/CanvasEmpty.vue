<script setup lang="ts">
import { ref } from 'vue';
import {
  Command,
  CommandDialog,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
  CommandSeparator,
} from '@/components/ui/command';
import { blockRegistry } from '@/pages/proposals/components/blocks/registry';
import type { BlockType } from '@/types/proposal-builder';

const emit = defineEmits<{
  (e: 'add-first', blockType: BlockType): void;
}>();

const props = defineProps<{
  isLocked: boolean;
}>();

const open = ref(false);

const handleSelect = (blockType: BlockType) => {
  emit('add-first', blockType);
  open.value = false;
};

const groupedBlocks = [
  { category: 'Layout', blocks: blockRegistry.filter((b) => b.category === 'layout') },
  { category: 'Content', blocks: blockRegistry.filter((b) => b.category === 'content') },
];
</script>

<template>
  <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border/70 px-6 py-12 text-center text-muted-foreground">
    <p class="text-sm">No blocks yet</p>
    <p class="mt-2 max-w-md text-xs text-muted-foreground">
      Add a block to start designing your canvas. Build a cover, lay out pricing, and collect e-signatures—all in one place.
    </p>
    <button
      v-if="!props.isLocked"
      class="mt-4 inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
      type="button"
      @click="open = true"
    >
      Add first block
    </button>
  </div>

  <CommandDialog v-model:open="open">
    <Command class="rounded-lg border shadow-md">
      <CommandInput placeholder="Search blocks..." />
      <CommandList>
        <CommandEmpty>No blocks found.</CommandEmpty>
        <template v-for="group in groupedBlocks" :key="group.category">
          <CommandGroup :heading="group.category">
            <CommandItem
              v-for="block in group.blocks"
              :key="block.type"
              :value="block.label"
              @select="() => handleSelect(block.type as BlockType)"
            >
              <component :is="block.icon" class="mr-2 h-4 w-4 text-muted-foreground" />
              <div class="flex flex-col">
                <span class="text-sm font-medium">{{ block.label }}</span>
                <span class="text-xs text-muted-foreground">{{ block.description }}</span>
              </div>
            </CommandItem>
          </CommandGroup>
          <CommandSeparator />
        </template>
      </CommandList>
    </Command>
  </CommandDialog>
</template>
