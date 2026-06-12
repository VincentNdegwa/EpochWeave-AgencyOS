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

const props = defineProps<{
    afterBlockId: string | null;
}>();

const emit = defineEmits<{
    (e: 'request-add', afterBlockId: string | null, blockType: BlockType): void;
}>();

const open = ref(false);

const handleSelect = (blockType: BlockType) => {
    emit('request-add', props.afterBlockId, blockType);
    open.value = false;
};

const groupedBlocks = [
    {
        category: 'Layout',
        blocks: blockRegistry.filter((b) => b.category === 'layout'),
    },
    {
        category: 'Content',
        blocks: blockRegistry.filter((b) => b.category === 'content'),
    },
];
</script>

<template>
    <div class="group -mx-2 flex items-center px-2 py-1">
        <div
            class="flex-1 border-t border-dashed border-border/60 transition-colors group-hover:border-primary"
        />
        <button
            type="button"
            class="mx-2 hidden rounded-full border border-border bg-background p-1 text-sm text-muted-foreground transition group-hover:flex group-hover:text-primary"
            @click="open = true"
        >
            +
        </button>
        <div
            class="flex-1 border-t border-dashed border-border/60 transition-colors group-hover:border-primary"
        />
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
                            @select="
                                () => handleSelect(block.type as BlockType)
                            "
                        >
                            <component
                                :is="block.icon"
                                class="mr-2 h-4 w-4 text-muted-foreground"
                            />
                            <div class="flex flex-col">
                                <span class="text-sm font-medium">{{
                                    block.label
                                }}</span>
                                <span class="text-xs text-muted-foreground">{{
                                    block.description
                                }}</span>
                            </div>
                        </CommandItem>
                    </CommandGroup>
                    <CommandSeparator />
                </template>
            </CommandList>
        </Command>
    </CommandDialog>
</template>
