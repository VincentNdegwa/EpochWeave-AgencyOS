<script setup lang="ts">
import { ref } from 'vue';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
  CommandSeparator,
} from '@/components/ui/command';
import { blockCategories, blockRegistry } from '@/pages/proposals/components/blocks/registry';
import type { BlockType } from '@/types/proposal-builder';

const props = defineProps<{
  trigger?: boolean;
}>();

const emit = defineEmits<{
  (e: 'select', type: BlockType): void;
}>();

const open = ref(false);
const search = ref('');

const handleSelect = (type: BlockType) => {
  emit('select', type);
  open.value = false;
  search.value = '';
};

const groupedBlocks = [
  { category: 'Layout', blocks: blockRegistry.filter((b) => b.category === 'layout') },
  { category: 'Content', blocks: blockRegistry.filter((b) => b.category === 'content') },
];
</script>

<template>
  <Popover v-model:open="open">
    <PopoverTrigger v-if="props.trigger" as-child>
      <slot />
    </PopoverTrigger>
    <PopoverContent class="w-[300px] p-0" align="start">
      <Command v-model:search-term="search">
        <CommandInput placeholder="Search blocks..." />
        <CommandList>
          <CommandEmpty>No blocks found.</CommandEmpty>

          <CommandGroup v-for="group in groupedBlocks" :key="group.category" :heading="group.category">
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
            <CommandSeparator />
          </CommandGroup>
        </CommandList>
      </Command>
    </PopoverContent>
  </Popover>
</template>
